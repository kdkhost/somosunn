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

    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-all duration-300">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white transition-colors">Olá,
                    {{ auth()->user()->name }}!</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1 transition-colors">
                    {{ $plan?->name ? 'Plano ativo: ' . $plan->name : 'Você ainda não possui um plano ativo.' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('panel.profile.edit') }}"
                    class="inline-flex items-center justify-center rounded-full bg-[#1F5EDB] px-5 py-2.5 text-sm font-bold text-white hover:brightness-110 transition shadow-lg shadow-blue-500/20">
                    <i class="fas fa-user-edit mr-2"></i> Editar perfil
                </a>
                <a href="{{ route('premium') }}"
                    class="inline-flex items-center justify-center rounded-full border border-blue-200 dark:border-blue-800 px-5 py-2.5 text-sm font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition">
                    <i class="fas fa-crown mr-2"></i> Ver planos
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mt-6" id="dashboard-widgets">
        @if($canAccessCourses)
            <x-widgets.metric title="Meus Cursos" icon="fas fa-graduation-cap" :value="$coursesCount ?? 0" color="blue" />
        @endif
        <x-widgets.metric title="Compras pagas" icon="fas fa-check-circle" :value="$ordersPaidCount ?? 0" color="emerald" />
        <x-widgets.metric title="Total em compras" icon="fas fa-wallet" :value="number_format($ordersPaidTotal ?? 0, 2, ',', '.')" color="green" />
        @if($canSellOnMarketplace)
            <x-widgets.metric title="Vendas realizadas" icon="fas fa-shopping-cart" :value="$sellerPaidCount ?? 0"
                color="purple" />
            <x-widgets.metric title="Receita líquida" icon="fas fa-coins" :value="number_format($sellerNetTotal ?? 0, 2, ',', '.')" color="yellow" />
        @endif
        @if($canAccessCommunity)
            <x-widgets.metric title="Comunidade" icon="fas fa-users" :value="$communityCount ?? 0" color="cyan" />
        @endif
    </div>

    @if(isset($suggestedUsers) && $suggestedUsers->count() > 0)
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 mt-6 transition-all duration-300">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white mb-4 flex items-center gap-2 transition-colors">
                <i class="fas fa-users text-blue-500"></i> Sugestões de Conexão
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($suggestedUsers as $sUser)
                    <div
                        class="bg-white dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 rounded-2xl p-4 flex flex-col items-center text-center hover:shadow-md transition duration-300">
                        <div
                            class="w-16 h-16 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 mb-3 border border-white dark:border-slate-700 shadow-sm">
                            @if($sUser->photo)
                                <img src="{{ asset($sUser->photo) }}" alt="{{ $sUser->name }}" class="w-full h-full object-cover">
                            @else
                                <div
                                    class="w-full h-full flex items-center justify-center text-slate-400 dark:text-slate-500 font-bold text-xl">
                                    {{ mb_substr($sUser->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-white text-sm line-clamp-1 transition-colors">
                            {{ $sUser->name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3 line-clamp-1 transition-colors">
                            {{ $sUser->occupation ?? 'Membro' }}</p>

                        @php
                            $commonTags = [];
                            if ($sUser->interests && auth()->user()->interests) {
                                $myTags = array_map('trim', explode(',', auth()->user()->interests));
                                $userTags = array_map('trim', explode(',', $sUser->interests));
                                $commonTags = array_intersect($myTags, $userTags);
                            }
                        @endphp

                        @if(!empty($commonTags))
                            <div class="flex flex-wrap gap-1 justify-center mb-3">
                                @foreach(array_slice($commonTags, 0, 2) as $tag)
                                    <span
                                        class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-2 py-0.5 rounded text-[10px] font-bold">{{ $tag }}</span>
                                @endforeach
                                @if(count($commonTags) > 2)
                                    <span
                                        class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded text-[10px]">+{{ count($commonTags) - 2 }}</span>
                                @endif
                            </div>
                        @endif

                        <a href="{{ route('social.profile', $sUser->id) }}"
                            class="mt-auto w-full inline-flex items-center justify-center rounded-xl bg-slate-900 dark:bg-blue-600 px-3 py-2 text-xs font-bold text-white hover:bg-slate-800 dark:hover:bg-blue-700 transition">
                            Conectar
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 mt-8 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
            <div class="text-lg font-bold text-slate-900 dark:text-white transition-colors">Vendas nos últimos 6 meses</div>
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
                        document.getElementById('counter-orders-total').textContent = 'R$ ' + (data.stats.orders_paid_total).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                        document.getElementById('counter-seller').textContent = data.stats.seller_paid_count;
                        document.getElementById('counter-seller-total').textContent = 'R$ ' + (data.stats.seller_net_total).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                        document.getElementById('widget-cursos-msg').textContent = '';
                        // Remove animação de loading
                        document.querySelectorAll('.animate-pulse').forEach(e => e.classList.remove('animate-pulse'));
                    }
                });
        }
        document.addEventListener('DOMContentLoaded', function () {
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
        document.addEventListener('DOMContentLoaded', function () {
            var ctx = document.getElementById('salesChart').getContext('2d');
            var salesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                    datasets: [{
                        label: 'Vendas pagas',
                        data: [0, 0, 0, 0, 0, 0],
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
            setInterval(function () { updateSalesChart(salesChart); }, 10000);
        });
    </script>
@endpush