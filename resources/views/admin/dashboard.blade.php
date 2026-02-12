@extends('admin.layouts.app')

@section('page_title', 'Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    @php
        $user = auth()->user();
        $isSuper = (bool) ($isSuperadmin ?? false) || (($user->role ?? '') === 'superadmin' || ($user->level ?? '') === 'superadmin');
        $roleLabel = $isSuper ? 'Super Admin' : 'Administrador';
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-navy shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            @if($user && $user->photo)
                                <img src="{{ asset($user->photo) }}"
                                    class="rounded-circle border border-light elevation-2"
                                    style="width: 74px; height: 74px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center border border-light elevation-2"
                                    style="width: 74px; height: 74px; font-size: 30px;">
                                    {{ $user ? substr($user->name ?? '', 0, 1) : '?' }}
                                </div>
                            @endif
                        </div>
                        <div class="col">
                            <h2 class="h4 font-weight-bold mb-1">Olá, {{ $user?->name }}!</h2>
                            <p class="mb-0 text-light opacity-75">
                                Acompanhe métricas, pedidos, faturas e acesso aos módulos da plataforma.
                            </p>
                        </div>
                        <div class="col-md-auto mt-3 mt-md-0 text-md-right">
                            <div class="badge badge-light p-2 px-3 shadow-sm">
                                <i class="fas fa-shield-alt text-primary mr-1"></i>
                                <span class="font-weight-bold text-primary">{{ $roleLabel }}</span>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light mr-2">
                                    <i class="fas fa-users mr-1"></i> Usuários
                                </a>
                                <a href="{{ route('admin.plans.index') }}" class="btn btn-sm btn-outline-light mr-2">
                                    <i class="fas fa-crown mr-1"></i> Planos
                                </a>
                                <a href="{{ route('admin.settings', ['group' => 'general']) }}" class="btn btn-sm btn-outline-light">
                                    <i class="fas fa-cogs mr-1"></i> Configurações
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>R$ {{ number_format((float) $totalRevenue, 2, ',', '.') }}</h3>
                    <p>Receita (Total)</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
                <a href="{{ route('admin.orders.index') }}" class="small-box-footer">
                    Ver pedidos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>R$ {{ number_format((float) $monthRevenue, 2, ',', '.') }}</h3>
                    <p>Receita (Mês)</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
                <a href="{{ route('admin.orders.index') }}" class="small-box-footer">
                    Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-12">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ (int) $pendingOrders }}</h3>
                    <p>Pedidos Pendentes</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                <a href="{{ route('admin.orders.index') }}" class="small-box-footer">
                    Acompanhar <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-12">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ (int) $activeSubscriptions }}</h3>
                    <p>Assinaturas Ativas</p>
                </div>
                <div class="icon"><i class="fas fa-sync-alt"></i></div>
                <a href="{{ route('admin.plans.index') }}" class="small-box-footer">
                    Ver planos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-sm-6 col-12">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>R$ {{ number_format((float) $refundedAmount, 2, ',', '.') }}</h3>
                    <p>Reembolsos</p>
                </div>
                <div class="icon"><i class="fas fa-undo"></i></div>
                <a href="{{ route('admin.orders.index') }}" class="small-box-footer">
                    Ver pedidos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-12">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ (int) $issuedInvoices }}</h3>
                    <p>Faturas (Emitidas/Rascunho)</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice"></i></div>
                <a href="{{ route('admin.invoices.index') }}" class="small-box-footer">
                    Ver faturas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-12">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ (int) $totalUsers }}</h3>
                    <p>Usuários Registrados</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                    Gerenciar <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ (int) $totalOrders }}</h3>
                    <p>Total de Pedidos</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-bag"></i></div>
                <a href="{{ route('admin.orders.index') }}" class="small-box-footer">
                    Ver todos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    @if($isSuper)
        <div class="row">
            <div class="col-lg-3 col-sm-6 col-12">
                <div class="small-box bg-dark">
                    <div class="inner">
                        <h3>{{ (int) $activePlans }}</h3>
                        <p>Planos (Ativos)</p>
                    </div>
                    <div class="icon"><i class="fas fa-layer-group"></i></div>
                    <a href="{{ route('admin.plans.index') }}" class="small-box-footer">
                        Gerenciar <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6 col-12">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ (int) $overdueInvoices }}</h3>
                        <p>Faturas em Atraso</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <a href="{{ route('admin.invoices.index') }}" class="small-box-footer">
                        Ver faturas <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6 col-12">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ (int) $failedOrders }}</h3>
                        <p>Pedidos com Falha</p>
                    </div>
                    <div class="icon"><i class="fas fa-times-circle"></i></div>
                    <a href="{{ route('admin.orders.index') }}" class="small-box-footer">
                        Ver pedidos <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-sm-6 col-12">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>R$ {{ number_format((float) $todayRevenue, 2, ',', '.') }}</h3>
                        <p>Receita (Hoje)</p>
                    </div>
                    <div class="icon"><i class="fas fa-calendar-day"></i></div>
                    <a href="{{ route('admin.orders.index') }}" class="small-box-footer">
                        Ver pedidos <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <section class="col-lg-7">
            <div class="card">
                <div class="card-header border-0">
                    <div class="d-flex justify-content-between">
                        <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Histórico de Vendas</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 260px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <section class="col-lg-5">
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

    @if($isSuper)
        <div class="row">
            <section class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title"><i class="fas fa-store mr-2"></i>Top vendedores</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Vendedor</th>
                                        <th class="text-right">Pedidos</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topSellers as $row)
                                        <tr>
                                            <td>{{ $row['seller_name'] ?? '-' }}</td>
                                            <td class="text-right">{{ (int) ($row['orders_count'] ?? 0) }}</td>
                                            <td class="text-right">
                                                R$ {{ number_format((float) ($row['total_amount'] ?? 0), 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted p-4">Nenhum dado ainda.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <section class="col-lg-6">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title"><i class="fas fa-shopping-cart mr-2"></i>Pedidos recentes</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Comprador</th>
                                        <th>Status</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                        <tr>
                                            <td>#{{ $order->id }}</td>
                                            <td class="text-truncate" style="max-width: 180px;">
                                                {{ $order->user?->name ?? '—' }}
                                            </td>
                                            <td>
                                                @php
                                                    $st = (string) ($order->status ?? '');
                                                    $badge = 'secondary';
                                                    if ($st === 'paid') $badge = 'success';
                                                    elseif ($st === 'pending') $badge = 'warning';
                                                    elseif ($st === 'failed' || $st === 'cancelled') $badge = 'danger';
                                                    elseif ($st === 'refunded') $badge = 'info';
                                                @endphp
                                                <span class="badge badge-{{ $badge }}">{{ strtoupper($st ?: 'N/A') }}</span>
                                            </td>
                                            <td class="text-right">
                                                R$ {{ number_format((float) $order->total_amount, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted p-4">Nenhum pedido ainda.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">
                            Ver todos os pedidos <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </section>
        </div>
    @endif
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
                height: 350,
                events: {!! json_encode($calendarEvents ?? []) !!},
                editable: false
            });
            calendar.render();
        });
    </script>
@endpush

