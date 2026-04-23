@extends('panel.layouts.app')

@section('title', 'Relatório SumUp')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.sumup.index') }}" class="hover:underline transition-all">SumUp</a>
    <span class="mx-2 text-slate-300 dark:text-slate-700">/</span>
    <span class="text-slate-500 dark:text-slate-400">Relatório</span>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('panel_content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Relatório SumUp</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Análise de vendas e receita processada via SumUp.</p>
        </div>
        <a href="{{ route('panel.admin.sumup.report.export', request()->query()) }}"
           class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-5 rounded-2xl shadow-lg shadow-green-500/30 transition flex items-center gap-2">
            <i class="fas fa-file-csv"></i> Exportar CSV
        </a>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('panel.admin.sumup.report') }}"
          class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">De</label>
            <input type="date" name="date_from" value="{{ $dateFrom }}"
                   class="rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-sm dark:text-white focus:border-blue-500 outline-none px-3 py-2">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Até</label>
            <input type="date" name="date_to" value="{{ $dateTo }}"
                   class="rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-sm dark:text-white focus:border-blue-500 outline-none px-3 py-2">
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-xl transition">
            <i class="fas fa-search mr-1"></i> Filtrar
        </button>
    </form>

    {{-- Totais --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Receita Bruta</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1">R$ {{ number_format($grossRevenue, 2, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Taxas SumUp</p>
            <p class="text-2xl font-bold text-red-500 dark:text-red-400 mt-1">R$ {{ number_format($fees, 2, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Receita Líquida</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">R$ {{ number_format($netRevenue, 2, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Reembolsos</p>
            <p class="text-2xl font-bold text-slate-500 dark:text-slate-400 mt-1">R$ {{ number_format($refundedAmount, 2, ',', '.') }}</p>
        </div>
    </div>

    {{-- Gráfico --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">
        <h3 class="font-bold text-slate-700 dark:text-slate-200 mb-4">Evolução de Vendas</h3>
        <canvas id="salesChart" height="80"></canvas>
    </div>

    {{-- Tabela de transações --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
            <h3 class="font-bold text-slate-700 dark:text-slate-200">Transações no Período ({{ $transactions->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-bold text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Comprador</th>
                        <th class="px-6 py-3">Valor</th>
                        <th class="px-6 py-3">Método</th>
                        <th class="px-6 py-3">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($transactions as $t)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                        <td class="px-6 py-3">{{ $t->order?->user?->name ?? '-' }}</td>
                        <td class="px-6 py-3 font-bold text-slate-800 dark:text-white">R$ {{ number_format($t->amount, 2, ',', '.') }}</td>
                        <td class="px-6 py-3">{{ $t->payment_type }}</td>
                        <td class="px-6 py-3 text-slate-400">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-400">Nenhuma transação no período.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const chartData = @json($chartData);
const labels = chartData.map(d => d.date);
const values = chartData.map(d => parseFloat(d.total));

const isDark = document.documentElement.classList.contains('dark');
const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Receita (R$)',
            data: values,
            backgroundColor: 'rgba(99, 102, 241, 0.7)',
            borderColor: 'rgba(99, 102, 241, 1)',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: gridColor }, ticks: { callback: v => 'R$ ' + v.toFixed(2) } },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
