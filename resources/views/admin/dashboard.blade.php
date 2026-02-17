                <!-- Gráficos principais -->
                <div class="row">
                    <div class="col-lg-8 col-12">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Histórico de Vendas</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart" style="height:220px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="card card-info card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Pedidos por Status</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="ordersStatusChart" style="height:220px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
        <!-- KPIs principais -->
        <div class="row mb-3">
            <div class="col-6 col-md-3 mb-3">
                <div class="card bg-success text-white h-100" data-toggle="tooltip" title="Receita total acumulada." aria-label="Saldo Total">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs">Saldo Total</div>
                                <div class="h4 mb-0">R$ {{ number_format($totalRevenue ?? 0, 2, ',', '.') }}</div>
                            </div>
                            <i class="fas fa-wallet fa-2x"></i>
                        </div>
                        <canvas id="saldoChart" height="30" aria-label="Gráfico de saldo"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card bg-danger text-white h-100" data-toggle="tooltip" title="Total reembolsado no período." aria-label="Reembolsados">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs">Reembolsados</div>
                                <div class="h4 mb-0">R$ {{ number_format($refundedAmount ?? 0, 2, ',', '.') }}</div>
                            </div>
                            <i class="fas fa-undo fa-2x"></i>
                        </div>
                        <canvas id="reembolsoChart" height="30" aria-label="Gráfico de reembolso"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card bg-warning text-dark h-100" data-toggle="tooltip" title="Total de usuários cadastrados." aria-label="Usuários">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs">Usuários</div>
                                <div class="h4 mb-0">{{ $totalUsers ?? 0 }}</div>
                            </div>
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <canvas id="usuariosChart" height="30" aria-label="Gráfico de usuários"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card bg-info text-white h-100" data-toggle="tooltip" title="Total de pedidos realizados." aria-label="Pedidos">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs">Pedidos</div>
                                <div class="h4 mb-0">{{ $totalOrders ?? 0 }}</div>
                            </div>
                            <i class="fas fa-shopping-bag fa-2x"></i>
                        </div>
                        <canvas id="pedidosChart" height="30" aria-label="Gráfico de pedidos"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid de métricas secundárias -->
        <div class="row mb-3">
            <div class="col-6 col-md-2 mb-3">
                <div class="card bg-primary text-white h-100" data-toggle="tooltip" title="Total de cursos cadastrados." aria-label="Cursos">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs">Cursos</div>
                                <div class="h5 mb-0">{{ $coursesCount ?? 0 }}</div>
                            </div>
                            <i class="fas fa-graduation-cap fa-lg"></i>
                        </div>
                        <canvas id="cursosChart" height="20" aria-label="Gráfico de cursos"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2 mb-3">
                <div class="card bg-success text-white h-100" data-toggle="tooltip" title="Total de mentorias ativas." aria-label="Mentorias">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs">Mentorias</div>
                                <div class="h5 mb-0">{{ $mentorshipsCount ?? 0 }}</div>
                            </div>
                            <i class="fas fa-chalkboard-teacher fa-lg"></i>
                        </div>
                        <canvas id="mentoriasChart" height="20" aria-label="Gráfico de mentorias"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2 mb-3">
                <div class="card bg-warning text-dark h-100" data-toggle="tooltip" title="Total de eventos realizados." aria-label="Eventos">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs">Eventos</div>
                                <div class="h5 mb-0">{{ $eventsCount ?? 0 }}</div>
                            </div>
                            <i class="fas fa-calendar-alt fa-lg"></i>
                        </div>
                        <canvas id="eventosChart" height="20" aria-label="Gráfico de eventos"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2 mb-3">
                <div class="card bg-secondary text-white h-100" data-toggle="tooltip" title="Total de certificados emitidos." aria-label="Certificados">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs">Certificados</div>
                                <div class="h5 mb-0">{{ $certificatesCount ?? 0 }}</div>
                            </div>
                            <i class="fas fa-certificate fa-lg"></i>
                        </div>
                        <canvas id="certificadosChart" height="20" aria-label="Gráfico de certificados"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2 mb-3">
                <div class="card bg-dark text-white h-100" data-toggle="tooltip" title="Jobs pendentes de execução." aria-label="Jobs Pendentes">
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs">Jobs Pendentes</div>
                                <div class="h5 mb-0">{{ $pendingJobsCount ?? 0 }}</div>
                            </div>
                            <i class="fas fa-tasks fa-lg"></i>
                        </div>
                        <canvas id="jobsChart" height="20" aria-label="Gráfico de jobs"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- FullCalendar 4 -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-outline card-secondary h-100" title="Calendário de eventos e atividades.">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Calendário</h3>
                    </div>
                    <div class="card-body">
                        <div id="calendar" style="width:100%; min-height: 300px; max-height: 400px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>
        </div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/core/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/daygrid/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/timegrid/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/list/main.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/core/main.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/daygrid/main.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/timegrid/main.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/list/main.min.css">
