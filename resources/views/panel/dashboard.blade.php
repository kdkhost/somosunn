@extends('panel.layouts.app')

@section('title', 'Painel do Membro - UNN')



@section('panel_content')
    @php
        $plan = $plan ?? null;
        $stats = $stats ?? [];
        $isImpersonatingAdmin = session()->has('impersonator_id') && session()->get('impersonator_is_admin');
        $canAccessCommunity = auth()->user()->canAccessFeature('community') || $isImpersonatingAdmin;
        $canAccessCourses = auth()->user()->canAccessFeature('courses_access') || (method_exists(auth()->user(), 'hasPurchasedCourses') && auth()->user()->hasPurchasedCourses()) || $isImpersonatingAdmin;
        $canSellOnMarketplace = auth()->user()->canSellOnMarketplace() || $isImpersonatingAdmin;
        $coursesCount = (int) ($stats['courses_count'] ?? 0);
        $ordersPaidCount = (int) ($stats['orders_paid_count'] ?? 0);
        $ordersPaidTotal = (float) ($stats['orders_paid_total'] ?? 0);
        $sellerPaidCount = (int) ($stats['seller_paid_count'] ?? 0);
        $sellerNetTotal = (float) ($stats['seller_net_total'] ?? 0);
    @endphp

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">Olá, {{ auth()->user()->name }}!</h1>
                <p class="text-slate-600 mt-1">
                    {{ $plan?->name ? 'Plano ativo: ' . $plan->name : 'Você ainda não possui um plano ativo.' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('panel.profile.edit') }}"
                    class="inline-flex items-center justify-center rounded-full bg-[#1F5EDB] px-5 py-2.5 text-sm font-bold text-white hover:brightness-110 transition">
                    <i class="fas fa-user-edit mr-2"></i> Editar perfil
                </a>
                <a href="{{ route('premium') }}"
                    class="inline-flex items-center justify-center rounded-full border border-[#1F5EDB] px-5 py-2.5 text-sm font-bold text-[#1F5EDB] hover:bg-[#1F5EDB]/10 transition">
                    <i class="fas fa-crown mr-2"></i> Ver planos
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mt-6" id="dashboard-widgets">
        @component('components.panel-widget', [
            'title' => 'Meus cursos',
            'value' => '<span id="counter-curso" class="animate-pulse">...</span>',
            'icon' => 'fas fa-graduation-cap',
            'iconBg' => 'bg-[#1F5EDB]/10',
            'iconColor' => 'text-[#1F5EDB]'
        ])
            <span id="widget-cursos-msg">Carregando...</span>
        @endcomponent

        @component('components.panel-widget', [
            'title' => 'Compras pagas',
            'value' => '<span id="counter-orders" class="animate-pulse">...</span>',
            'icon' => 'fas fa-check-circle',
            'iconBg' => 'bg-emerald-500/10',
            'iconColor' => 'text-emerald-600'
        ])
            Total: <span id="counter-orders-total" class="font-extrabold text-slate-900 animate-pulse">...</span>
        @endcomponent

        @component('components.panel-widget', [
            'title' => 'Comunidade',
            'value' => 'UNN',
            'icon' => 'fas fa-users',
            'iconBg' => 'bg-slate-900/5',
            'iconColor' => 'text-slate-700'
        ])
            @if($canAccessCommunity)
                <a href="{{ route('social.feed') }}"
                    class="inline-flex items-center justify-center rounded-full bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800 transition">
                    <i class="fas fa-arrow-right mr-2"></i> Ir para o feed
                </a>
            @else
                <a href="{{ route('premium') }}"
                    class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                    <i class="fas fa-crown mr-2"></i> Fazer upgrade
                </a>
            @endif
        @endcomponent

        @component('components.panel-widget', [
            'title' => 'Minhas vendas',
            'value' => '<span id="counter-seller" class="animate-pulse">...</span>',
            'icon' => 'fas fa-receipt',
            'iconBg' => 'bg-amber-500/10',
            'iconColor' => 'text-amber-600'
        ])
            Líquido: <span id="counter-seller-total" class="font-extrabold text-slate-900 animate-pulse">...</span>
            <div class="mt-3">
                <a href="{{ route('panel.marketplace.sales') }}"
                    class="inline-flex items-center text-sm font-bold text-[#1F5EDB] hover:underline">
                    Ver detalhes <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>
        @endcomponent
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mt-8">
        <div class="flex items-center justify-between mb-4">
            <div class="text-lg font-bold text-slate-900">Vendas nos últimos 6 meses</div>
        </div>
        <canvas id="salesChart" height="80"></canvas>
    </div>
@endsection

@push('scripts')
<script>
function updateDashboardWidgets() {
    fetch('{{ route('panel.dashboard.stats') }}')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.stats) {
                document.getElementById('counter-curso').textContent = data.stats.courses_count;
                document.getElementById('counter-orders').textContent = data.stats.orders_paid_count;
                document.getElementById('counter-orders-total').textContent = 'R$ ' + (data.stats.orders_paid_total).toLocaleString('pt-BR', {minimumFractionDigits: 2});
                document.getElementById('counter-seller').textContent = data.stats.seller_paid_count;
                document.getElementById('counter-seller-total').textContent = 'R$ ' + (data.stats.seller_net_total).toLocaleString('pt-BR', {minimumFractionDigits: 2});
                document.getElementById('widget-cursos-msg').textContent = '';
                // Remove animação de loading
                document.querySelectorAll('.animate-pulse').forEach(e => e.classList.remove('animate-pulse'));
            }
        });
}
document.addEventListener('DOMContentLoaded', function() {
    updateDashboardWidgets();
    setInterval(updateDashboardWidgets, 10000); // Atualiza a cada 10s
});
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function updateSalesChart(chart) {
    fetch('{{ route('panel.dashboard.stats') }}?chart=1')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.sales_chart) {
                chart.data.labels = data.sales_chart.labels;
                chart.data.datasets[0].data = data.sales_chart.data;
                chart.update();
            }
        });
}
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Vendas pagas',
                data: [0,0,0,0,0,0],
                backgroundColor: '#1F5EDB',
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    updateSalesChart(salesChart);
    setInterval(function() { updateSalesChart(salesChart); }, 10000);
});
</script>
@endpush
