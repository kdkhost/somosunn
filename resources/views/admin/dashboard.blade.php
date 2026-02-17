@extends('admin.layouts.app')

@section('page_title', 'Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    @if(!$isAdmin)
        <div class="row">
            <div class="col-12">
                <div class="card bg-gradient-navy shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                @if(auth()->user()->photo)
                                    <img src="{{ asset(auth()->user()->photo) }}"
                                        class="rounded-circle border border-light elevation-2"
                                        style="width: 80px; height: 80px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center border border-light elevation-2"
                                        style="width: 80px; height: 80px; font-size: 32px;">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="col">
                                <h2 class="h4 font-weight-bold mb-1">Olá, {{ auth()->user()->name }}!</h2>
                                <p class="mb-0 text-light opacity-75">Bem-vindo ao seu painel UNN. Aqui você gerencia seus
                                    cursos, mentorias e conexões.</p>
                            </div>
                            <div class="col-md-auto mt-3 mt-md-0 text-md-right">
                                <div class="badge badge-light p-2 px-3 shadow-sm">
                                    <i class="fas fa-crown text-warning mr-1"></i>
                                    Plano: <span
                                        class="font-weight-bold text-primary">{{ auth()->user()->activePlan() ? auth()->user()->activePlan()->name : 'Acesso Limitado' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isAdmin)
    <form id="dashboardFilterForm" class="mb-3 d-flex flex-wrap align-items-center gap-2">
        <label class="mr-2 font-weight-bold">Período:</label>
        <select name="period" id="dashboardPeriod" class="form-control form-control-sm w-auto">
            <option value="30">Últimos 30 dias</option>
            <option value="7">Últimos 7 dias</option>
            <option value="90">Últimos 90 dias</option>
            <option value="365">Ano atual</option>
        </select>
        <button type="button" class="btn btn-sm btn-outline-primary ml-2" id="refreshDashboardBtn"><i class="fas fa-sync-alt"></i> Atualizar</button>
    </form>
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="small-box bg-gradient-success shadow-lg position-relative">
                <div class="inner">
                    <h3 class="display-4 mb-1">R$ {{ number_format($totalRevenue ?? 0, 2, ',', '.') }}</h3>
                    <p class="font-weight-bold">Saldo Total <span class="badge badge-light ml-1" title="Vendas confirmadas"><i class="fas fa-check-circle text-success"></i></span></p>
                </div>
                <div class="icon" style="top:10px;right:10px;"><i class="fas fa-wallet fa-3x"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="small-box bg-gradient-danger shadow-lg position-relative">
                <div class="inner">
                    <h3 class="display-4 mb-1">R$ {{ number_format($refundedAmount ?? 0, 2, ',', '.') }}</h3>
                    <p class="font-weight-bold">Reembolsados <span class="badge badge-light ml-1" title="Pedidos reembolsados"><i class="fas fa-undo"></i></span></p>
                </div>
                <div class="icon" style="top:10px;right:10px;"><i class="fas fa-undo fa-3x"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="small-box bg-gradient-warning shadow-lg position-relative">
                <div class="inner">
                    <h3 class="display-4 mb-1">{{ $totalUsers ?? 0 }}</h3>
                    <p class="font-weight-bold">Usuários Registrados <span class="badge badge-light ml-1" title="Contas ativas"><i class="fas fa-users"></i></span></p>
                </div>
                <div class="icon" style="top:10px;right:10px;"><i class="fas fa-users fa-3x"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="small-box bg-gradient-info shadow-lg position-relative">
                <div class="inner">
                    <h3 class="display-4 mb-1">{{ $totalOrders ?? 0 }}</h3>
                    <p class="font-weight-bold">Total de Pedidos <span class="badge badge-light ml-1" title="Pedidos realizados"><i class="fas fa-shopping-bag"></i></span></p>
                </div>
                <div class="icon" style="top:10px;right:10px;"><i class="fas fa-shopping-bag fa-3x"></i></div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h5 class="mb-2 font-weight-bold text-secondary"><i class="fas fa-chart-bar mr-1"></i>Monitoramento Geral</h5>
            <hr class="mt-0 mb-3">
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
            <div class="info-box bg-primary shadow-sm h-100">
                <span class="info-box-icon"><i class="fas fa-graduation-cap fa-2x"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Cursos</span>
                    <span class="info-box-number">{{ $coursesCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
            <div class="info-box bg-success shadow-sm h-100">
                <span class="info-box-icon"><i class="fas fa-chalkboard-teacher fa-2x"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Mentorias</span>
                    <span class="info-box-number">{{ $mentorshipsCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
            <div class="info-box bg-warning shadow-sm h-100">
                <span class="info-box-icon"><i class="fas fa-calendar-alt fa-2x"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Eventos</span>
                    <span class="info-box-number">{{ $eventsCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
            <div class="info-box bg-secondary shadow-sm h-100">
                <span class="info-box-icon"><i class="fas fa-certificate fa-2x"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Certificados</span>
                    <span class="info-box-number">{{ $certificatesCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
            <div class="info-box bg-dark shadow-sm h-100">
                <span class="info-box-icon"><i class="fas fa-tasks fa-2x"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Jobs Pendentes</span>
                    <span class="info-box-number">{{ $pendingJobsCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
            <div class="info-box bg-light shadow-sm h-100">
                <span class="info-box-icon text-primary"><i class="fas fa-history fa-2x"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Logs</span>
                    <span class="info-box-number">{{ $logsCount ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6 col-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-2">
                    <h6 class="card-title mb-0 font-weight-bold"><i class="fas fa-chart-line mr-1"></i>Histórico de Vendas</h6>
                </div>
                <div class="card-body pt-0" style="height:320px; min-height:320px; max-height:320px;">
                    <canvas id="salesChart" style="height:100% !important; max-height:100% !important;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-2">
                    <h6 class="card-title mb-0 font-weight-bold"><i class="fas fa-chart-pie mr-1"></i>Pedidos por Status</h6>
                </div>
                <div class="card-body pt-0" style="height:320px; min-height:320px; max-height:320px;">
                    <canvas id="ordersStatusChart" style="height:100% !important; max-height:100% !important;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-2">
                    <h6 class="card-title mb-0 font-weight-bold"><i class="fas fa-user-plus mr-1"></i>Novos Usuários por Mês</h6>
                </div>
                <div class="card-body pt-0" style="height:320px; min-height:320px; max-height:320px;">
                    <canvas id="usersByMonthChart" style="height:100% !important; max-height:100% !important;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-2">
                    <h6 class="card-title mb-0 font-weight-bold"><i class="fas fa-layer-group mr-1"></i>Distribuição de Conteúdo</h6>
                </div>
                <div class="card-body pt-0" style="height:320px; min-height:320px; max-height:320px;">
                    <canvas id="contentDistributionChart" style="height:100% !important; max-height:100% !important;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-2">
                    <h6 class="card-title mb-0 font-weight-bold"><i class="fas fa-certificate mr-1"></i>Certificados Emitidos por Mês</h6>
                </div>
                <div class="card-body pt-0" style="height:320px; min-height:320px; max-height:320px;">
                    <canvas id="certificatesByMonthChart" style="height:100% !important; max-height:100% !important;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-2">
                    <h6 class="card-title mb-0 font-weight-bold"><i class="fas fa-tasks mr-1"></i>Jobs Pendentes x Concluídos</h6>
                </div>
                <div class="card-body pt-0" style="height:320px; min-height:320px; max-height:320px;">
                    <canvas id="jobsStatusChart" style="height:100% !important; max-height:100% !important;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-2">
                    <h6 class="card-title mb-0 font-weight-bold"><i class="fas fa-history mr-1"></i>Logs por Tipo</h6>
                </div>
                <div class="card-body pt-0" style="height:320px; min-height:320px; max-height:320px;">
                    <canvas id="logsByTypeChart" style="height:100% !important; max-height:100% !important;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-2">
                    <h6 class="card-title mb-0 font-weight-bold"><i class="fas fa-calendar-alt mr-1"></i>Calendário</h6>
                </div>
                <div class="card-body pt-0">
                    <div id="calendar" style="width:100%; min-height: 350px; max-height: 400px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Novos info-boxes de monitoramento -->
    <div class="row flex-wrap" style="gap: 0.5rem;">
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
            <div class="info-box bg-primary" style="min-height: 90px;">
                <span class="info-box-icon" style="min-width:56px;"><i class="fas fa-graduation-cap"></i></span>
                <div class="info-box-content" style="white-space:normal;">
                    <span class="info-box-text" style="font-size:1.05rem;">Cursos</span>
                    <span class="info-box-number" style="font-size:1.3rem;">{{ $coursesCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
            <div class="info-box bg-success" style="min-height: 90px;">
                <span class="info-box-icon" style="min-width:56px;"><i class="fas fa-chalkboard-teacher"></i></span>
                <div class="info-box-content" style="white-space:normal;">
                    <span class="info-box-text" style="font-size:1.05rem;">Mentorias</span>
                    <span class="info-box-number" style="font-size:1.3rem;">{{ $mentorshipsCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
            <div class="info-box bg-warning" style="min-height: 90px;">
                <span class="info-box-icon" style="min-width:56px;"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content" style="white-space:normal;">
                    <span class="info-box-text" style="font-size:1.05rem;">Eventos</span>
                    <span class="info-box-number" style="font-size:1.3rem;">{{ $eventsCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
            <div class="info-box bg-secondary" style="min-height: 90px;">
                <span class="info-box-icon" style="min-width:56px;"><i class="fas fa-certificate"></i></span>
                <div class="info-box-content" style="white-space:normal;">
                    <span class="info-box-text" style="font-size:1.05rem;">Certificados</span>
                    <span class="info-box-number" style="font-size:1.3rem;">{{ $certificatesCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
            <div class="info-box bg-dark" style="min-height: 90px;">
                <span class="info-box-icon" style="min-width:56px;"><i class="fas fa-tasks"></i></span>
                <div class="info-box-content" style="white-space:normal;">
                    <span class="info-box-text" style="font-size:1.05rem;">Jobs Pendentes</span>
                    <span class="info-box-number" style="font-size:1.3rem;">{{ $pendingJobsCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-2">
            <div class="info-box bg-light" style="min-height: 90px;">
                <span class="info-box-icon text-primary" style="min-width:56px;"><i class="fas fa-history"></i></span>
                <div class="info-box-content" style="white-space:normal;">
                    <span class="info-box-text" style="font-size:1.05rem;">Logs</span>
                    <span class="info-box-number" style="font-size:1.3rem;">{{ $logsCount ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($isAdmin)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 pb-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 font-weight-bold"><i class="fas fa-history mr-1"></i>Últimas Atividades do Sistema</h6>
                    <!-- <a href="#" class="btn btn-xs btn-outline-secondary disabled" title="Funcionalidade em breve">Ver todos</a> -->
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Ação</th>
                                    <th>Usuário</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\ActivityLog::with('user')->latest()->limit(10)->get() as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                        <td><span class="badge badge-info">{{ $log->action }}</span></td>
                                        <td>{{ $log->user ? $log->user->name : '-' }}</td>
                                        <td>{{ Str::limit($log->description, 60) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(!$isAdmin)
    <div class="row">
        <div class="col-md-4">
            <x-widgets.metric title="Meus Cursos" icon="fas fa-graduation-cap" :value="$coursesCount ?? 0" color="blue" />
        </div>
        <div class="col-md-4">
            <x-widgets.metric title="Mentorias" icon="fas fa-chalkboard-teacher" :value="$mentorshipsCount ?? 0" color="green" />
        </div>
        <div class="col-md-4">
            <x-widgets.metric title="Eventos" icon="fas fa-calendar-alt" :value="$eventsCount ?? 0" color="yellow" />
        </div>
    </div>
    @endif
    </div>

    @if($isAdmin)
        <div class="row">
            <section class="col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title"><i class="fas fa-chart-line me-2"></i>Histórico de Vendas</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="100"></canvas>
                    </div>
                </div>
            </section>
        </div>
    @endif

    <div class="row">
        <section class="col-lg-5 connectedSortable">
            <!-- Calendar -->
            <div class="card bg-gradient-success">
                <div class="card-header border-0">
                    <h3 class="card-title">
                        <i class="far fa-calendar-alt"></i>
                        Calendário
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div id="calendar" style="width: 100%"></div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/core/main.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/daygrid/main.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/bootstrap/main.min.css">
    <style>
        .fc-header-toolbar {
            font-size: 0.9em;
        }

        .fc-toolbar.fc-header-toolbar {
            margin-bottom: 0.5em;
        }

        .fc-button {
            padding: 0.2rem 0.5rem;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/core/main.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/daygrid/main.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/interaction/main.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/bootstrap/main.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/core/locales/pt-br.js"></script>
    <script>
        $(function () {
            // Filtro de período (apenas visual, precisa integração backend para filtrar de verdade)
            $('#refreshDashboardBtn').on('click', function(e) {
                e.preventDefault();
                alert('Filtro de período ainda não implementado no backend. (Exemplo visual)');
            });
            // Gráfico de vendas (linha)
            new Chart(document.getElementById('salesChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($months) !!},
                    datasets: [{
                        label: 'Vendas (R$)',
                        data: {!! json_encode($salesChartData) !!},
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        borderColor: '#28a745',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Gráfico de pizza: Pedidos por status
            new Chart(document.getElementById('ordersStatusChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: {!! json_encode(array_keys($ordersByStatus)) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($ordersByStatus)) !!},
                        backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6c757d', '#007bff']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Gráfico de barras: Novos usuários por mês
            new Chart(document.getElementById('usersByMonthChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($usersByMonth)) !!},
                    datasets: [{
                        label: 'Novos Usuários',
                        data: {!! json_encode(array_values($usersByMonth)) !!},
                        backgroundColor: '#007bff'
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Gráfico de barras horizontais: Distribuição de conteúdo
            new Chart(document.getElementById('contentDistributionChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($contentDistribution)) !!},
                    datasets: [{
                        label: 'Quantidade',
                        data: {!! json_encode(array_values($contentDistribution)) !!},
                        backgroundColor: ['#007bff', '#28a745', '#ffc107']
                    }]
                },
                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false }
            });

            // Gráfico de linha: Certificados emitidos por mês
            new Chart(document.getElementById('certificatesByMonthChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_keys($certificatesByMonth)) !!},
                    datasets: [{
                        label: 'Certificados',
                        data: {!! json_encode(array_values($certificatesByMonth)) !!},
                        backgroundColor: 'rgba(108,117,125,0.2)',
                        borderColor: '#6c757d',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Gráfico doughnut: Jobs pendentes x concluídos
            new Chart(document.getElementById('jobsStatusChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($jobsStatus)) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($jobsStatus)) !!},
                        backgroundColor: ['#343a40', '#28a745']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Gráfico de pizza: Logs por tipo
            new Chart(document.getElementById('logsByTypeChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: {!! json_encode(array_keys($logsByType)) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($logsByType)) !!},
                        backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d', '#17a2b8']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Calendar Widget
            var calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    plugins: ['dayGrid', 'interaction', 'bootstrap'],
                    themeSystem: 'bootstrap',
                    locale: 'pt-br',
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth'
                    },
                    contentHeight: 350,
                    height: null,
                    events: {!! json_encode($calendarEvents ?? []) !!},
                    editable: false
                });
                calendar.render();
            }
        });
    </script>
@endpush