<script>
function formatUptime(seconds) {
    const d = Math.floor(seconds / 86400);
    const h = Math.floor((seconds % 86400) / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    return `${d}d ${h}h ${m}min`;
}
document.addEventListener('DOMContentLoaded', function() {
    // Uptime fake (substitua por valor real se disponível)
    const uptimeSeconds = 86400 * 12 + 3600 * 6 + 60 * 23; // 12 dias, 6h, 23min
    document.getElementById('uptime').innerText = formatUptime(uptimeSeconds);

    // Ativa tooltips Bootstrap/AdminLTE
    if (window.$ && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    } else {
        document.querySelectorAll('[data-toggle="tooltip"]').forEach(function(el) {
            el.setAttribute('title', el.getAttribute('title'));
        });
    }

    // Mini-gráficos Chart.js
    const miniData = (max) => Array.from({length: 10}, () => Math.floor(Math.random() * max));
    const miniConfig = (color) => ({
        type: 'line',
        data: { labels: Array(10).fill(''), datasets: [{ data: miniData(100), borderColor: color, backgroundColor: color+'22', fill: true, tension: 0.4, pointRadius: 0 }] },
        options: { plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } }, elements: { line: { borderWidth: 2 } }, responsive: false, maintainAspectRatio: false }
    });
    const charts = [
        ['saldoChart', '#28a745'],
        ['reembolsoChart', '#dc3545'],
        ['usuariosChart', '#ffc107'],
        ['pedidosChart', '#17a2b8'],
        ['cursosChart', '#007bff'],
        ['mentoriasChart', '#28a745'],
        ['eventosChart', '#ffc107'],
        ['certificadosChart', '#6c757d'],
        ['jobsChart', '#343a40']
    ];
    charts.forEach(([id, color]) => {
        const el = document.getElementById(id);
        if (el) new Chart(el.getContext('2d'), miniConfig(color));
    });

    // FullCalendar 4
    var calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: ['dayGrid', 'timeGrid', 'list'],
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            locale: 'pt-br',
            height: 350,
            events: [
                { title: 'Evento 1', start: new Date(), color: '#007bff' },
                { title: 'Mentoria', start: new Date(new Date().setDate(new Date().getDate()+2)), color: '#28a745' },
                { title: 'Curso', start: new Date(new Date().setDate(new Date().getDate()+5)), color: '#ffc107' }
            ]
        });
        calendar.render();
    }
});
</script>
@endpush
@extends('admin.layouts.app')

