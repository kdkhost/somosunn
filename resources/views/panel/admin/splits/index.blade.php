@extends('panel.layouts.app')

@section('title', 'Contabilidade de Rateios')

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
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Contabilidade de Rateios</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Valores do proprio administrador-vendedor sao liquidados automaticamente. Repasses externos permanecem pendentes ate a transferencia real.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['Total rateado', $summary['total'], 'fa-chart-pie', 'text-blue-600'],
                ['Total liquidado', $summary['paid'], 'fa-circle-check', 'text-emerald-600'],
                ['Total pendente', $summary['pending'], 'fa-clock', 'text-amber-600'],
                ['Rateios pendentes', $summary['pending_count'], 'fa-list-check', 'text-violet-600', true],
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
                <option value="">Todos os status</option>
                <option value="pending" @selected($status === 'pending')>Pendentes</option>
                <option value="paid" @selected($status === 'paid')>Liquidados</option>
                <option value="rejected" @selected($status === 'rejected')>Rejeitados</option>
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
                <table class="w-full min-w-[1050px] text-left">
                    <thead class="bg-slate-50 dark:bg-slate-950/60">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-4">Pedido</th>
                            <th class="px-5 py-4">Destinatario</th>
                            <th class="px-5 py-4">Tipo</th>
                            <th class="px-5 py-4 text-right">Percentual</th>
                            <th class="px-5 py-4 text-right">Valor</th>
                            <th class="px-5 py-4">Chave PIX</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Data</th>
                            <th class="px-5 py-4 text-right">Acao</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($splits as $split)
                            <tr class="text-sm text-slate-600 hover:bg-slate-50/70 dark:text-slate-300 dark:hover:bg-slate-950/30">
                                <td class="px-5 py-4">
                                    <a href="{{ route('panel.admin.orders.show', $split->order_id) }}" class="font-black text-blue-600 hover:underline">#{{ $split->order_id }}</a>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $split->receiver?->name ?? 'Nao vinculado' }}</div>
                                    <div class="text-xs text-slate-400">{{ $split->receiver?->email }}</div>
                                </td>
                                <td class="px-5 py-4">{{ $typeLabels[$split->receiver_type] ?? ucfirst($split->receiver_type) }}</td>
                                <td class="px-5 py-4 text-right font-bold">{{ number_format((float) $split->percentage, 2, ',', '.') }}%</td>
                                <td class="px-5 py-4 text-right font-black text-slate-900 dark:text-white">R$ {{ number_format((float) $split->amount, 2, ',', '.') }}</td>
                                <td class="px-5 py-4"><span class="max-w-[180px] truncate font-mono text-xs">{{ $split->pix_key ?: '-' }}</span></td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $split->status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">
                                        {{ $split->status === 'paid' ? 'Liquidado' : 'Pendente' }}
                                    </span>
                                    @if($split->status !== 'paid' && !$split->pix_key)
                                        <div class="mt-1 text-[11px] font-bold text-red-500">Chave PIX ausente</div>
                                    @elseif($split->status !== 'paid')
                                        <div class="mt-1 text-[11px] text-slate-400">Repasse externo</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">{{ $split->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-4 text-right">
                                    @if($split->status !== 'paid' && $split->pix_key)
                                        <button type="button" data-pay-url="{{ route('panel.admin.splits.pay', $split) }}"
                                            class="js-pay-split rounded-xl bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700">
                                            Liquidar
                                        </button>
                                    @elseif($split->status !== 'paid')
                                        <span class="text-xs font-bold text-red-500">Cadastre o PIX</span>
                                    @else
                                        <span class="text-xs font-bold text-slate-400">Concluido</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-16 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Nenhum rateio encontrado.
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
document.querySelectorAll('.js-pay-split').forEach(function (button) {
    button.addEventListener('click', async function () {
        const result = await Swal.fire({
            title: 'Liquidar este rateio?',
            text: 'Confirme somente depois de realizar o repasse.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, liquidar',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        const response = await fetch(button.dataset.payUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();

        if (!response.ok) {
            await Swal.fire('Erro', data.message || 'Nao foi possivel liquidar o rateio.', 'error');
            return;
        }

        await Swal.fire('Concluido', data.message, 'success');
        window.location.reload();
    });
});
</script>
@endpush
