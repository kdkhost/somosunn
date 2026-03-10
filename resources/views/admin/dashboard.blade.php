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
                        <div class="small-box bg-success shadow-sm">
                            <div class="inner">
                                <h3>R$ {{ number_format($totalRevenue ?? 0, 2, ',', '.') }}</h3>
                                <p>Receita Total</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <a href="{{ route('admin.orders.index') }}" class="small-box-footer">Ver Vendas <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-maroon shadow-sm">
                            <div class="inner">
                                <h3>R$ {{ number_format($revenueToday ?? 0, 2, ',', '.') }}</h3>
                                <p>Receita de Hoje</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <a href="{{ route('admin.orders.index', ['date' => now()->format('Y-m-d')]) }}" class="small-box-footer">Ver Hoje <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning shadow-sm">
                            <div class="inner">
                                <h3>{{ $totalUsers ?? 0 }}</h3>
                                <p>Usuários Totais</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <a href="{{ route('admin.users.index') }}" class="small-box-footer">Gerenciar Usuários <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info shadow-sm">
                            <div class="inner">
                                <h3>{{ $usersToday ?? 0 }}</h3>
                                <p>Novos Hoje</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <a href="{{ route('admin.users.index', ['created_at' => now()->format('Y-m-d')]) }}" class="small-box-footer">Ver Novos <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

                {{-- QUICK ACTIONS --}}
                <div class="row mb-3 mx-0">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-2 d-flex flex-wrap justify-content-center">
                                <button type="button" onclick="window.openQuickUploadModal()" class="btn btn-primary btn-sm rounded-pill px-3 m-1 shadow-sm">
                                    <i class="fas fa-camera-retro mr-1"></i> Registrar Fotos
                                </button>
                                <a href="{{ route('admin.courses.create') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 m-1">
                                    <i class="fas fa-plus mr-1"></i> Novo Curso
                                </a>
                                <a href="{{ route('admin.events.create') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 m-1">
                                    <i class="fas fa-calendar-plus mr-1"></i> Novo Evento
                                </a>
                                <a href="{{ route('admin.users.create') }}" class="btn btn-outline-warning btn-sm rounded-pill px-3 m-1">
                                    <i class="fas fa-user-plus mr-1"></i> Novo Usuário
                                </a>
                                <a href="{{ route('admin.settings') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 m-1">
                                    <i class="fas fa-cog mr-1"></i> Configurações
                                </a>
                                <a href="{{ route('admin.activity_logs.index') }}" class="btn btn-outline-info btn-sm rounded-pill px-3 m-1">
                                    <i class="fas fa-history mr-1"></i> Logs do Sistema
                                </a>
                            </div>
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

                @php
                    $serviceVisitSummary = $serviceVisitSummary ?? [
                        'total' => 0,
                        'last_24h' => 0,
                        'site' => 0,
                        'curso' => 0,
                        'evento' => 0,
                        'mentoria' => 0,
                        'palestra' => 0,
                        'monitored_products' => 0,
                    ];
                @endphp

                <div class="row" id="legacy-admin-service-visits">
                    <div class="col-12">
                        <div class="card card-outline card-primary">
                            <div class="card-header border-0">
                                <h3 class="card-title"><i class="fas fa-satellite-dish mr-2"></i>Rastreio Global de Visitas</h3>
                                <div class="card-tools">
                                    <span class="badge badge-success"><i class="fas fa-circle mr-1"></i>Ao vivo</span>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($serviceVisitsEnabled ?? false)
                                    <div class="row mb-4">
                                        <div class="col-md-3 col-sm-6 col-12">
                                            <div class="info-box bg-light shadow-sm">
                                                <span class="info-box-icon bg-primary"><i class="fas fa-eye"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total de visitas</span>
                                                    <span class="info-box-number" id="legacy-visits-total">{{ $serviceVisitSummary['total'] ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-12">
                                            <div class="info-box bg-light shadow-sm">
                                                <span class="info-box-icon bg-success"><i class="fas fa-clock"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Últimas 24h</span>
                                                    <span class="info-box-number" id="legacy-visits-day">{{ $serviceVisitSummary['last_24h'] ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-12">
                                            <div class="info-box bg-light shadow-sm">
                                                <span class="info-box-icon bg-info"><i class="fas fa-globe"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Site institucional</span>
                                                    <span class="info-box-number" id="legacy-visits-site">{{ $serviceVisitSummary['site'] ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 col-12">
                                            <div class="info-box bg-light shadow-sm">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-layer-group"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Produtos monitorados</span>
                                                    <span class="info-box-number" id="legacy-visits-products">{{ $serviceVisitSummary['monitored_products'] ?? 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mb-4" id="legacy-visit-chips">
                                        @foreach([
                                            'Cursos' => $serviceVisitSummary['curso'] ?? 0,
                                            'Eventos' => $serviceVisitSummary['evento'] ?? 0,
                                            'Mentorias' => $serviceVisitSummary['mentoria'] ?? 0,
                                            'Palestras' => $serviceVisitSummary['palestra'] ?? 0,
                                        ] as $label => $value)
                                            <span class="badge badge-light border px-3 py-2">{{ $label }}: {{ $value }}</span>
                                        @endforeach
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Produto</th>
                                                            <th>Tipo</th>
                                                            <th class="text-right">Visitas</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="legacy-top-items">
                                                        @forelse(($serviceVisitTopItems ?? []) as $item)
                                                            <tr>
                                                                <td>{{ $item['label'] }}</td>
                                                                <td>{{ $item['type'] }}</td>
                                                                <td class="text-right font-weight-bold">{{ $item['total'] }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3" class="text-center text-muted">Ainda não há visitas registradas.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Responsável</th>
                                                            <th>Segmentação</th>
                                                            <th class="text-right">Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="legacy-owner-leaders">
                                                        @forelse(($serviceVisitOwnerLeaders ?? []) as $leader)
                                                            <tr>
                                                                <td>{{ $leader['name'] }}</td>
                                                                <td class="text-muted">C {{ $leader['curso'] }} • E {{ $leader['evento'] }} • M {{ $leader['mentoria'] }}</td>
                                                                <td class="text-right font-weight-bold">{{ $leader['total'] }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3" class="text-center text-muted">Ainda não há responsáveis ranqueados.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-secondary mb-0">O rastreio de visitas ainda não está disponível neste ambiente.</div>
                                @endif
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


                        {{-- Business Vitality Index (Customer Health Gadget) --}}
                        @php
                            $totalHealth = ($customerHealth['Alta'] + $customerHealth['Média'] + $customerHealth['Baixa']);
                            $altaPerc = $totalHealth > 0 ? round($customerHealth['Alta'] / $totalHealth * 100) : 0;
                        @endphp
                        <div class="mt-3 rounded-2xl overflow-hidden p-4 text-white position-relative"
                            style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f2d4c 100%); border: 1px solid rgba(255,255,255,0.07);">
                            {{-- Background orb --}}
                            <div class="position-absolute"
                                style="top:-20px; right:-20px; width:120px; height:120px; background: radial-gradient(circle, rgba(16,185,129,0.25), transparent 70%); pointer-events:none;">
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="text-xs font-weight-bold"
                                        style="color: #94a3b8; letter-spacing: 0.08em; text-transform: uppercase;">Business
                                        Vitality</div>
                                    <div class="font-weight-black" style="font-size: 1.2rem; color: #fff;">Saúde do Cliente
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-center rounded-circle"
                                    style="width:42px; height:42px; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3);">
                                    <i class="fas fa-heartbeat"
                                        style="color: #10b981; {{ $altaPerc >= 60 ? 'animation: pulse-anim 1.5s ease-in-out infinite;' : '' }}"></i>
                                </div>
                            </div>

                            {{-- Score Ring + Bars --}}
                            <div class="d-flex gap-3 align-items-center">
                                {{-- Canvas Donut --}}
                                <div style="flex-shrink:0; width:90px; height:90px; position:relative;">
                                    <canvas id="customerHealthChart" width="90" height="90"></canvas>
                                    <div class="position-absolute d-flex flex-column align-items-center justify-content-center text-center"
                                        style="inset:0;">
                                        <div class="font-weight-black"
                                            style="font-size:1.4rem; color:{{ $altaPerc >= 60 ? '#10b981' : ($altaPerc >= 30 ? '#f59e0b' : '#ef4444') }};">
                                            {{ $altaPerc }}%
                                        </div>
                                        <div
                                            style="font-size:0.55rem; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em;">
                                            Engajados</div>
                                    </div>
                                </div>

                                {{-- Stats --}}
                                <div class="flex-fill">
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span style="font-size:0.7rem; color:#94a3b8;"><i class="fas fa-circle mr-1"
                                                    style="color:#10b981;"></i>Alta</span>
                                            <span class="font-weight-bold"
                                                style="font-size:0.7rem; color:#10b981;">{{ $customerHealth['Alta'] }}</span>
                                        </div>
                                        <div class="rounded-pill" style="height:4px; background:rgba(255,255,255,0.08);">
                                            <div class="rounded-pill"
                                                style="height:4px; width:{{ $altaPerc }}%; background: linear-gradient(90deg, #10b981, #34d399);">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        @php $medPerc = $totalHealth > 0 ? round($customerHealth['Média'] / $totalHealth * 100) : 0; @endphp
                                        <div class="d-flex justify-content-between mb-1">
                                            <span style="font-size:0.7rem; color:#94a3b8;"><i class="fas fa-circle mr-1"
                                                    style="color:#f59e0b;"></i>Média</span>
                                            <span class="font-weight-bold"
                                                style="font-size:0.7rem; color:#f59e0b;">{{ $customerHealth['Média'] }}</span>
                                        </div>
                                        <div class="rounded-pill" style="height:4px; background:rgba(255,255,255,0.08);">
                                            <div class="rounded-pill"
                                                style="height:4px; width:{{ $medPerc }}%; background: linear-gradient(90deg, #f59e0b, #fbbf24);">
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        @php $lowPerc = $totalHealth > 0 ? round($customerHealth['Baixa'] / $totalHealth * 100) : 0; @endphp
                                        <div class="d-flex justify-content-between mb-1">
                                            <span style="font-size:0.7rem; color:#94a3b8;"><i class="fas fa-circle mr-1"
                                                    style="color:#ef4444;"></i>Baixa</span>
                                            <span class="font-weight-bold"
                                                style="font-size:0.7rem; color:#ef4444;">{{ $customerHealth['Baixa'] }}</span>
                                        </div>
                                        <div class="rounded-pill" style="height:4px; background:rgba(255,255,255,0.08);">
                                            <div class="rounded-pill"
                                                style="height:4px; width:{{ $lowPerc }}%; background: linear-gradient(90deg, #ef4444, #f87171);">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-3 pt-2" style="border-top: 1px solid rgba(255,255,255,0.06);">
                                <small style="color:#475569; font-size:0.65rem;">Atualizado em tempo real baseado em planos e
                                    perfis</small>
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
                        {{-- My Vitality Score (Saúde Individual do Cliente) --}}
                        <div class="rounded-2xl overflow-hidden p-4 text-white position-relative mb-3"
                             style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f2d4c 100%); border: 1px solid rgba(255,255,255,0.07);">
                            {{-- BG Orb --}}
                            <div class="position-absolute"
                                 style="top:-15px; right:-15px; width:110px; height:110px;
                                        background: radial-gradient(circle, {{ $myHealth['color'] }}40, transparent 70%);
                                        pointer-events:none;"></div>

                            {{-- Header --}}
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div style="font-size:0.65rem; color:#94a3b8; text-transform:uppercase; letter-spacing:0.08em; font-weight:700;">
                                        Minha Reputação
                                    </div>
                                    <div style="font-size:1.1rem; color:#fff; font-weight:900;">Vitality Score</div>
                                </div>
                                <div class="d-flex align-items-center justify-content-center rounded-circle"
                                     style="width:42px; height:42px;
                                            background: {{ $myHealth['color'] }}25;
                                            border: 1px solid {{ $myHealth['color'] }}50;">
                                    <i class="fas fa-heartbeat"
                                       style="color: {{ $myHealth['color'] }};
                                              {{ $myHealth['level'] === 'Alta' ? 'animation: pulse-anim 1.5s ease-in-out infinite;' : '' }}"></i>
                                </div>
                            </div>

                            {{-- Score Ring --}}
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="flex-shrink:0; position:relative; width:80px; height:80px;">
                                    <svg viewBox="0 0 36 36" style="width:80px; height:80px; transform:rotate(-90deg);">
                                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="3"/>
                                        <circle cx="18" cy="18" r="15.9" fill="none"
                                            stroke="{{ $myHealth['color'] }}" stroke-width="3"
                                            stroke-dasharray="{{ $myHealth['score'] }}, 100"
                                            stroke-linecap="round"
                                            style="transition: stroke-dasharray 1s ease;"/>
                                    </svg>
                                    <div class="position-absolute d-flex flex-column align-items-center justify-content-center"
                                         style="inset:0; line-height:1;">
                                        <span style="font-size:1.2rem; font-weight:900; color:{{ $myHealth['color'] }};">
                                            {{ $myHealth['score'] }}
                                        </span>
                                        <span style="font-size:0.5rem; color:#94a3b8; text-transform:uppercase;">pts</span>
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size:1rem; font-weight:900; color:{{ $myHealth['color'] }};">
                                        {{ $myHealth['emoji'] }} {{ $myHealth['level'] }}
                                    </div>
                                    <div style="font-size:0.7rem; color:#94a3b8; margin-top:2px;">
                                        @if($myHealth['level'] === 'Alta')
                                            Perfil completo e plano ativo! 🎉
                                        @elseif($myHealth['level'] === 'Média')
                                            Complete seu perfil para subir!
                                        @else
                                            Ative um plano para começar.
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Checklist de critérios --}}
                            @if(!empty($myHealthDetails))
                            <div style="border-top: 1px solid rgba(255,255,255,0.06); padding-top:0.75rem;">
                                @foreach([
                                    'plano_ativo'     => ['label' => 'Plano ativo',       'icon' => 'fa-crown'],
                                    'foto'            => ['label' => 'Foto de perfil',     'icon' => 'fa-user-circle'],
                                    'bio'             => ['label' => 'Bio preenchida',     'icon' => 'fa-align-left'],
                                    'telefone'        => ['label' => 'Telefone',           'icon' => 'fa-phone'],
                                    'ocupacao'        => ['label' => 'Ocupação',           'icon' => 'fa-briefcase'],
                                    'cidade_estado'   => ['label' => 'Cidade/Estado',      'icon' => 'fa-map-marker-alt'],
                                    'empresa'         => ['label' => 'Empresa',            'icon' => 'fa-building'],
                                ] as $key => $item)
                                    @if(array_key_exists($key, $myHealthDetails))
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span style="font-size:0.68rem; color:#94a3b8;">
                                            <i class="fas {{ $item['icon'] }} mr-1" style="width:12px;"></i>
                                            {{ $item['label'] }}
                                        </span>
                                        @if($myHealthDetails[$key])
                                            <i class="fas fa-check-circle" style="color:#10b981; font-size:0.75rem;"></i>
                                        @else
                                            <i class="fas fa-times-circle" style="color:#ef4444; font-size:0.75rem;"></i>
                                        @endif
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                            @endif

                            <a href="{{ route('panel.settings.index') }}"
                               class="d-block text-center mt-3 py-2 rounded-xl"
                               style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
                                      color: #94a3b8; font-size:0.7rem; text-decoration:none;
                                      transition: background 0.2s;"
                               onmouseover="this.style.background='rgba(255,255,255,0.12)'"
                               onmouseout="this.style.background='rgba(255,255,255,0.06)'">
                                <i class="fas fa-edit mr-1"></i> Completar meu perfil
                            </a>
                        </div>

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
    @include('partials.service-visits-realtime')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/pt-br.js"></script>

    <script>
        function renderLegacyAdminServiceVisits(payload) {
            const summary = payload.serviceVisitSummary || {};
            const topItems = payload.serviceVisitTopItems || [];
            const leaders = payload.serviceVisitOwnerLeaders || [];

            [
                ['legacy-visits-total', summary.total || 0],
                ['legacy-visits-day', summary.last_24h || 0],
                ['legacy-visits-site', summary.site || 0],
                ['legacy-visits-products', summary.monitored_products || 0],
            ].forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = value;
                }
            });

            const chips = document.getElementById('legacy-visit-chips');
            if (chips) {
                chips.innerHTML = [
                    ['Cursos', summary.curso || 0],
                    ['Eventos', summary.evento || 0],
                    ['Mentorias', summary.mentoria || 0],
                    ['Palestras', summary.palestra || 0],
                ].map(([label, value]) => `<span class="badge badge-light border px-3 py-2">${label}: ${value}</span>`).join('');
            }

            const topItemsBody = document.getElementById('legacy-top-items');
            if (topItemsBody) {
                topItemsBody.innerHTML = topItems.length
                    ? topItems.map((item) => `
                        <tr>
                            <td>${item.label}</td>
                            <td>${item.type}</td>
                            <td class="text-right font-weight-bold">${item.total}</td>
                        </tr>
                    `).join('')
                    : '<tr><td colspan="3" class="text-center text-muted">Ainda não há visitas registradas.</td></tr>';
            }

            const leadersBody = document.getElementById('legacy-owner-leaders');
            if (leadersBody) {
                leadersBody.innerHTML = leaders.length
                    ? leaders.map((leader) => `
                        <tr>
                            <td>${leader.name}</td>
                            <td class="text-muted">Cursos: ${leader.curso} • Eventos: ${leader.evento} • Mentorias: ${leader.mentoria}</td>
                            <td class="text-right font-weight-bold">${leader.total}</td>
                        </tr>
                    `).join('')
                    : '<tr><td colspan="3" class="text-center text-muted">Ainda não há responsáveis ranqueados.</td></tr>';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            renderLegacyAdminServiceVisits({!! json_encode([
                'serviceVisitSummary' => $serviceVisitSummary,
                'serviceVisitTopItems' => $serviceVisitTopItems ?? [],
                'serviceVisitOwnerLeaders' => $serviceVisitOwnerLeaders ?? [],
            ]) !!});

            window.UNNServiceVisitsRealtime.start({
                statsUrl: @json(route('admin.dashboard.stats')),
                refreshMs: @json(max(3000, (int) config('dashboard.refresh_interval_ms', 10000))),
                onPayload: renderLegacyAdminServiceVisits,
            });

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

                // Customer Health Chart (Business Vitality Index)
                const healthCtx = document.getElementById('customerHealthChart');
                if (healthCtx) {
                    new Chart(healthCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Alta', 'Média', 'Baixa'],
                            datasets: [{
                                data: [{{ $customerHealth['Alta'] }}, {{ $customerHealth['Média'] }}, {{ $customerHealth['Baixa'] }}],
                                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                                borderWidth: 2,
                                borderColor: ['rgba(16,185,129,0.2)', 'rgba(245,158,11,0.2)', 'rgba(239,68,68,0.2)']
                            }]
                        },
                        options: {
                            responsive: false,
                            maintainAspectRatio: false,
                            cutout: '72%',
                            plugins: { legend: { display: false }, tooltip: { enabled: true } }
                        }
                    });
                }
            @endif
                                });
    </script>

    <style>
        @keyframes pulse-anim {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }

        .gap-3 {
            gap: 0.75rem;
        }

        .flex-fill {
            flex: 1 1 auto;
        }

        .rounded-2xl {
            border-radius: 1rem !important;
        }

        .font-weight-black {
            font-weight: 900 !important;
        }
    </style>
@endpush