@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- Card de informações do sistema -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-primary card-outline">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Detalhes do Sistema <i class="fas fa-info-circle text-muted ml-1"></i></h3>
                        <span class="badge badge-success">Online</span>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><b>Monitor:</b> {{ request()->getHost() }}</li>
                            <li><b>Tipo:</b> Laravel</li>
                            <li><b>Versão:</b> {{ app()->version() }}</li>
                            <li><b>PHP:</b> {{ phpversion() }}</li>
                            <li><b>Uptime:</b> <span id="uptime">--</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs padrão AdminLTE -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>R$ {{ number_format($totalRevenue ?? 0, 2, ',', '.') }}</h3>
                        <p>Saldo Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>R$ {{ number_format($refundedAmount ?? 0, 2, ',', '.') }}</h3>
                        <p>Reembolsados</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-undo"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $totalUsers ?? 0 }}</h3>
                        <p>Usuários</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $totalOrders ?? 0 }}</h3>
                        <p>Pedidos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards métricas secundárias -->
        <div class="row">
            <div class="col-md-2 col-6">
                <div class="info-box bg-primary">
                    <span class="info-box-icon"><i class="fas fa-graduation-cap"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Cursos</span>
                        <span class="info-box-number">{{ $coursesCount ?? 0 }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Mentorias</span>
                        <span class="info-box-number">{{ $mentorshipsCount ?? 0 }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Eventos</span>
                        <span class="info-box-number">{{ $eventsCount ?? 0 }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="info-box bg-secondary">
                    <span class="info-box-icon"><i class="fas fa-certificate"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Certificados</span>
                        <span class="info-box-number">{{ $certificatesCount ?? 0 }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="info-box bg-dark">
                    <span class="info-box-icon"><i class="fas fa-tasks"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Jobs Pendentes</span>
                        <span class="info-box-number">{{ $pendingJobsCount ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- FullCalendar 4 -->
        <div class="row">
            <div class="col-lg-6 col-12">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Calendário</h3>
                    </div>
                    <div class="card-body">
                        <div id="calendar" style="width:100%; min-height: 300px; max-height: 400px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/core/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/daygrid/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/timegrid/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/list/main.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/core/main.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/daygrid/main.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/timegrid/main.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/list/main.min.css">
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de vendas (exemplo)
        var salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            new Chart(salesCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                    datasets: [{
                        label: 'Vendas',
                        data: [120, 150, 180, 90, 200, 170, 220],
                        borderColor: '#007bff',
                        backgroundColor: '#007bff22',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: { plugins: { legend: { display: false } }, scales: { x: {}, y: {} } }
            });
        }
        // Gráfico de pedidos por status (exemplo)
        var ordersCtx = document.getElementById('ordersStatusChart');
        if (ordersCtx) {
            new Chart(ordersCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Sucesso', 'Pendente', 'Falha'],
                    datasets: [{
                        data: [60, 25, 15],
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                    }]
                },
                options: { plugins: { legend: { display: true } } }
            });
        }
        // FullCalendar
        var calendarEl = document.getElementById('calendar');
        if (calendarEl) {
            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['dayGrid', 'timeGrid', 'list'],
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                locale: 'pt-br',
                height: 350,
                events: [
                    { title: 'Evento 1', start: new Date(), color: '#007bff' },
                    { title: 'Mentoria', start: new Date(new Date().setDate(new Date().getDate()+2)), color: '#28a745' },
                    { title: 'Curso', start: new Date(new Date().setDate(new Date().getDate()+5)), color: '#ffc107' }
                ]
            });
            calendar.render();
        }
    });
    </script>
    @endpush
    </div>
