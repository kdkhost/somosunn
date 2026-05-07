@extends('panel.layouts.app')

@section('title', 'Transações SumUp')

@section('panel_breadcrumb')
    <span class="text-slate-500 dark:text-slate-400">SumUp</span>
@endsection

@section('panel_content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Transações SumUp</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Gerencie todos os pagamentos processados via SumUp.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('panel.admin.settings', ['group' => 'gateway']) }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-2xl shadow-lg shadow-blue-500/30 transition flex items-center gap-2">
                <i class="fas fa-cog"></i> Configurar Credenciais
            </a>
            <a href="{{ route('panel.admin.sumup.report') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-2xl shadow-lg shadow-indigo-500/30 transition flex items-center gap-2">
                <i class="fas fa-chart-bar"></i> Relatório
            </a>
            <button onclick="testConnection()"
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold py-2.5 px-5 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-center gap-2">
                <i class="fas fa-plug"></i> Testar Conexão
            </button>
        </div>
    </div>

    <div class="bg-blue-50 dark:bg-blue-950/20 rounded-2xl p-4 border border-blue-100 dark:border-blue-900/50 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0">
                <i class="fas fa-key"></i>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-black text-blue-950 dark:text-blue-200">Antes de testar a SumUp</h3>
                <p class="text-xs text-blue-900 dark:text-blue-200 mt-1 leading-relaxed">
                    Preencha em <strong>Configurações &gt; Pagamentos</strong> a <strong>API Key secreta</strong> criada em
                    <strong>me.sumup.com &gt; Settings &gt; For Developers &gt; Toolkit &gt; API Keys</strong> e o
                    <strong>Merchant Code</strong> da mesma conta lojista. Client ID, Client Secret e Webhook Secret são opcionais
                    e só devem ser usados quando OAuth ou assinatura HMAC estiverem configurados na SumUp.
                </p>
            </div>
        </div>
    </div>

    {{-- Totais --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-100 dark:border-slate-800 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Receita Paga</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">R$ {{ number_format($totals['paid'], 2, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-100 dark:border-slate-800 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Pendentes</p>
            <p class="text-2xl font-bold text-yellow-500 dark:text-yellow-400 mt-1">{{ $totals['pending'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-100 dark:border-slate-800 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Falhas</p>
            <p class="text-2xl font-bold text-red-500 dark:text-red-400 mt-1">{{ $totals['failed'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-100 dark:border-slate-800 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Reembolsado</p>
            <p class="text-2xl font-bold text-slate-600 dark:text-slate-300 mt-1">R$ {{ number_format($totals['refunded'], 2, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('panel.admin.sumup.index') }}"
          class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ID, checkout, comprador..."
                   class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-sm dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none px-3 py-2">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Status</label>
            <select name="status" class="rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-sm dark:text-white focus:border-blue-500 outline-none px-3 py-2">
                <option value="">Todos</option>
                <option value="PAID" @selected(request('status') === 'PAID')>Pago</option>
                <option value="PENDING" @selected(request('status') === 'PENDING')>Pendente</option>
                <option value="FAILED" @selected(request('status') === 'FAILED')>Falhou</option>
                <option value="REFUNDED" @selected(request('status') === 'REFUNDED')>Reembolsado</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Método</label>
            <select name="payment_type" class="rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-sm dark:text-white focus:border-blue-500 outline-none px-3 py-2">
                <option value="">Todos</option>
                <option value="CARD" @selected(request('payment_type') === 'CARD')>Cartão</option>
                <option value="PIX" @selected(request('payment_type') === 'PIX')>PIX</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">De</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-sm dark:text-white focus:border-blue-500 outline-none px-3 py-2">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Até</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-sm dark:text-white focus:border-blue-500 outline-none px-3 py-2">
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-xl transition">
            <i class="fas fa-search mr-1"></i> Filtrar
        </button>
        <a href="{{ route('panel.admin.sumup.index') }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 py-2 px-3 rounded-xl transition text-sm">
            Limpar
        </a>
    </form>

    {{-- Tabela --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 dark:text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4">ID / Checkout</th>
                        <th class="px-6 py-4">Comprador</th>
                        <th class="px-6 py-4">Valor</th>
                        <th class="px-6 py-4">Método</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4">Data</th>
                        <th class="px-6 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800 dark:text-white">#{{ $t->id }}</div>
                                <div class="text-xs font-mono text-slate-400 dark:text-slate-500 truncate max-w-[160px]" title="{{ $t->checkout_id }}">{{ $t->checkout_id }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-700 dark:text-slate-200">{{ $t->order?->user?->name ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $t->order?->user?->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-800 dark:text-white">
                                R$ {{ number_format($t->amount, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($t->payment_type === 'PIX')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400">
                                        <i class="fas fa-qrcode text-xs"></i> PIX
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400">
                                        <i class="fas fa-credit-card text-xs"></i> Cartão
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusMap = [
                                        'PAID'     => ['bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400', 'Pago'],
                                        'PENDING'  => ['bg-yellow-100 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400', 'Pendente'],
                                        'FAILED'   => ['bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400', 'Falhou'],
                                        'REFUNDED' => ['bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400', 'Reembolsado'],
                                    ];
                                    [$cls, $label] = $statusMap[$t->status] ?? ['bg-slate-100 text-slate-600', $t->status];
                                @endphp
                                <span class="inline-flex px-2.5 py-0.5 rounded-lg text-xs font-bold {{ $cls }}">{{ $label }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">
                                {{ $t->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('panel.admin.sumup.show', $t) }}"
                                   class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                                   title="Ver detalhes">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i class="fas fa-inbox text-4xl opacity-20 block mb-2"></i>
                                Nenhuma transação SumUp encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function testConnection() {
    fetch('{{ route('panel.admin.sumup.test-connection') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            toastr.success(d.message);
        } else {
            toastr.error(d.message);
        }
    })
    .catch(() => toastr.error('Erro ao testar conexão.'));
}
</script>
@endpush
