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
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>R$ {{ number_format($totalRevenue ?? 0, 2, ',', '.') }}</h3>
                    <p>Saldo Total (Vendas)</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>R$ {{ number_format($refundedAmount ?? 0, 2, ',', '.') }}</h3>
                    <p>Reembolsados</p>
                </div>
                <div class="icon"><i class="fas fa-undo"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalUsers ?? 0 }}</h3>
                    <p>Usuários Registrados</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalOrders ?? 0 }}</h3>
                    <p>Total de Pedidos</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-bag"></i></div>
            </div>
        </div>
    </div>
    <!-- Novos info-boxes de monitoramento -->
    <div class="row">
        <div class="col-md-2 col-sm-4 col-6">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-graduation-cap"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Cursos</span>
                    <span class="info-box-number">{{ $coursesCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Mentorias</span>
                    <span class="info-box-number">{{ $mentorshipsCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Eventos</span>
                    <span class="info-box-number">{{ $eventsCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="info-box bg-secondary">
                <span class="info-box-icon"><i class="fas fa-certificate"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Certificados</span>
                    <span class="info-box-number">{{ $certificatesCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="info-box bg-dark">
                <span class="info-box-icon"><i class="fas fa-tasks"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Jobs Pendentes</span>
                    <span class="info-box-number">{{ $pendingJobsCount ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="info-box bg-light">
                <span class="info-box-icon text-primary"><i class="fas fa-history"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Logs</span>
                    <span class="info-box-number">{{ $logsCount ?? 0 }}</span>
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
            // Sales Chart
            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
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
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    if (label) { label += ': '; }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) { return 'R$ ' + value; }
                            }
                        }
                    }
                }
            });

            // Calendar Widget
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['dayGrid', 'interaction', 'bootstrap'],
                themeSystem: 'bootstrap',
                locale: 'pt-br',
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                height: 350, // Widget Height
                height: 350, // Widget Height
                events: {!! json_encode($calendarEvents ?? []) !!},
                editable: false // Read-only dashboard widget
            });
            calendar.render();
        });
    </script>
@endpush