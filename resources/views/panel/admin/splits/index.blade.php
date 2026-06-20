@extends('panel.layouts.app')

@section('title', 'Controle de Repasses')

@section('panel_content')
    @php
        $typeLabels = [
            'seller' => 'Vendedor',
            'platform' => 'Plataforma',
            'traffic' => 'Marketing',
            'superadmin' => 'Superadmin',
        ];
    @endphp

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Controle de Repasses</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                O pedido pago gera o rateio automaticamente. O repasse fica controlado separadamente para conciliacao manual ou automacao futura.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['Total rateado', $summary['total'], 'fa-chart-pie', 'text-blue-600'],
                ['Liquidado', $summary['paid'], 'fa-circle-check', 'text-emerald-600'],
                ['Aguardando repasse', $summary['pending'], 'fa-clock', 'text-amber-600'],
                ['Com falha', $summary['failed_count'], 'fa-exclamation-triangle', 'text-rose-600', true],
            ] as $card)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $card[0] }}</p>
                        <i class="fas {{ $card[2] }} {{ $card[3] }}"></i>
                    </div>
                    <p class="mt-3 text-xl font-black text-slate-900 dark:text-white">
                        {{ !empty($card[4]) ? number_format($card[1], 0, ',', '.') : 'R$ ' . number_format($card[1], 2, ',', '.') }}
                    </p>
                </div>
            @endforeach
        </div>

        <form method="GET" action="{{ route('panel.admin.splits.index') }}"
            class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_180px_180px_auto] dark:border-slate-800 dark:bg-slate-900">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar pedido, nome ou e-mail..."
                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
            <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos os repasses</option>
                <option value="pending" @selected($status === 'pending')>Pendentes</option>
                <option value="paid" @selected($status === 'paid')>Liquidados</option>
                <option value="failed" @selected($status === 'failed')>Com falha</option>
            </select>
            <select name="receiver_type" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos os destinatarios</option>
                @foreach($typeLabels as $value => $label)
                    <option value="{{ $value }}" @selected($receiverType === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-700">
                <i class="fas fa-filter mr-2"></i>Filtrar
            </button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1220px] text-left">
                    <thead class="bg-slate-50 dark:bg-slate-950/60">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-4">Pedido</th>
                            <th class="px-5 py-4">Destinatario</th>
                            <th class="px-5 py-4">Tipo</th>
                            <th class="px-5 py-4 text-right">Percentual</th>
                            <th class="px-5 py-4 text-right">Valor</th>
                            <th class="px-5 py-4">Chave PIX</th>
                            <th class="px-5 py-4">Repasse</th>
                            <th class="px-5 py-4">Operacao</th>
                            <th class="px-5 py-4 text-right">Acao</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($splits as $split)
                            @php
                                $payout = $split->payout;
                                $payoutStatus = $payout?->status ?? 'pending';
                                $provider = $payout?->provider ?? 'manual';
                            @endphp
                            <tr class="text-sm text-slate-600 hover:bg-slate-50/70 dark:text-slate-300 dark:hover:bg-slate-950/30">
                                <td class="px-5 py-4">
                                    <a href="{{ route('panel.admin.orders.show', $split->order_id) }}" class="font-black text-blue-600 hover:underline">#{{ $split->order_id }}</a>
                                    <div class="text-xs text-slate-400">{{ $split->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $split->receiver?->name ?? 'Nao vinculado' }}</div>
                                    <div class="text-xs text-slate-400">{{ $split->receiver?->email }}</div>
                                </td>
                                <td class="px-5 py-4">{{ $typeLabels[$split->receiver_type] ?? ucfirst($split->receiver_type) }}</td>
                                <td class="px-5 py-4 text-right font-bold">{{ number_format((float) $split->percentage, 2, ',', '.') }}%</td>
                                <td class="px-5 py-4 text-right font-black text-slate-900 dark:text-white">R$ {{ number_format((float) $split->amount, 2, ',', '.') }}</td>
                                <td class="px-5 py-4"><span class="max-w-[220px] truncate font-mono text-xs">{{ $split->pix_key ?: 'PIX ausente' }}</span></td>
                                <td class="px-5 py-4">
                                    @if($payoutStatus === 'paid')
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Liquidado</span>
                                    @elseif($payoutStatus === 'failed')
                                        <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">Falhou</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">Pendente</span>
                                    @endif
                                    <div class="mt-1 text-[11px] text-slate-400">{{ $provider === 'internal' ? 'Interno' : 'Manual' }}</div>
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-500 dark:text-slate-400">
                                    <div><strong>Tentativas:</strong> {{ (int) ($payout?->attempts ?? 0) }}</div>
                                    @if(!empty($payout?->last_error))
                                        <div class="mt-1 text-rose-500">{{ $payout->last_error }}</div>
                                    @elseif($payout?->processed_at)
                                        <div class="mt-1 text-emerald-500">Confirmado em {{ $payout->processed_at->format('d/m/Y H:i') }}</div>
                                    @else
                                        <div class="mt-1">Aguardando tratamento</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if($payoutStatus !== 'paid' && $split->pix_key)
                                        <div class="flex justify-end gap-2">
                                            <button type="button" data-pay-url="{{ route('panel.admin.splits.pay', $split) }}"
                                                data-receiver="{{ $split->receiver?->name ?? ($typeLabels[$split->receiver_type] ?? 'Destinatario') }}"
                                                data-amount="R$ {{ number_format((float) $split->amount, 2, ',', '.') }}"
                                                class="js-pay-split rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700">
                                                Confirmar
                                            </button>
                                            <button type="button" data-fail-url="{{ route('panel.admin.splits.fail', $split) }}"
                                                class="js-fail-split rounded-xl border border-rose-200 px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:border-rose-900/40 dark:hover:bg-rose-900/10">
                                                Falhou
                                            </button>
                                        </div>
                                    @elseif($payoutStatus !== 'paid')
                                        <span class="text-xs font-bold text-red-500">Cadastre o PIX</span>
                                    @else
                                        <span class="text-xs font-bold text-slate-400">Concluido</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-16 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Nenhum repasse encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($splits->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">{{ $splits->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
async function postSplitAction(url, payload) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload || {})
    });

    const data = await response.json();
    if (!response.ok) {
        throw new Error(data.message || 'Nao foi possivel concluir a operacao.');
    }

    return data;
}

