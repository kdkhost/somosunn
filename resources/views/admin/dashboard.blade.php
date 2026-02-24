@extends('admin.layouts.app')

@section('page_title', 'Dashboard')

@section('content')
    <div class="row">
        {{-- WELCOME SECTION (ALL USERS) --}}
        <div class="col-12 mb-4">
            <div class="card bg-gradient-navy shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            @if(auth()->user()->photo)
                                <img src="{{ asset(auth()->user()->photo) }}"
                                    class="rounded-circle border border-white elevation-2"
                                    style="width: 70px; height: 70px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border border-white elevation-2"
                                    style="width: 70px; height: 70px; font-size: 28px; color: #333;">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="col">
                            <h2 class="h4 font-weight-bold mb-1">Olá, {{ auth()->user()->name }}!</h2>
                            <p class="mb-0 text-light opacity-75">
                                Bem-vindo ao <strong>SOMOS UNN</strong>.
                                @if(isset($isAdmin) && $isAdmin)
                                    Painel Administrativo Completo. Acompanhe as métricas e gerencie a plataforma.
                                @else
                                    Acompanhe seu progresso, próximos eventos e novidades.
                                @endif
                            </p>
                        </div>
                        <div class="col-md-auto mt-3 mt-md-0 text-md-right">
                            {{-- System Status & Uptime --}}
                            <div class="text-xs text-white-50 mb-1">Status do Sistema</div>
                            <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Online</span>
                            <small class="d-block text-white-50 mt-1">PHP {{ phpversion() }} • Laravel
                                {{ app()->version() }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ADMIN DASHBOARD --}}
        @if(isset($isAdmin) && $isAdmin)
            <div class="col-12">
                {{-- KPI ROW 1: REVENUE & USERS --}}
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>R$ {{ number_format($totalRevenue ?? 0, 2, ',', '.') }}</h3>
                                <p>Receita Total</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <a href="{{ route('admin.orders.index') }}" class="small-box-footer">Ver Pedidos <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    {{-- MP BALANCE WIDGET --}}
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary" id="mp-balance-widget">
                            <div class="inner">
                                <h3 id="mp-balance-value"><i class="fas fa-spinner fa-spin text-sm"></i></h3>
                                <p>Saldo Mercado Pago</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-university"></i>
                            </div>
                            <a href="#" onclick="fetchMpBalance(); return false;" class="small-box-footer">Atualizar <i
                                    class="fas fa-sync"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $totalUsers ?? 0 }}</h3>
                                <p>Usuários Cadastrados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <a href="{{ route('admin.users.index') }}" class="small-box-footer">Gerenciar Usuários <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $totalOrders ?? 0 }}</h3>
                                <p>Total de Pedidos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <a href="{{ route('admin.orders.index') }}" class="small-box-footer">Todos Pedidos <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

                {{-- KPI ROW 2: CONTENT CONTENT --}}
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-primary"><i class="fas fa-graduation-cap"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Cursos</span>
                                <span class="info-box-number">{{ $coursesCount ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-success"><i class="fas fa-chalkboard-teacher"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Mentorias</span>
                                <span class="info-box-number">{{ $mentorshipsCount ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-warning"><i class="fas fa-calendar-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Eventos</span>
                                <span class="info-box-number">{{ $eventsCount ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-secondary"><i class="fas fa-certificate"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Certificados</span>
                                <span class="info-box-number">{{ $certificatesCount ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ROW 3: CHARTS --}}
                <div class="row">
                    {{-- Sales Chart --}}
                    <div class="col-lg-8">
                        <div class="card card-outline card-primary">
                            <div class="card-header border-0">
                                <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Histórico de Vendas (Últimos 6
                                    meses)</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart">
                                    <canvas id="salesChart"
                                        style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Order Status Chart --}}
                    <div class="col-lg-4">
                        <div class="card card-outline card-info">
                            <div class="card-header border-0">
                                <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Pedidos por Status</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="ordersStatusChart"
                                    style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ROW 4: CALENDAR & LOGS --}}
                <div class="row">
                    {{-- Calendar --}}
                    <div class="col-lg-7">
                        <div class="card card-outline card-success">
                            <div class="card-header border-0">
                                <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Calendário de Eventos</h3>
                            </div>
                            <div class="card-body p-0">
                                <div id="calendar" style="width: 100%; min-height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                    {{-- Activity Logs --}}
                    <div class="col-lg-5">
                        <div class="card card-outline card-secondary">
                            <div class="card-header border-0">
                                <h3 class="card-title"><i class="fas fa-history mr-2"></i>Últimas Atividades</h3>
                                <div class="card-tools">
                                    <span class="badge badge-light">{{ $logsCount ?? 0 }} logs</span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-valign-middle">
                                        <thead>
                                            <tr>
                                                <th>Hora</th>
                                                <th>Ação</th>
                                                <th>Usuário</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse(\App\Models\ActivityLog::with('user')->latest()->limit(8)->get() as $log)
                                                <tr>
                                                    <td class="text-xs text-muted">{{ $log->created_at->format('d/m H:i') }}</td>
                                                    <td><span
                                                            class="badge badge-info text-xs">{{ Str::limit($log->action, 15) }}</span>
                                                    </td>
                                                    <td class="text-xs">{{ $log->user->name ?? 'Sistema' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">Nenhuma atividade recente.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <a href="{{ route('admin.activity_logs.index') }}" class="uppercase text-xs font-bold">Ver Todos
                                    os Logs</a>
                            </div>
                        </div>

                        {{-- Jobs Status Mini Chart --}}
                        <div class="card card-dark mt-3">
                            <div class="card-header border-0 py-2">
                                <h3 class="card-title text-sm"><i class="fas fa-tasks mr-2"></i>Jobs do Sistema</h3>
                            </div>
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Pendentes: <strong>{{ $pendingJobsCount ?? 0 }}</strong></span>
                                    <div style="width: 100px; height: 50px;">
                                        <canvas id="jobsStatusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Customer Health Gadget --}}
                        <div class="card card-outline card-success mt-3">
                            <div class="card-header border-0 py-2">
                                <h3 class="card-title text-sm font-weight-bold text-success"><i
                                        class="fas fa-heartbeat mr-2"></i>Saúde do Cliente</h3>
                            </div>
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <canvas id="customerHealthChart" style="height: 100px; max-height: 100px;"></canvas>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-xs mb-1"><i class="fas fa-circle text-success mr-1"></i> Alta:
                                            <strong>{{ $customerHealth['Alta'] }}</strong></div>
                                        <div class="text-xs mb-1"><i class="fas fa-circle text-warning mr-1"></i> Média:
                                            <strong>{{ $customerHealth['Média'] }}</strong></div>
                                        <div class="text-xs"><i class="fas fa-circle text-danger mr-1"></i> Baixa:
                                            <strong>{{ $customerHealth['Baixa'] }}</strong></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer py-2 text-center">
                                <small class="text-muted">Proporção de engajamento e planos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- MEMBER DASHBOARD (If not admin, or simplified view) --}}
        @if(!isset($isAdmin) || !$isAdmin)
            <div class="col-12">
                <div class="row">
                    <div class="col-md-4">
                        <x-widgets.metric title="Meus Cursos" icon="fas fa-graduation-cap" :value="$coursesCount ?? 0"
                            color="blue" />
                    </div>
                    <div class="col-md-4">
                        <x-widgets.metric title="Minhas Mentorias" icon="fas fa-chalkboard-teacher" :value="$mentorshipsCount ?? 0" color="green" />
                    </div>
                    <div class="col-md-4">
                        <x-widgets.metric title="Meus Eventos" icon="fas fa-calendar-alt" :value="$eventsCount ?? 0"
                            color="yellow" />
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-lg-8">
                        <div class="card card-outline card-primary shadow-sm">
                            <div class="card-header border-0">
                                <h3 class="card-title"><i class="fas fa-calendar-day mr-2"></i>Meu Calendário</h3>
                            </div>
                            <div class="card-body p-0">
                                <div id="calendar" style="width: 100%; min-height: 500px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card card-outline card-info shadow-sm">
                            <div class="card-header border-0">
                                <h3 class="card-title"><i class="fas fa-bullhorn mr-2"></i>Novidades</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Nenhuma novidade no momento.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    {{-- FullCalendar CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        .fc-event {
            cursor: pointer;
        }

        .fc-toolbar-title {
            font-size: 1.1rem !important;
        }

        .fc-button {
            font-size: 0.85rem !important;
        }
    </style>
@endpush

@push('scripts')
    {{-- Chart.js & FullCalendar JS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/pt-br.js"></script>

    <script>
        function fetchMpBalance() {
            const el = document.getElementById('mp-balance-value');
            if (!el) return;

            el.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i>';

            fetch('{{ route('admin.dashboard.balance') }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const total = data.balance.total_amount || 0;
                        el.innerText = 'R$ ' + parseFloat(total).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                    } else {
                        el.innerHTML = '<span class="text-xs">Erro</span>';
                    }
                })
                .catch(err => {
                    console.error(err);
                    el.innerHTML = '<span class="text-xs">Erro</span>';
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Fetch Balance immediately
            @if(isset($isAdmin) && $isAdmin)
                fetchMpBalance();
            @endif

                    // --- 1. Init FullCalendar ---
                    var calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'pt-br',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    themeSystem: 'bootstrap',
                    events: {!! json_encode($calendarEvents ?? []) !!},
                    height: 'auto',
                    contentHeight: 500,
                    eventClick: function (info) {
                        if (info.event.url) {
                            info.jsEvent.preventDefault();
                            if (info.event.url) window.location.href = info.event.url;
                        }
                    }
                });
                calendar.render();
            }

            // --- 2. Admin Charts (Only if elements exist) ---
            @if(isset($isAdmin) && $isAdmin)
                // Sales Chart
                const salesCtx = document.getElementById('salesChart');
                if (salesCtx) {
                    new Chart(salesCtx, {
                        type: 'line',
                        data: {
                            labels: {!! json_encode($months ?? []) !!},
                            datasets: [{
                                label: 'Vendas (R$)',
                                data: {!! json_encode($salesChartData ?? []) !!},
                                borderColor: '#007bff',
                                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true, grid: { color: '#efefef' } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }

                // Orders Status Chart
                const ordersCtx = document.getElementById('ordersStatusChart');
                if (ordersCtx) {
                    new Chart(ordersCtx, {
                        type: 'doughnut',
                        data: {
                            labels: {!! json_encode(array_keys($ordersByStatus ?? [])) !!},
                            datasets: [{
                                data: {!! json_encode(array_values($ordersByStatus ?? [])) !!},
                                backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d', '#007bff']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'right', labels: { boxWidth: 10, font: { size: 11 } } }
                            }
                        }
                    });
                }

                // Jobs Status (Mini)
                const jobsCtx = document.getElementById('jobsStatusChart');
                if (jobsCtx) {
                    new Chart(jobsCtx, {
                        type: 'pie',
                        data: {
                            labels: ['Pendentes', 'Concluídos'],
                            datasets: [{
                                data: [{{ $pendingJobsCount ?? 0 }}, {{ $jobsStatus['Concluídos'] ?? 0 }}],
                                backgroundColor: ['#dc3545', '#28a745']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } }
                        }
                    });
                }

                // Customer Health Chart
                const healthCtx = document.getElementById('customerHealthChart');
                if (healthCtx) {
                    new Chart(healthCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Alta', 'Média', 'Baixa'],
                            datasets: [{
                                data: [{{ $customerHealth['Alta'] }}, {{ $customerHealth['Média'] }}, {{ $customerHealth['Baixa'] }}],
                                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });
                }
            @endif
                    });
    </script>
@endpush