</section>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Card de informações do sistema -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-primary h-100" data-toggle="tooltip" title="Informações do sistema e ambiente." aria-label="Detalhes do Sistema">
                <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
                    <h3 class="card-title text-sm">Detalhes do Sistema <i class="fas fa-info-circle text-muted ml-1"></i></h3>
                    <span class="badge badge-success" aria-label="Status do sistema">Online</span>
                </div>
                <div class="card-body p-2">
                    <ul class="list-unstyled mb-0">
                        <li><b>Monitor:</b> {{ request()->getHost() }}</li>
                        <li><b>Tipo:</b> Laravel</li>
                        <li><b>Versão:</b> {{ app()->version() }}</li>
                        <li><b>PHP:</b> {{ phpversion() }}</li>
                        <li><b>Uptime:</b> <span id="uptime">--</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs e métricas principais -->
    <div class="row mb-3">
        <div class="col-6 col-md-3 mb-3">
            <div class="card bg-success text-white h-100" data-toggle="tooltip" title="Receita total acumulada." aria-label="Saldo Total">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Saldo Total</div>
                            <div class="h4 mb-0">R$ {{ number_format($totalRevenue ?? 0, 2, ',', '.') }}</div>
                        </div>
                        <i class="fas fa-wallet fa-2x"></i>
                    </div>
                    <canvas id="saldoChart" height="30" aria-label="Gráfico de saldo"></canvas>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card bg-danger text-white h-100" data-toggle="tooltip" title="Total reembolsado no período." aria-label="Reembolsados">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Reembolsados</div>
                            <div class="h4 mb-0">R$ {{ number_format($refundedAmount ?? 0, 2, ',', '.') }}</div>
                        </div>
                        <i class="fas fa-undo fa-2x"></i>
                    </div>
                    <canvas id="reembolsoChart" height="30" aria-label="Gráfico de reembolso"></canvas>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card bg-warning text-dark h-100" data-toggle="tooltip" title="Total de usuários cadastrados." aria-label="Usuários">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Usuários</div>
                            <div class="h4 mb-0">{{ $totalUsers ?? 0 }}</div>
                        </div>
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <canvas id="usuariosChart" height="30" aria-label="Gráfico de usuários"></canvas>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card bg-info text-white h-100" data-toggle="tooltip" title="Total de pedidos realizados." aria-label="Pedidos">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Pedidos</div>
                            <div class="h4 mb-0">{{ $totalOrders ?? 0 }}</div>
                        </div>
                        <i class="fas fa-shopping-bag fa-2x"></i>
                    </div>
                    <canvas id="pedidosChart" height="30" aria-label="Gráfico de pedidos"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de métricas secundárias -->
    <div class="row mb-3">
        <div class="col-6 col-md-2 mb-3">
            <div class="card bg-primary text-white h-100" data-toggle="tooltip" title="Total de cursos cadastrados." aria-label="Cursos">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Cursos</div>
                            <div class="h5 mb-0">{{ $coursesCount ?? 0 }}</div>
                        </div>
                        <i class="fas fa-graduation-cap fa-lg"></i>
                    </div>
                    <canvas id="cursosChart" height="20" aria-label="Gráfico de cursos"></canvas>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2 mb-3">
            <div class="card bg-success text-white h-100" data-toggle="tooltip" title="Total de mentorias ativas." aria-label="Mentorias">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Mentorias</div>
                            <div class="h5 mb-0">{{ $mentorshipsCount ?? 0 }}</div>
                        </div>
                        <i class="fas fa-chalkboard-teacher fa-lg"></i>
                    </div>
                    <canvas id="mentoriasChart" height="20" aria-label="Gráfico de mentorias"></canvas>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2 mb-3">
            <div class="card bg-warning text-dark h-100" data-toggle="tooltip" title="Total de eventos realizados." aria-label="Eventos">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Eventos</div>
                            <div class="h5 mb-0">{{ $eventsCount ?? 0 }}</div>
                        </div>
                        <i class="fas fa-calendar-alt fa-lg"></i>
                    </div>
                    <canvas id="eventosChart" height="20" aria-label="Gráfico de eventos"></canvas>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2 mb-3">
            <div class="card bg-secondary text-white h-100" data-toggle="tooltip" title="Total de certificados emitidos." aria-label="Certificados">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Certificados</div>
                            <div class="h5 mb-0">{{ $certificatesCount ?? 0 }}</div>
                        </div>
                        <i class="fas fa-certificate fa-lg"></i>
                    </div>
                    <canvas id="certificadosChart" height="20" aria-label="Gráfico de certificados"></canvas>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2 mb-3">
            <div class="card bg-dark text-white h-100" data-toggle="tooltip" title="Jobs pendentes de execução." aria-label="Jobs Pendentes">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Jobs Pendentes</div>
                            <div class="h5 mb-0">{{ $pendingJobsCount ?? 0 }}</div>
                        </div>
                        <i class="fas fa-tasks fa-lg"></i>
                    </div>
                    <canvas id="jobsChart" height="20" aria-label="Gráfico de jobs"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- FullCalendar 4 -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-secondary h-100" title="Calendário de eventos e atividades.">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Calendário</h3>
                </div>
                <div class="card-body">
                    <div id="calendar" style="width:100%; min-height: 300px; max-height: 400px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/core/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/daygrid/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/timegrid/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/list/main.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/core/main.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/daygrid/main.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/timegrid/main.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@4.4.2/list/main.min.css">