document.querySelectorAll('.js-pay-split').forEach(function (button) {
    button.addEventListener('click', async function () {
        const result = await Swal.fire({
            title: 'Confirmar repasse?',
            text: 'Confirme somente depois da transferencia para ' + button.dataset.receiver + ' no valor de ' + button.dataset.amount + '.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, confirmar',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        try {
            const data = await postSplitAction(button.dataset.payUrl);
            await Swal.fire('Concluido', data.message, 'success');
            window.location.reload();
        } catch (error) {
            await Swal.fire('Erro', error.message, 'error');
        }
    });
});

document.querySelectorAll('.js-fail-split').forEach(function (button) {
    button.addEventListener('click', async function () {
        const result = await Swal.fire({
            title: 'Registrar falha',
            input: 'text',
            inputLabel: 'Motivo da falha do repasse',
            inputPlaceholder: 'Ex.: PIX recusado, conta indisponivel, divergencia de dados',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Registrar falha',
            cancelButtonText: 'Cancelar',
            inputValidator: function (value) {
                if (!value) {
                    return 'Informe o motivo da falha.';
                }
            }
        });

        if (!result.isConfirmed) return;

        try {
            const data = await postSplitAction(button.dataset.failUrl, { message: result.value });
            await Swal.fire('Registrado', data.message, 'success');
            window.location.reload();
        } catch (error) {
            await Swal.fire('Erro', error.message, 'error');
        }
    });
});
</script>
@endpush
