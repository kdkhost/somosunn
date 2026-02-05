@extends('admin.layouts.app')

@section('page_title', 'Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    @if($isAdmin)
        <div class="row">
            <!-- Balance -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>R$ {{ number_format($totalRevenue, 2, ',', '.') }}</h3>
                        <p>Saldo Total (Vendas)</p>
                    </div>
                    <div class="icon"><i class="fas fa-wallet"></i></div>
                    <a href="{{ route('admin.orders.index') }}" class="small-box-footer">Ver detalhes <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <!-- Refunds -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>R$ {{ number_format($refundedAmount, 2, ',', '.') }}</h3>
                        <p>Reembolsados</p>
                    </div>
                    <div class="icon"><i class="fas fa-undo"></i></div>
                    <a href="{{ route('admin.orders.index') }}" class="small-box-footer">Ver detalhes <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <!-- Users -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $totalUsers }}</h3>
                        <p>Usuários Registrados</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <a href="{{ route('admin.users.index') }}" class="small-box-footer">Gerenciar <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <!-- Orders -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $totalOrders }}</h3>
                        <p>Total de Pedidos</p>
                    </div>
                    <div class="icon"><i class="fas fa-shopping-bag"></i></div>
                    <a href="{{ route('admin.orders.index') }}" class="small-box-footer">Ver todos <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

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
    @else
        {{-- DASHBOARD DE MEMBRO --}}
        <div class="row">
            {{-- Banner Removido --}}

            <div class="col-md-4">
                <div class="info-box mb-3 bg-gradient-primary">
                    <span class="info-box-icon"><i class="fas fa-graduation-cap"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Meus Cursos</span>
                        <span class="info-box-number">Acessar</span>
                    </div>
                    <!-- /.info-box-content -->
                    <a href="{{ route('courses.index') }}" class="stretched-link"></a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-box mb-3 bg-gradient-success">
                    <span class="info-box-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Mentorias</span>
                        <span class="info-box-number">Ver Disponíveis</span>
                    </div>
                    <a href="{{ route('mentorships.index') }}" class="stretched-link"></a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-box mb-3 bg-gradient-warning">
                    <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Eventos</span>
                        <span class="info-box-number">Calendário</span>
                    </div>
                    <a href="{{ route('events.index') }}" class="stretched-link"></a>
                </div>
            </div>
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