<script>
function formatUptime(seconds) {
    const d = Math.floor(seconds / 86400);
    const h = Math.floor((seconds % 86400) / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    return `${d}d ${h}h ${m}min`;
}
document.addEventListener('DOMContentLoaded', function() {
    // Uptime fake (substitua por valor real se disponível)
    const uptimeSeconds = 86400 * 12 + 3600 * 6 + 60 * 23; // 12 dias, 6h, 23min
    document.getElementById('uptime').innerText = formatUptime(uptimeSeconds);

    // Ativa tooltips Bootstrap/AdminLTE
    if (window.$ && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    } else {
        document.querySelectorAll('[data-toggle="tooltip"]').forEach(function(el) {
            el.setAttribute('title', el.getAttribute('title'));
        });
    }

    // Mini-gráficos Chart.js
    const miniData = (max) => Array.from({length: 10}, () => Math.floor(Math.random() * max));
    const miniConfig = (color) => ({
        type: 'line',
        data: { labels: Array(10).fill(''), datasets: [{ data: miniData(100), borderColor: color, backgroundColor: color+'22', fill: true, tension: 0.4, pointRadius: 0 }] },
        options: { plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } }, elements: { line: { borderWidth: 2 } }, responsive: false, maintainAspectRatio: false }
    });
    const charts = [
        ['saldoChart', '#28a745'],
        ['reembolsoChart', '#dc3545'],
        ['usuariosChart', '#ffc107'],
        ['pedidosChart', '#17a2b8'],
        ['cursosChart', '#007bff'],
        ['mentoriasChart', '#28a745'],
        ['eventosChart', '#ffc107'],
        ['certificadosChart', '#6c757d'],
        ['jobsChart', '#343a40']
    ];
    charts.forEach(([id, color]) => {
        const el = document.getElementById(id);
        if (el) new Chart(el.getContext('2d'), miniConfig(color));
    });

    // FullCalendar 4
    var calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: ['dayGrid', 'timeGrid', 'list'],
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            locale: 'pt-br',
            height: 350,
            events: [
                { title: 'Evento 1', start: new Date(), color: '#007bff' },
                { title: 'Mentoria', start: new Date(new Date().setDate(new Date().getDate()+2)), color: '#28a745' },
                { title: 'Curso', start: new Date(new Date().setDate(new Date().getDate()+5)), color: '#ffc107' }
            ]
        });
        calendar.render();
    }
});
</script>
@endpush
@endsection

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
    <form id="dashboardFilterForm" class="mb-4 row align-items-center justify-content-end">
        <div class="col-auto pr-0"><label class="col-form-label font-weight-bold">Período:</label></div>
        <div class="col-auto">
            <select name="period" id="dashboardPeriod" class="form-control form-control-sm rounded-pill">
                <option value="30">Últimos 30 dias</option>
                <option value="7">Últimos 7 dias</option>
                <option value="90">Últimos 90 dias</option>
                <option value="365">Ano atual</option>
            </select>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-4" id="refreshDashboardBtn"><i class="fas fa-sync-alt"></i> Atualizar</button>
        </div>
    </form>
    <!-- Filtros rápidos no topo -->
    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap align-items-center gap-2">
            <form class="form-inline flex-wrap" method="get" action="">
                <div class="form-group mr-2 mb-2">
                    <label for="periodo" class="mr-1">Período:</label>
                    <select name="periodo" id="periodo" class="form-control form-control-sm">
                        <option value="7">Últimos 7 dias</option>
                        <option value="30">Últimos 30 dias</option>
                        <option value="90">Últimos 90 dias</option>
                        <option value="all">Todo o período</option>
                    </select>
                </div>
                <div class="form-group mr-2 mb-2">
                    <label for="status" class="mr-1">Status:</label>
                    <select name="status" id="status" class="form-control form-control-sm">
                        <option value="all">Todos</option>
                        <option value="success">Sucesso</option>
                        <option value="pending">Pendente</option>
                        <option value="fail">Falha</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm mb-2">Filtrar</button>
            </form>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-md-3 col-12 mb-2">
            <div class="card card-outline card-primary h-100" data-toggle="tooltip" title="Informações do sistema e ambiente." aria-label="Detalhes do Sistema">
                <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
                    <h3 class="card-title text-sm">Detalhes do Sistema <i class="fas fa-info-circle text-muted ml-1"></i></h3>
                    <span class="badge badge-success" aria-label="Status do sistema">Online</span>
                </div>
                <div class="card-body p-2">
                    <ul class="list-unstyled mb-0">
                        <li><b>Monitor:</b> {{ request()->getHost() }}</li>
                        <li><b>Tipo:</b> Laravel</li>
                        <li><b>Versão:</b> {{ app()->version() }}</li>
                        <li><b>PHP:</b> {{ phpversion() }}</li>
                        <li><b>Uptime:</b> <span id="uptime">--</span></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-12 mb-2">
            <div class="card card-outline card-success h-100" data-toggle="tooltip" title="Percentual de disponibilidade do sistema." aria-label="Disponibilidade">
                <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
                    <h3 class="card-title text-sm">Disponibilidade <i class="fas fa-question-circle text-muted ml-1"></i></h3>
                    <span class="badge badge-success" aria-label="Disponibilidade">99%</span>
                </div>
                <div class="card-body p-2 d-flex align-items-center justify-content-center">
                    <canvas id="availabilityChart" width="80" height="80" aria-label="Gráfico de disponibilidade"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-12 mb-2">
            <div class="row h-100">
                <div class="col-12 col-sm-4 mb-2">
                    <div class="card bg-success text-white h-100" data-toggle="tooltip" title="Receita total acumulada." aria-label="Saldo Total">
                        <div class="card-header py-1 px-2 d-flex justify-content-between align-items-center bg-transparent border-0">
                            <span class="badge badge-success" aria-label="Status financeiro">OK</span>
                        </div>
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-xs">Saldo Total</div>
                                    <div class="h4 mb-0">R$ {{ number_format($totalRevenue ?? 0, 2, ',', '.') }}</div>
                                </div>
                                <i class="fas fa-wallet fa-2x"></i>
                            </div>
                            <canvas id="saldoChart" height="30" aria-label="Gráfico de saldo"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-4 mb-2">
                    <div class="card bg-danger text-white h-100" data-toggle="tooltip" title="Total reembolsado no período." aria-label="Reembolsados">
                        <div class="card-header py-1 px-2 d-flex justify-content-between align-items-center bg-transparent border-0">
                            <span class="badge badge-warning" aria-label="Status de reembolso">Alerta</span>
                        </div>
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-xs">Reembolsados</div>
                                    <div class="h4 mb-0">R$ {{ number_format($refundedAmount ?? 0, 2, ',', '.') }}</div>
                                </div>
                                <i class="fas fa-undo fa-2x"></i>
                            </div>
                            <canvas id="reembolsoChart" height="30" aria-label="Gráfico de reembolso"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-4 mb-2">
                    <div class="card bg-warning text-dark h-100" data-toggle="tooltip" title="Total de usuários cadastrados." aria-label="Usuários">
                        <div class="card-header py-1 px-2 d-flex justify-content-between align-items-center bg-transparent border-0">
                            <span class="badge badge-info" aria-label="Status de usuários">Estável</span>
                        </div>
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-xs">Usuários</div>
                                    <div class="h4 mb-0">{{ $totalUsers ?? 0 }}</div>
                                </div>
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <canvas id="usuariosChart" height="30" aria-label="Gráfico de usuários"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de métricas principais e secundárias -->
    <div class="row">
        <div class="col-12 col-sm-6 col-md-2 mb-2">
            <div class="card bg-info text-white h-100" data-toggle="tooltip" title="Total de pedidos realizados.">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Pedidos</div>
                            <div class="h5 mb-0">{{ $totalOrders ?? 0 }}</div>
                        </div>
                        <i class="fas fa-shopping-bag fa-lg"></i>
                    </div>
                    <canvas id="pedidosChart" height="20"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-2 mb-2">
            <div class="card bg-primary text-white h-100" data-toggle="tooltip" title="Total de cursos cadastrados.">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Cursos</div>
                            <div class="h5 mb-0">{{ $coursesCount ?? 0 }}</div>
                        </div>
                        <i class="fas fa-graduation-cap fa-lg"></i>
                    </div>
                    <canvas id="cursosChart" height="20"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-2 mb-2">
            <div class="card bg-success text-white h-100" data-toggle="tooltip" title="Total de mentorias ativas.">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Mentorias</div>
                            <div class="h5 mb-0">{{ $mentorshipsCount ?? 0 }}</div>
                        </div>
                        <i class="fas fa-chalkboard-teacher fa-lg"></i>
                    </div>
                    <canvas id="mentoriasChart" height="20"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-2 mb-2">
            <div class="card bg-warning text-dark h-100" data-toggle="tooltip" title="Total de eventos realizados.">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Eventos</div>
                            <div class="h5 mb-0">{{ $eventsCount ?? 0 }}</div>
                        </div>
                        <i class="fas fa-calendar-alt fa-lg"></i>
                    </div>
                    <canvas id="eventosChart" height="20"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-2 mb-2">
            <div class="card bg-secondary text-white h-100" data-toggle="tooltip" title="Total de certificados emitidos.">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Certificados</div>
                            <div class="h5 mb-0">{{ $certificatesCount ?? 0 }}</div>
                        </div>
                        <i class="fas fa-certificate fa-lg"></i>
                    </div>
                    <canvas id="certificadosChart" height="20"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-2 mb-2">
            <div class="card bg-dark text-white h-100" data-toggle="tooltip" title="Jobs pendentes de execução.">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs">Jobs Pendentes</div>
                            <div class="h5 mb-0">{{ $pendingJobsCount ?? 0 }}</div>
                        </div>
                        <i class="fas fa-tasks fa-lg"></i>
                    </div>
                    <canvas id="jobsChart" height="20"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Linha de gráficos maiores -->
    <div class="row mt-2">
        <div class="col-lg-8 col-12 mb-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Histórico de Vendas</h3>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" style="height:220px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-12 mb-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Pedidos por Status</h3>
                </div>
                <div class="card-body">
                    <canvas id="ordersStatusChart" style="height:220px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Linha de calendário -->
    <div class="row mt-2">
        <div class="col-lg-5 col-12 mb-2">
            <div class="card" title="Calendário de eventos e atividades.">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Calendário</h3>
                </div>
                <div class="card-body">
                    <div id="calendar" style="width:100%; min-height: 180px; max-height: 220px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Função para uptime fake (exemplo, pode ser substituído por valor real via backend)
    function formatUptime(seconds) {
        const d = Math.floor(seconds / 86400);
        const h = Math.floor((seconds % 86400) / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        return `${d}d ${h}h ${m}min`;
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Uptime fake (substitua por valor real se disponível)
        const uptimeSeconds = 86400 * 12 + 3600 * 6 + 60 * 23; // 12 dias, 6h, 23min
        document.getElementById('uptime').innerText = formatUptime(uptimeSeconds);

        // Ativa tooltips Bootstrap/AdminLTE
        if (window.$ && $.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip();
        } else {
            document.querySelectorAll('[data-toggle="tooltip"]').forEach(function(el) {
                el.setAttribute('title', el.getAttribute('title'));
            });
        }

        // Dados fake para mini-gráficos
        const miniData = (max) => Array.from({length: 10}, () => Math.floor(Math.random() * max));
        const miniConfig = (color) => ({
            type: 'line',
            data: { labels: Array(10).fill(''), datasets: [{ data: miniData(100), borderColor: color, backgroundColor: color+'22', fill: true, tension: 0.4, pointRadius: 0 }] },
            options: { plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } }, elements: { line: { borderWidth: 2 } }, responsive: false, maintainAspectRatio: false }
        });
        const charts = [
            ['saldoChart', '#28a745'],
            ['reembolsoChart', '#dc3545'],
            ['usuariosChart', '#ffc107'],
            ['pedidosChart', '#17a2b8'],
            ['cursosChart', '#007bff'],
            ['mentoriasChart', '#28a745'],
            ['eventosChart', '#ffc107'],
            ['certificadosChart', '#6c757d'],
            ['jobsChart', '#343a40']
        ];
        charts.forEach(([id, color]) => {
            const el = document.getElementById(id);
            if (el) new Chart(el.getContext('2d'), miniConfig(color));
        });
        // Disponibilidade (doughnut)
        const avail = document.getElementById('availabilityChart');
        if (avail) new Chart(avail.getContext('2d'), {
            type: 'doughnut',
            data: { labels: ['Up', 'Down'], datasets: [{ data: [99, 1], backgroundColor: ['#28a745', '#e0e0e0'], borderWidth: 0 }] },
            options: { cutout: '70%', plugins: { legend: { display: false } } }
        });
    });
    </script>
    @endpush
@push('styles')
    <style>
        .kpi-gourmet {
            min-width: 220px;
            max-width: 340px;
            border-radius: 1.2rem;
            transition: box-shadow .15s, transform .15s;
        }
        .kpi-gourmet:hover {
            box-shadow: 0 0 0 4px #007bff22, 0 2px 16px #0001 !important;
            transform: translateY(-2px) scale(1.03);
            z-index: 2;
        }
        .info-box-content .display-4 {
            font-size: 2.2rem;
            font-weight: bold;
            letter-spacing: -1px;
        }
        @media (max-width: 991px) {
            .kpi-gourmet { min-width: 160px !important; }
        }
        @media (max-width: 767px) {
            .kpi-gourmet { min-width: 120px !important; }
            .info-box-content .display-4 { font-size: 1.3rem !important; }
        }
    </style>
@endpush

    <!-- Gráficos premium -->
    <div class="row">
        <div class="col-lg-8 col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Histórico de Vendas</h3>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" style="height:300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Pedidos por Status</h3>
                </div>
                <div class="card-body">
                    <canvas id="ordersStatusChart" style="height:300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4 col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>Novos Usuários por Mês</h3>
                </div>
                <div class="card-body">
                    <canvas id="usersByMonthChart" style="height:250px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-12">
            <div class="row">
                <div class="col-md-4 col-6">
                    <div class="info-box bg-primary">
                        <span class="info-box-icon"><i class="fas fa-graduation-cap"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cursos</span>
                            <span class="info-box-number">{{ $coursesCount ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Mentorias</span>
                            <span class="info-box-number">{{ $mentorshipsCount ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-12">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Eventos</span>
                            <span class="info-box-number">{{ $eventsCount ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-6">
                    <div class="info-box bg-secondary">
                        <span class="info-box-icon"><i class="fas fa-certificate"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Certificados</span>
                            <span class="info-box-number">{{ $certificatesCount ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-6">
                    <div class="info-box bg-dark">
                        <span class="info-box-icon"><i class="fas fa-tasks"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Jobs Pendentes</span>
                            <span class="info-box-number">{{ $pendingJobsCount ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Calendário premium -->
    <div class="row">
        <div class="col-lg-5 col-12">
            <div class="card" title="Calendário de eventos e atividades.">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Calendário</h3>
                </div>
                <div class="card-body">
                    <div id="calendar" style="width:100%; min-height: 250px; max-height: 320px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>
@push('styles')
    <style>
        .kpi-hover:hover, .chart-hover:hover, .info-hover:hover {
            box-shadow: 0 0 0 4px #007bff22, 0 2px 16px #0001 !important;
            transform: translateY(-2px) scale(1.02);
            transition: all 0.15s;
            z-index: 2;
        }
        .kpi-card .display-4, .info-box-number.display-4 {
            color: #222;
            letter-spacing: -1px;
        }
        .card-title, .info-box-text {
            letter-spacing: 0.5px;
        }
        .card-header {
            border-radius: 0.5rem 0.5rem 0 0;
        }
        .card {
            border-radius: 0.7rem;
        }
        @media (max-width: 991px) {
            .kpi-card, .chart-hover, .info-hover {
                min-width: 160px !important;
            }
        }
        @media (max-width: 767px) {
            .kpi-card, .chart-hover, .info-hover {
                min-width: 120px !important;
            }
            .display-4 {
                font-size: 2rem !important;
            }
        }
    </style>
@endpush

    <!-- Cards de métricas secundárias (apenas 5) -->
    <div class="row mb-4">
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
    </div>

    <!-- Calendário discreto -->
    <div class="row mb-4">
        <div class="col-lg-6 col-12 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-2">
                    <h6 class="card-title mb-0 font-weight-bold"><i class="fas fa-calendar-alt mr-1"></i>Calendário</h6>
                </div>
                <div class="card-body pt-0" style="height:350px; min-height:350px; max-height:350px;">
                    <div id="calendar" style="width:100%; min-height: 320px; max-height: 340px; overflow-y: auto;"></div>
                </div>
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