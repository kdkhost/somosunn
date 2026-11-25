@extends('admin.layouts.app')

@section('title', 'Dashboard - Admin')
@section('page_title', 'Dashboard')

@section('content')
    @php
        $user = auth()->user();
        $isAdminUser = isset($isAdmin) && $isAdmin;
        $newUsersTodayUrl = route('admin.users.index', ['registered' => 'today']);

        $hour = (int) now()->format('H');
        $greeting = $hour < 12 ? 'Bom dia' : ($hour < 18 ? 'Boa tarde' : 'Boa noite');

        $nameParts = preg_split('/\s+/', trim((string) ($user->name ?? 'Usuário')));
        $initials = strtoupper(
            mb_substr($nameParts[0] ?? 'U', 0, 1) .
            (count($nameParts) > 1 ? mb_substr(end($nameParts), 0, 1) : '')
        );

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

    <div class="dash-page">
        {{-- HERO EXECUTIVO --}}
        <section class="dash-hero mb-4">
            <div class="row align-items-center g-3 position-relative" style="z-index: 2;">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-3">
                        <div class="dash-avatar-wrap flex-shrink-0">
                            @if($user && $user->photo)
                                <img src="{{ asset($user->photo) }}" alt="{{ $user->name }}"
                                    class="dash-avatar-img" loading="lazy" decoding="async">
                            @else
                                <div class="dash-avatar-fallback">{{ $initials }}</div>
                            @endif
                            <span class="dash-avatar-status" title="Online"></span>
                        </div>
                        <div class="min-w-0">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <span class="dash-hero-pill">
                                    <span class="dash-live-dot"></span>
                                    Sistema Online
                                </span>
                                <span class="dash-hero-pill d-none d-sm-inline-flex">
                                    <i class="far fa-calendar-alt"></i>
                                    {{ now()->locale('pt_BR')->translatedFormat('d \d\e F, Y') }}
                                </span>
                            </div>
                            <h2 class="h4 fw-bold text-white mb-1 text-truncate">{{ $greeting }}, {{ $user->name }}!</h2>
                            <p class="mb-0 text-white-50 small">
                                @if($isAdminUser)
                                    Painel Administrativo <strong>SOMOS UNN</strong> — acompanhe faturamento, crescimento, tráfego e saúde da plataforma em tempo real.
                                @else
                                    Bem-vindo ao <strong>SOMOS UNN</strong> — acompanhe seu progresso, próximos eventos e novidades.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                        @if($isAdminUser)
                            <button type="button" onclick="window.openQuickUploadModal()" class="btn dash-hero-btn-light btn-sm">
                                <i class="fas fa-camera-retro me-1"></i> Registrar Fotos
                            </button>
                            <a href="{{ route('admin.settings') }}" class="btn dash-hero-btn-ghost btn-sm">
                                <i class="fas fa-sliders-h me-1"></i> Configurações
                            </a>
                        @endif
                        <div class="dash-env-badge ms-lg-1">
                            <i class="fas fa-code-branch me-1"></i>
                            PHP {{ phpversion() }} <span class="mx-1 opacity-50">•</span> Laravel {{ app()->version() }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- PAINEL ADMINISTRATIVO --}}
        @if($isAdminUser)
            {{-- BLOCO 1: KPIS PRINCIPAIS --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-sm-6">
                    <div class="small-box bg-success mb-0 h-100">
                        <div class="inner">
                            <h3>R$ {{ number_format($totalRevenue ?? 0, 2, ',', '.') }}</h3>
                            <p>Receita Total</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <a href="{{ route('admin.orders.index') }}" class="small-box-footer">
                            Abrir Pedidos e Vendas <i class="fas fa-arrow-circle-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="small-box bg-maroon mb-0 h-100">
                        <div class="inner">
                            <h3>R$ {{ number_format($revenueToday ?? 0, 2, ',', '.') }}</h3>
                            <p>Receita de Hoje</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <a href="{{ route('admin.orders.index', ['date' => now()->format('Y-m-d')]) }}" class="small-box-footer">
                            Ver Faturamento de Hoje <i class="fas fa-arrow-circle-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="small-box bg-warning mb-0 h-100">
                        <div class="inner">
                            <h3>{{ $totalUsers ?? 0 }}</h3>
                            <p>Usuários Totais</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                            Abrir Lista de Usuários <i class="fas fa-arrow-circle-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="small-box bg-info mb-0 h-100">
                        <div class="inner">
                            <h3>{{ $usersToday ?? 0 }}</h3>
                            <p>Novos Hoje</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <a href="{{ $newUsersTodayUrl }}" class="small-box-footer">
                            Listar Novos Cadastros <i class="fas fa-arrow-circle-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- BLOCO 2: BARRA DE ACESSO RÁPIDO --}}
            <div class="card dash-quickbar mb-4">
                <div class="card-body py-3 px-3 px-md-4">
                    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <span class="dash-quickbar-icon">
                                <i class="fas fa-bolt"></i>
                            </span>
                            <div>
                                <div class="fw-bold small mb-0">Ações Rápidas</div>
                                <div class="text-body-secondary" style="font-size: 0.72rem;">Atalhos operacionais frequentes</div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button type="button" onclick="window.openQuickUploadModal()" class="btn btn-primary btn-sm rounded-pill px-3">
                                <i class="fas fa-camera-retro me-1"></i> Registrar Fotos
                            </button>
                            <a href="{{ route('admin.courses.create') }}" class="btn dash-action-pill btn-sm">
                                <i class="fas fa-graduation-cap text-primary me-1"></i> Novo Curso
                            </a>
                            <a href="{{ route('admin.events.create') }}" class="btn dash-action-pill btn-sm">
                                <i class="fas fa-calendar-plus text-success me-1"></i> Novo Evento
                            </a>
                            <a href="{{ route('admin.users.create') }}" class="btn dash-action-pill btn-sm">
                                <i class="fas fa-user-plus text-warning me-1"></i> Novo Usuário
                            </a>
                            <a href="{{ $newUsersTodayUrl }}" class="btn dash-action-pill btn-sm">
                                <i class="fas fa-user-clock text-info me-1"></i> Novos Cadastros
                            </a>
                            <a href="{{ route('admin.activity_logs.index') }}" class="btn dash-action-pill btn-sm">
                                <i class="fas fa-history text-secondary me-1"></i> Logs do Sistema
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOCO 3: INVENTÁRIO DE CONTEÚDO --}}
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-sm-6">
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.courses.index') ? route('admin.courses.index') : '#' }}" class="text-decoration-none text-reset d-block h-100">
                        <div class="info-box dash-content-box mb-0 h-100">
                            <span class="info-box-icon bg-primary"><i class="fas fa-graduation-cap"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Cursos</span>
                                <span class="info-box-number">{{ $coursesCount ?? 0 }}</span>
                            </div>
                            <span class="dash-box-arrow"><i class="fas fa-chevron-right"></i></span>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.mentorships.index') ? route('admin.mentorships.index') : '#' }}" class="text-decoration-none text-reset d-block h-100">
                        <div class="info-box dash-content-box mb-0 h-100">
                            <span class="info-box-icon bg-success"><i class="fas fa-chalkboard-teacher"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Mentorias</span>
                                <span class="info-box-number">{{ $mentorshipsCount ?? 0 }}</span>
                            </div>
                            <span class="dash-box-arrow"><i class="fas fa-chevron-right"></i></span>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.events.index') ? route('admin.events.index') : '#' }}" class="text-decoration-none text-reset d-block h-100">
                        <div class="info-box dash-content-box mb-0 h-100">
                            <span class="info-box-icon bg-warning"><i class="fas fa-calendar-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Eventos</span>
                                <span class="info-box-number">{{ $eventsCount ?? 0 }}</span>
                            </div>
                            <span class="dash-box-arrow"><i class="fas fa-chevron-right"></i></span>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <a href="{{ \Illuminate\Support\Facades\Route::has('admin.certificates.index') ? route('admin.certificates.index') : '#' }}" class="text-decoration-none text-reset d-block h-100">
                        <div class="info-box dash-content-box mb-0 h-100">
                            <span class="info-box-icon bg-secondary"><i class="fas fa-certificate"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Certificados</span>
                                <span class="info-box-number">{{ $certificatesCount ?? 0 }}</span>
                            </div>
                            <span class="dash-box-arrow"><i class="fas fa-chevron-right"></i></span>
                        </div>
                    </a>
                </div>
            </div>

            {{-- BLOCO 4: GRÁFICOS FINANCEIROS E PEDIDOS --}}
            <div class="row g-3 mb-4">
                <div class="col-lg-8">
                    <div class="card card-outline card-primary h-100 mb-0">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-chart-area text-primary me-2"></i>Histórico de Vendas
                                </h3>
                                <div class="text-body-secondary small mt-1">Evolução do faturamento nos últimos 6 meses</div>
                            </div>
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                Ver Relatório
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="dash-chart-wrap" style="height: 270px;">
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card card-outline card-info h-100 mb-0">
                        <div class="card-header">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-chart-pie text-info me-2"></i>Pedidos por Status
                            </h3>
                            <div class="text-body-secondary small mt-1">Distribuição atual dos pedidos</div>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <div class="dash-chart-wrap w-100" style="height: 270px;">
                                <canvas id="ordersStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOCO 5: RASTREIO GLOBAL DE VISITAS --}}
            <div class="row g-3 mb-4" id="legacy-admin-service-visits">
                <div class="col-12">
                    <div class="card card-outline card-primary mb-0">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-satellite-dish text-primary me-2"></i>Rastreio Global de Visitas
                                </h3>
                                <div class="text-body-secondary small mt-1">Monitoramento em tempo real de acessos no site e produtos</div>
                            </div>
                            <span class="dash-live-badge">
                                <span class="dash-live-dot"></span> Ao vivo
                            </span>
                        </div>

                        <div class="card-body">
                            @if($serviceVisitsEnabled ?? false)
                                <div class="row g-3 mb-4">
                                    <div class="col-xl-3 col-sm-6">
                                        <div class="dash-metric-tile">
                                            <span class="dash-tile-icon bg-primary-subtle text-primary">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                            <div>
                                                <div class="dash-tile-label">Total de Visitas</div>
                                                <div class="dash-tile-value" id="legacy-visits-total">{{ $serviceVisitSummary['total'] ?? 0 }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-sm-6">
                                        <div class="dash-metric-tile">
                                            <span class="dash-tile-icon bg-success-subtle text-success">
                                                <i class="fas fa-clock"></i>
                                            </span>
                                            <div>
                                                <div class="dash-tile-label">Últimas 24h</div>
                                                <div class="dash-tile-value" id="legacy-visits-day">{{ $serviceVisitSummary['last_24h'] ?? 0 }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-sm-6">
                                        <div class="dash-metric-tile">
                                            <span class="dash-tile-icon bg-info-subtle text-info">
                                                <i class="fas fa-globe"></i>
                                            </span>
                                            <div>
                                                <div class="dash-tile-label">Site Institucional</div>
                                                <div class="dash-tile-value" id="legacy-visits-site">{{ $serviceVisitSummary['site'] ?? 0 }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-sm-6">
                                        <div class="dash-metric-tile">
                                            <span class="dash-tile-icon bg-warning-subtle text-warning">
                                                <i class="fas fa-layer-group"></i>
                                            </span>
                                            <div>
                                                <div class="dash-tile-label">Produtos Monitorados</div>
                                                <div class="dash-tile-value" id="legacy-visits-products">{{ $serviceVisitSummary['monitored_products'] ?? 0 }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap align-items-center gap-2 mb-4" id="legacy-visit-chips">
                                    @foreach([
                                        'Cursos' => $serviceVisitSummary['curso'] ?? 0,
                                        'Eventos' => $serviceVisitSummary['evento'] ?? 0,
                                        'Mentorias' => $serviceVisitSummary['mentoria'] ?? 0,
                                        'Palestras' => $serviceVisitSummary['palestra'] ?? 0,
                                    ] as $label => $value)
                                        <span class="dash-chip">{{ $label }}: <strong>{{ $value }}</strong></span>
                                    @endforeach
                                </div>

                                <div class="row g-3">
                                    <div class="col-lg-6">
                                        <div class="dash-subpanel h-100">
                                            <div class="dash-subpanel-header">
                                                <i class="fas fa-fire text-warning me-2"></i>Produtos Mais Visitados
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Produto</th>
                                                            <th>Tipo</th>
                                                            <th class="text-end">Visitas</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="legacy-top-items">
                                                        @forelse(($serviceVisitTopItems ?? []) as $item)
                                                            <tr>
                                                                <td class="fw-medium">{{ $item['label'] }}</td>
                                                                <td><span class="badge text-bg-secondary rounded-pill">{{ $item['type'] }}</span></td>
                                                                <td class="text-end fw-bold">{{ $item['total'] }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3" class="text-center text-body-secondary py-4">Ainda não há visitas registradas.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="dash-subpanel h-100">
                                            <div class="dash-subpanel-header">
                                                <i class="fas fa-trophy text-primary me-2"></i>Ranking por Responsável
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Responsável</th>
                                                            <th>Segmentação</th>
                                                            <th class="text-end">Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="legacy-owner-leaders">
                                                        @forelse(($serviceVisitOwnerLeaders ?? []) as $leader)
                                                            <tr>
                                                                <td class="fw-medium">{{ $leader['name'] }}</td>
                                                                <td class="text-body-secondary small">C {{ $leader['curso'] }} • E {{ $leader['evento'] }} • M {{ $leader['mentoria'] }}</td>
                                                                <td class="text-end fw-bold">{{ $leader['total'] }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3" class="text-center text-body-secondary py-4">Ainda não há responsáveis ranqueados.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-secondary mb-0">
                                    <i class="fas fa-info-circle me-2"></i>O rastreio de visitas ainda não está disponível neste ambiente.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOCO 6: CALENDÁRIO + PAINEL OPERACIONAL --}}
            <div class="row g-3 mb-4">
                {{-- Calendário --}}
                <div class="col-lg-7">
                    <div class="card card-outline card-success h-100 mb-0">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-calendar-alt text-success me-2"></i>Calendário de Eventos
                                </h3>
                                <div class="text-body-secondary small mt-1">Agenda integrada de eventos e atividades</div>
                            </div>
                            <a href="{{ route('admin.events.create') }}" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                <i class="fas fa-plus me-1"></i> Evento
                            </a>
                        </div>
                        <div class="card-body p-3">
                            <div id="calendar" class="dash-calendar"></div>
                        </div>
                    </div>
                </div>

                {{-- Coluna Direita: Atividades + Saúde do Cliente + Jobs --}}
                <div class="col-lg-5 d-flex flex-column gap-3">
                    {{-- Últimas Atividades --}}
                    <div class="card card-outline card-secondary mb-0">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-history text-secondary me-2"></i>Últimas Atividades
                            </h3>
                            <span class="badge text-bg-secondary rounded-pill">{{ $logsCount ?? 0 }} logs</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Hora</th>
                                            <th>Ação</th>
                                            <th class="pe-3">Usuário</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse(\App\Models\ActivityLog::with('user')->latest()->limit(7)->get() as $log)
                                            <tr>
                                                <td class="ps-3 small text-body-secondary text-nowrap">{{ $log->created_at->format('d/m H:i') }}</td>
                                                <td>
                                                    <span class="badge text-bg-primary bg-opacity-75 rounded-pill fw-normal">
                                                        {{ \Illuminate\Support\Str::limit($log->action, 18) }}
                                                    </span>
                                                </td>
                                                <td class="pe-3 small fw-medium text-truncate" style="max-width: 140px;">
                                                    {{ $log->user->name ?? 'Sistema' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-body-secondary py-4">Nenhuma atividade recente.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-center py-2">
                            <a href="{{ route('admin.activity_logs.index') }}" class="text-decoration-none fw-semibold small">
                                Ver Todos os Logs <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Business Vitality Index (Saúde do Cliente) --}}
                    @php
                        $totalHealth = ($customerHealth['Alta'] ?? 0) + ($customerHealth['Média'] ?? 0) + ($customerHealth['Baixa'] ?? 0);
                        $altaPerc = $totalHealth > 0 ? round(($customerHealth['Alta'] ?? 0) / $totalHealth * 100) : 0;
                        $medPerc = $totalHealth > 0 ? round(($customerHealth['Média'] ?? 0) / $totalHealth * 100) : 0;
                        $lowPerc = $totalHealth > 0 ? round(($customerHealth['Baixa'] ?? 0) / $totalHealth * 100) : 0;
                    @endphp
                    <div class="dash-vitality-card p-4 text-white position-relative">
                        <div class="dash-vitality-orb"></div>

                        <div class="d-flex justify-content-between align-items-center mb-3 position-relative" style="z-index: 2;">
                            <div>
                                <div class="dash-vitality-eyebrow">Business Vitality</div>
                                <div class="fs-5 fw-bold text-white">Saúde do Cliente</div>
                            </div>
                            <div class="dash-vitality-icon">
                                <i class="fas fa-heartbeat {{ $altaPerc >= 60 ? 'dash-pulse-icon' : '' }}" style="color: #10b981;"></i>
                            </div>
                        </div>

                        <div class="d-flex gap-3 align-items-center position-relative" style="z-index: 2;">
                            <div class="flex-shrink-0 position-relative" style="width: 92px; height: 92px;">
                                <canvas id="customerHealthChart" width="92" height="92"></canvas>
                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center pointer-events-none">
                                    <div class="fw-bolder lh-1" style="font-size: 1.35rem; color: {{ $altaPerc >= 60 ? '#10b981' : ($altaPerc >= 30 ? '#f59e0b' : '#ef4444') }};">
                                        {{ $altaPerc }}%
                                    </div>
                                    <div style="font-size: 0.58rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">
                                        Engajados
                                    </div>
                                </div>
                            </div>

                            <div class="flex-fill">
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1 small">
                                        <span style="color: #cbd5e1; font-size: 0.74rem;"><i class="fas fa-circle me-1" style="color: #10b981; font-size: 0.55rem;"></i>Alta</span>
                                        <span class="fw-bold" style="color: #10b981; font-size: 0.74rem;">{{ $customerHealth['Alta'] ?? 0 }}</span>
                                    </div>
                                    <div class="dash-vitality-bar">
                                        <div style="width: {{ $altaPerc }}%; background: linear-gradient(90deg, #10b981, #34d399);"></div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1 small">
                                        <span style="color: #cbd5e1; font-size: 0.74rem;"><i class="fas fa-circle me-1" style="color: #f59e0b; font-size: 0.55rem;"></i>Média</span>
                                        <span class="fw-bold" style="color: #f59e0b; font-size: 0.74rem;">{{ $customerHealth['Média'] ?? 0 }}</span>
                                    </div>
                                    <div class="dash-vitality-bar">
                                        <div style="width: {{ $medPerc }}%; background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between mb-1 small">
                                        <span style="color: #cbd5e1; font-size: 0.74rem;"><i class="fas fa-circle me-1" style="color: #ef4444; font-size: 0.55rem;"></i>Baixa</span>
                                        <span class="fw-bold" style="color: #ef4444; font-size: 0.74rem;">{{ $customerHealth['Baixa'] ?? 0 }}</span>
                                    </div>
                                    <div class="dash-vitality-bar">
                                        <div style="width: {{ $lowPerc }}%; background: linear-gradient(90deg, #ef4444, #f87171);"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-3 pt-2 position-relative" style="z-index: 2; border-top: 1px solid rgba(255,255,255,0.08);">
                            <small style="color: #94a3b8; font-size: 0.68rem;">Atualizado em tempo real com base em planos e perfis</small>
                        </div>
                    </div>

                    {{-- Jobs do Sistema --}}
                    <div class="card mb-0">
                        <div class="card-body py-3 px-3 d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <div class="fw-bold small mb-1">
                                    <i class="fas fa-tasks text-primary me-1"></i> Fila de Jobs do Sistema
                                </div>
                                <div class="d-flex flex-wrap gap-3 small text-body-secondary">
                                    <span>Pendentes: <strong class="text-danger">{{ $pendingJobsCount ?? 0 }}</strong></span>
                                    <span>Concluídos: <strong class="text-success">{{ $jobsStatus['Concluídos'] ?? 0 }}</strong></span>
                                </div>
                            </div>
                            <div style="width: 68px; height: 52px;" class="flex-shrink-0">
                                <canvas id="jobsStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BLOCO 7: SAÚDE DO SISTEMA & INFRAESTRUTURA --}}
            <div class="row g-3">
                <div class="col-12">
                    <div class="card card-outline card-dark mb-0">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-server text-primary me-2"></i>Saúde do Sistema &amp; Infraestrutura
                                </h3>
                                <div class="text-body-secondary small mt-1">Disco, banco de dados, capacidade e rotina interna</div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btn-refresh-health" title="Atualizar métricas">
                                <i class="fas fa-sync-alt me-1"></i> Atualizar
                            </button>
                        </div>
                        <div class="card-body" id="system-health-body">
                            <div class="text-center text-body-secondary py-4">
                                <i class="fas fa-spinner fa-spin me-2"></i> Carregando informações do sistema...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- PAINEL DO MEMBRO (VISUAL SIMPLIFICADO) --}}
        @if(!$isAdminUser)
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <x-widgets.metric title="Meus Cursos" icon="fas fa-graduation-cap" :value="$coursesCount ?? 0" color="blue" />
                </div>
                <div class="col-md-4">
                    <x-widgets.metric title="Minhas Mentorias" icon="fas fa-chalkboard-teacher" :value="$mentorshipsCount ?? 0" color="green" />
                </div>
                <div class="col-md-4">
                    <x-widgets.metric title="Meus Eventos" icon="fas fa-calendar-alt" :value="$eventsCount ?? 0" color="yellow" />
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card card-outline card-primary h-100 mb-0">
                        <div class="card-header">
                            <h3 class="card-title mb-0"><i class="fas fa-calendar-day text-primary me-2"></i>Meu Calendário</h3>
                        </div>
                        <div class="card-body p-3">
                            <div id="calendar" class="dash-calendar"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 d-flex flex-column gap-3">
                    {{-- Vitality Score Individual --}}
                    <div class="dash-vitality-card p-4 text-white position-relative">
                        <div class="position-absolute"
                            style="top:-18px; right:-18px; width:120px; height:120px; background: radial-gradient(circle, {{ $myHealth['color'] ?? '#10b981' }}40, transparent 70%); pointer-events:none;">
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3 position-relative" style="z-index: 2;">
                            <div>
                                <div class="dash-vitality-eyebrow">Minha Reputação</div>
                                <div class="fs-5 fw-bold text-white">Vitality Score</div>
                            </div>
                            <div class="d-flex align-items-center justify-content-center rounded-circle"
                                style="width:42px; height:42px; background: {{ $myHealth['color'] ?? '#10b981' }}25; border: 1px solid {{ $myHealth['color'] ?? '#10b981' }}50;">
                                <i class="fas fa-heartbeat {{ ($myHealth['level'] ?? '') === 'Alta' ? 'dash-pulse-icon' : '' }}"
                                    style="color: {{ $myHealth['color'] ?? '#10b981' }};"></i>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 mb-3 position-relative" style="z-index: 2;">
                            <div class="flex-shrink-0 position-relative" style="width: 82px; height: 82px;">
                                <svg viewBox="0 0 36 36" style="width: 82px; height: 82px; transform: rotate(-90deg);">
                                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="3" />
                                    <circle cx="18" cy="18" r="15.9" fill="none"
                                        stroke="{{ $myHealth['color'] ?? '#10b981' }}" stroke-width="3"
                                        stroke-dasharray="{{ $myHealth['score'] ?? 0 }}, 100"
                                        stroke-linecap="round"
                                        style="transition: stroke-dasharray 1s ease;" />
                                </svg>
                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center lh-1">
                                    <span class="fw-bolder" style="font-size: 1.25rem; color: {{ $myHealth['color'] ?? '#10b981' }};">
                                        {{ $myHealth['score'] ?? 0 }}
                                    </span>
                                    <span style="font-size: 0.55rem; color: #94a3b8; text-transform: uppercase;">pts</span>
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold fs-6" style="color: {{ $myHealth['color'] ?? '#10b981' }};">
                                    {{ $myHealth['emoji'] ?? '' }} {{ $myHealth['level'] ?? 'Inicial' }}
                                </div>
                                <div class="small mt-1" style="color: #cbd5e1; font-size: 0.76rem;">
                                    @if(($myHealth['level'] ?? '') === 'Alta')
                                        Perfil completo e plano ativo!
                                    @elseif(($myHealth['level'] ?? '') === 'Média')
                                        Complete seu perfil para subir de nível!
                                    @else
                                        Ative um plano para começar.
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if(!empty($myHealthDetails))
                            <div class="pt-3 position-relative" style="z-index: 2; border-top: 1px solid rgba(255,255,255,0.08);">
                                @foreach([
                                    'plano_ativo' => ['label' => 'Plano ativo', 'icon' => 'fa-crown'],
                                    'foto' => ['label' => 'Foto de perfil', 'icon' => 'fa-user-circle'],
                                    'bio' => ['label' => 'Bio preenchida', 'icon' => 'fa-align-left'],
                                    'telefone' => ['label' => 'Telefone', 'icon' => 'fa-phone'],
                                    'ocupacao' => ['label' => 'Ocupação', 'icon' => 'fa-briefcase'],
                                    'cidade_estado' => ['label' => 'Cidade/Estado', 'icon' => 'fa-map-marker-alt'],
                                    'empresa' => ['label' => 'Empresa', 'icon' => 'fa-building'],
                                ] as $key => $item)
                                    @if(array_key_exists($key, $myHealthDetails))
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span style="font-size: 0.75rem; color: #cbd5e1;">
                                                <i class="fas {{ $item['icon'] }} me-2" style="width: 14px;"></i>{{ $item['label'] }}
                                            </span>
                                            @if($myHealthDetails[$key])
                                                <i class="fas fa-check-circle text-success"></i>
                                            @else
                                                <i class="fas fa-times-circle text-danger"></i>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <a href="{{ route('panel.admin.settings') }}" class="dash-vitality-btn d-block text-center mt-3">
                            <i class="fas fa-edit me-1"></i> Completar meu perfil
                        </a>
                    </div>

                    <div class="card card-outline card-info mb-0">
                        <div class="card-header">
                            <h3 class="card-title mb-0"><i class="fas fa-bullhorn text-info me-2"></i>Novidades</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-body-secondary small mb-0">Nenhuma novidade no momento.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .dash-hero {
            position: relative;
            overflow: hidden;
            border-radius: 1.15rem;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #1F5EDB 100%);
            color: #ffffff;
            padding: 1.5rem 1.75rem;
            box-shadow: 0 18px 40px -14px rgba(15, 23, 42, 0.42);
        }
        .dash-hero::before {
            content: '';
            position: absolute;
            right: -60px;
            top: -60px;
            width: 240px;
            height: 240px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.28) 0%, rgba(56, 189, 248, 0) 70%);
            pointer-events: none;
        }
        .dash-hero::after {
            content: '';
            position: absolute;
            left: 35%;
            bottom: -90px;
            width: 280px;
            height: 160px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(31, 94, 219, 0.35) 0%, rgba(31, 94, 219, 0) 70%);
            pointer-events: none;
        }
        .dash-avatar-wrap {
            position: relative;
            width: 64px;
            height: 64px;
        }
        .dash-avatar-img,
        .dash-avatar-fallback {
            width: 64px;
            height: 64px;
            border-radius: 999px;
            border: 2px solid rgba(255, 255, 255, 0.65);
            box-shadow: 0 8px 22px -6px rgba(0, 0, 0, 0.45);
        }
        .dash-avatar-img {
            object-fit: cover;
        }
        .dash-avatar-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            font-weight: 800;
            color: #ffffff;
            background: linear-gradient(135deg, #2563eb 0%, #38bdf8 100%);
        }
        .dash-avatar-status {
            position: absolute;
            right: 2px;
            bottom: 2px;
            width: 14px;
            height: 14px;
            border-radius: 999px;
            background: #22c55e;
            border: 2px solid #0f172a;
        }
        .dash-hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.13);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #f8fafc;
            backdrop-filter: blur(8px);
        }
        .dash-hero-btn-light {
            background: #ffffff;
            color: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 0.7rem;
            font-weight: 700;
            padding: 0.48rem 0.95rem;
            box-shadow: 0 8px 20px -6px rgba(15, 23, 42, 0.35);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }
        .dash-hero-btn-light:hover {
            background: #f8fafc;
            color: #1F5EDB;
            transform: translateY(-1px);
        }
        .dash-hero-btn-ghost {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.26);
            border-radius: 0.7rem;
            font-weight: 600;
            padding: 0.48rem 0.95rem;
        }
        .dash-hero-btn-ghost:hover {
            background: rgba(255, 255, 255, 0.22);
            color: #ffffff;
        }
        .dash-env-badge {
            font-size: 0.72rem;
            color: rgba(248, 250, 252, 0.78);
            background: rgba(15, 23, 42, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
        }
        .dash-live-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #22c55e;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            animation: dashLivePulse 2s infinite;
        }
        .dash-live-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.74rem;
            font-weight: 700;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.14);
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        [data-bs-theme="dark"] .dash-live-badge {
            color: #4ade80;
            background: rgba(34, 197, 94, 0.18);
        }

        /* Quick Actions Bar */
        .dash-quickbar {
            border-radius: 1rem;
        }
        .dash-quickbar-icon {
            width: 38px;
            height: 38px;
            border-radius: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(31, 94, 219, 0.12);
            color: #1F5EDB;
            font-size: 0.95rem;
        }
        [data-bs-theme="dark"] .dash-quickbar-icon {
            background: rgba(56, 189, 248, 0.15);
            color: #60a5fa;
        }
        .dash-action-pill {
            border-radius: 999px;
            padding: 0.38rem 0.9rem;
            font-weight: 600;
            font-size: 0.8rem;
            background: var(--bs-tertiary-bg);
            border: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
            transition: transform 0.16s ease, border-color 0.16s ease, background-color 0.16s ease;
        }
        .dash-action-pill:hover {
            transform: translateY(-1px);
            border-color: #1F5EDB;
            color: #1F5EDB;
            background: rgba(31, 94, 219, 0.08);
        }
        [data-bs-theme="dark"] .dash-action-pill:hover {
            color: #93c5fd;
            border-color: #60a5fa;
            background: rgba(59, 130, 246, 0.15);
        }

        /* Inventário Content Box */
        .dash-content-box {
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
            align-items: center;
        }
        .dash-content-box:hover {
            transform: translateY(-2px);
            border-color: rgba(31, 94, 219, 0.45);
        }
        .dash-box-arrow {
            color: var(--bs-secondary-color);
            opacity: 0.45;
            padding-right: 0.5rem;
            font-size: 0.78rem;
            transition: transform 0.18s ease, opacity 0.18s ease;
        }
        .dash-content-box:hover .dash-box-arrow {
            opacity: 1;
            transform: translateX(3px);
            color: #1F5EDB;
        }

        /* Metric Tiles (Visitas & Saúde do Sistema) */
        .dash-metric-tile {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            padding: 0.95rem 1rem;
            border-radius: 0.85rem;
            background: var(--bs-tertiary-bg);
            border: 1px solid var(--bs-border-color);
            height: 100%;
        }
        .dash-tile-icon {
            width: 44px;
            height: 44px;
            border-radius: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .dash-tile-label {
            font-size: 0.74rem;
            font-weight: 600;
            color: var(--bs-secondary-color);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .dash-tile-value {
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1.2;
            color: var(--bs-body-color);
        }
        .dash-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.36rem 0.8rem;
            border-radius: 999px;
            font-size: 0.78rem;
            background: var(--bs-tertiary-bg);
            border: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
        }
        .dash-subpanel {
            border-radius: 0.85rem;
            border: 1px solid var(--bs-border-color);
            overflow: hidden;
            background: var(--bs-body-bg);
        }
        .dash-subpanel-header {
            padding: 0.65rem 1rem;
            font-size: 0.8rem;
            font-weight: 700;
            background: var(--bs-tertiary-bg);
            border-bottom: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
        }
        .dash-chart-wrap {
            position: relative;
            width: 100%;
        }

        /* Vitality Card */
        .dash-vitality-card {
            border-radius: 1rem;
            overflow: hidden;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #0f2d4c 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 14px 32px -12px rgba(15, 23, 42, 0.45);
        }
        .dash-vitality-orb {
            position: absolute;
            top: -24px;
            right: -24px;
            width: 130px;
            height: 130px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.25), transparent 70%);
            pointer-events: none;
        }
        .dash-vitality-eyebrow {
            font-size: 0.68rem;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .dash-vitality-icon {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.32);
        }
        .dash-vitality-bar {
            height: 5px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }
        .dash-vitality-bar > div {
            height: 100%;
            border-radius: 999px;
        }
        .dash-vitality-btn {
            padding: 0.55rem 1rem;
            border-radius: 0.7rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            color: #e2e8f0;
            font-size: 0.78rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.18s ease, color 0.18s ease;
        }
        .dash-vitality-btn:hover {
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
        }
        .dash-pulse-icon {
            animation: dashPulseAnim 1.5s ease-in-out infinite;
        }

        /* Mini stat bar (Saúde do Sistema) */
        .dash-mini-stat {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0.95rem;
            border-radius: 0.75rem;
            background: var(--bs-tertiary-bg);
            border: 1px solid var(--bs-border-color);
        }

        /* FullCalendar Harmonização Light/Dark */
        .dash-calendar {
            min-height: 430px;
        }
        .dash-calendar .fc {
            --fc-border-color: var(--bs-border-color);
            --fc-page-bg-color: transparent;
            --fc-neutral-bg-color: var(--bs-tertiary-bg);
            --fc-today-bg-color: rgba(31, 94, 219, 0.09);
            --fc-button-bg-color: #1F5EDB;
            --fc-button-border-color: #1F5EDB;
            --fc-button-hover-bg-color: #1D3FC4;
            --fc-button-hover-border-color: #1D3FC4;
            --fc-button-active-bg-color: #1735a8;
            --fc-button-active-border-color: #1735a8;
            --fc-event-bg-color: #1F5EDB;
            --fc-event-border-color: #1F5EDB;
        }
        .dash-calendar .fc-toolbar-title {
            font-size: 1.05rem !important;
            font-weight: 700;
            text-transform: capitalize;
        }
        .dash-calendar .fc-button {
            font-size: 0.78rem !important;
            font-weight: 600;
            border-radius: 0.5rem !important;
            padding: 0.35rem 0.65rem !important;
            text-transform: capitalize;
        }
        .dash-calendar .fc-event {
            cursor: pointer;
            border-radius: 0.35rem;
            padding: 1px 4px;
            font-size: 0.75rem;
        }
        .dash-calendar .fc-col-header-cell-cushion,
        .dash-calendar .fc-daygrid-day-number {
            color: var(--bs-body-color);
            text-decoration: none;
        }

        @keyframes dashLivePulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.65); }
            70% { box-shadow: 0 0 0 7px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
        @keyframes dashPulseAnim {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.18); opacity: 0.8; }
        }
    </style>
@endpush

@push('scripts')
    {{-- FullCalendar 6 & Realtime --}}
    @include('partials.service-visits-realtime')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/pt-br.global.min.js"></script>

    <script>
        (function () {
            function isDarkMode() {
                return document.documentElement.getAttribute('data-bs-theme') === 'dark';
            }

            function destroyChartIfExists(canvas) {
                if (typeof Chart === 'undefined' || !canvas) return;
                const existing = Chart.getChart(canvas);
                if (existing) existing.destroy();
            }

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
                    ].map(([label, value]) => `<span class="dash-chip">${label}: <strong>${value}</strong></span>`).join('');
                }

                const topItemsBody = document.getElementById('legacy-top-items');
                if (topItemsBody) {
                    topItemsBody.innerHTML = topItems.length
                        ? topItems.map((item) => `
                            <tr>
                                <td class="fw-medium">${item.label}</td>
                                <td><span class="badge text-bg-secondary rounded-pill">${item.type}</span></td>
                                <td class="text-end fw-bold">${item.total}</td>
                            </tr>
                        `).join('')
                        : '<tr><td colspan="3" class="text-center text-body-secondary py-4">Ainda não há visitas registradas.</td></tr>';
                }

                const leadersBody = document.getElementById('legacy-owner-leaders');
                if (leadersBody) {
                    leadersBody.innerHTML = leaders.length
                        ? leaders.map((leader) => `
                            <tr>
                                <td class="fw-medium">${leader.name}</td>
                                <td class="text-body-secondary small">Cursos: ${leader.curso} • Eventos: ${leader.evento} • Mentorias: ${leader.mentoria}</td>
                                <td class="text-end fw-bold">${leader.total}</td>
                            </tr>
                        `).join('')
                        : '<tr><td colspan="3" class="text-center text-body-secondary py-4">Ainda não há responsáveis ranqueados.</td></tr>';
                }
            }

            function initAdminDashboard() {
                const dark = isDarkMode();
                const textColor = dark ? '#cbd5e1' : '#475569';
                const gridColor = dark ? 'rgba(148, 163, 184, 0.14)' : 'rgba(15, 23, 42, 0.06)';

                renderLegacyAdminServiceVisits({!! json_encode([
                    'serviceVisitSummary' => $serviceVisitSummary,
                    'serviceVisitTopItems' => $serviceVisitTopItems ?? [],
                    'serviceVisitOwnerLeaders' => $serviceVisitOwnerLeaders ?? [],
                ]) !!});

                if (window.UNNServiceVisitsRealtime && typeof window.UNNServiceVisitsRealtime.start === 'function') {
                    window.UNNServiceVisitsRealtime.start({
                        statsUrl: @json(route('admin.dashboard.stats')),
                        refreshMs: @json(max(3000, (int) config('dashboard.refresh_interval_ms', 30000))),
                        onPayload: renderLegacyAdminServiceVisits,
                    });
                }

                // 1. FullCalendar
                const calendarEl = document.getElementById('calendar');
                if (calendarEl && typeof FullCalendar !== 'undefined') {
                    calendarEl.innerHTML = '';
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        locale: 'pt-br',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,listWeek'
                        },
                        events: {!! json_encode($calendarEvents ?? []) !!},
                        height: 'auto',
                        contentHeight: 460,
                        eventClick: function (info) {
                            if (info.event.url) {
                                info.jsEvent.preventDefault();
                                window.location.href = info.event.url;
                            }
                        }
                    });
                    calendar.render();
                }

                @if($isAdminUser)
                    // 2. Gráfico de Vendas
                    const salesCanvas = document.getElementById('salesChart');
                    if (salesCanvas && typeof Chart !== 'undefined') {
                        destroyChartIfExists(salesCanvas);
                        const ctx = salesCanvas.getContext('2d');
                        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
                        gradient.addColorStop(0, 'rgba(31, 94, 219, 0.30)');
                        gradient.addColorStop(1, 'rgba(31, 94, 219, 0.01)');

                        new Chart(salesCanvas, {
                            type: 'line',
                            data: {
                                labels: {!! json_encode($months ?? []) !!},
                                datasets: [{
                                    label: 'Vendas (R$)',
                                    data: {!! json_encode($salesChartData ?? []) !!},
                                    borderColor: '#1F5EDB',
                                    backgroundColor: gradient,
                                    pointBackgroundColor: '#1F5EDB',
                                    pointBorderColor: dark ? '#0f172a' : '#ffffff',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    borderWidth: 2.5,
                                    fill: true,
                                    tension: 0.38
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: function (context) {
                                                const val = Number(context.parsed.y || 0);
                                                return ' R$ ' + val.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: { color: gridColor },
                                        ticks: {
                                            color: textColor,
                                            callback: function (value) {
                                                return 'R$ ' + Number(value).toLocaleString('pt-BR');
                                            }
                                        }
                                    },
                                    x: {
                                        grid: { display: false },
                                        ticks: { color: textColor }
                                    }
                                }
                            }
                        });
                    }

                    // 3. Gráfico Pedidos por Status
                    const ordersCanvas = document.getElementById('ordersStatusChart');
                    if (ordersCanvas && typeof Chart !== 'undefined') {
                        destroyChartIfExists(ordersCanvas);
                        new Chart(ordersCanvas, {
                            type: 'doughnut',
                            data: {
                                labels: {!! json_encode(array_keys($ordersByStatus ?? [])) !!},
                                datasets: [{
                                    data: {!! json_encode(array_values($ordersByStatus ?? [])) !!},
                                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#0ea5e9', '#64748b', '#1F5EDB'],
                                    borderWidth: 2,
                                    borderColor: dark ? '#1e293b' : '#ffffff'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '64%',
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            color: textColor,
                                            usePointStyle: true,
                                            pointStyle: 'circle',
                                            padding: 14,
                                            font: { size: 11 }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // 4. Jobs Status (Mini)
                    const jobsCanvas = document.getElementById('jobsStatusChart');
                    if (jobsCanvas && typeof Chart !== 'undefined') {
                        destroyChartIfExists(jobsCanvas);
                        new Chart(jobsCanvas, {
                            type: 'doughnut',
                            data: {
                                labels: ['Pendentes', 'Concluídos'],
                                datasets: [{
                                    data: [{{ $pendingJobsCount ?? 0 }}, {{ $jobsStatus['Concluídos'] ?? 0 }}],
                                    backgroundColor: ['#ef4444', '#10b981'],
                                    borderWidth: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '62%',
                                plugins: { legend: { display: false } }
                            }
                        });
                    }

                    // 5. Customer Health Chart (Business Vitality Index)
                    const healthCanvas = document.getElementById('customerHealthChart');
                    if (healthCanvas && typeof Chart !== 'undefined') {
                        destroyChartIfExists(healthCanvas);
                        new Chart(healthCanvas, {
                            type: 'doughnut',
                            data: {
                                labels: ['Alta', 'Média', 'Baixa'],
                                datasets: [{
                                    data: [{{ $customerHealth['Alta'] ?? 0 }}, {{ $customerHealth['Média'] ?? 0 }}, {{ $customerHealth['Baixa'] ?? 0 }}],
                                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                                    borderWidth: 2,
                                    borderColor: ['rgba(16,185,129,0.2)', 'rgba(245,158,11,0.2)', 'rgba(239,68,68,0.2)']
                                }]
                            },
                            options: {
                                responsive: false,
                                maintainAspectRatio: false,
                                cutout: '74%',
                                plugins: { legend: { display: false }, tooltip: { enabled: true } }
                            }
                        });
                    }

                    // 6. Saúde do Sistema (AJAX)
                    function loadSystemHealth() {
                        const container = document.getElementById('system-health-body');
                        const refreshBtn = document.getElementById('btn-refresh-health');
                        const icon = refreshBtn ? refreshBtn.querySelector('i') : null;
                        if (!container) return;

                        if (icon) icon.classList.add('fa-spin');

                        fetch(@json(route('admin.dashboard.system-health')), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(r => r.json())
                        .then(data => {
                            const hasQuota = !!data.disk.quota_available;
                            const diskPercent = hasQuota ? data.disk.percent : null;
                            const diskColor = !hasQuota ? '#3b82f6' : (diskPercent > 90 ? '#ef4444' : (diskPercent > 75 ? '#f59e0b' : '#10b981'));
                            const cronColor = data.cron.active ? '#10b981' : '#ef4444';
                            const cronLabel = data.cron.active ? 'Ativo' : 'Inativo';

                            container.innerHTML = `
                                <div class="row g-3 mb-3">
                                    <div class="col-xl-3 col-sm-6">
                                        <div class="dash-metric-tile">
                                            <span class="dash-tile-icon" style="background:${diskColor}20; color:${diskColor};">
                                                <i class="fas fa-hdd"></i>
                                            </span>
                                            <div class="flex-fill min-w-0">
                                                <div class="dash-tile-label">Hospedagem</div>
                                                <div class="dash-tile-value">${hasQuota ? (diskPercent + '%') : (data.disk.install_used_gb + ' GB')}</div>
                                                <small class="text-body-secondary d-block text-truncate">
                                                    ${hasQuota ? (data.disk.account_used_gb + ' GB / ' + data.disk.total_gb + ' GB na conta') : (data.disk.install_used_gb + ' GB em public_html')}
                                                </small>
                                                ${hasQuota
                                                    ? '<div class="progress mt-1" style="height:4px;"><div class="progress-bar" style="width:' + diskPercent + '%; background:' + diskColor + ';"></div></div>'
                                                    : '<small class="text-body-secondary d-block" style="font-size:0.7rem;">Quota da conta não exposta pelo shell</small>'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-sm-6">
                                        <div class="dash-metric-tile">
                                            <span class="dash-tile-icon bg-info-subtle text-info">
                                                <i class="fas fa-database"></i>
                                            </span>
                                            <div>
                                                <div class="dash-tile-label">Banco de Dados</div>
                                                <div class="dash-tile-value">${data.database.size_mb} MB</div>
                                                <small class="text-body-secondary">PHP ${data.php_version}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-sm-6">
                                        <div class="dash-metric-tile">
                                            <span class="dash-tile-icon bg-primary-subtle text-primary">
                                                <i class="fas fa-users-cog"></i>
                                            </span>
                                            <div>
                                                <div class="dash-tile-label">Capacidade</div>
                                                <div class="dash-tile-value">~${data.capacity.estimated_concurrent}</div>
                                                <small class="text-body-secondary">Simultâneos (${data.capacity.memory_limit})</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-sm-6">
                                        <div class="dash-metric-tile">
                                            <span class="dash-tile-icon" style="background:${cronColor}20; color:${cronColor};">
                                                <i class="fas fa-clock"></i>
                                            </span>
                                            <div>
                                                <div class="dash-tile-label">Cron Interno</div>
                                                <div class="dash-tile-value" style="color:${cronColor};">${cronLabel}</div>
                                                <small class="text-body-secondary">${data.cron.last_run ? 'Último: ' + data.cron.last_run : 'Nunca executou'}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="dash-mini-stat">
                                            <span class="dash-tile-icon bg-primary-subtle text-primary" style="width:36px; height:36px; font-size:0.9rem;">
                                                <i class="fas fa-user-clock"></i>
                                            </span>
                                            <div>
                                                <div class="small text-body-secondary">Online agora</div>
                                                <div class="fw-bold">${data.users.online_now} <small class="fw-normal text-body-secondary">/ ${data.users.total} total</small></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="dash-mini-stat">
                                            <span class="dash-tile-icon bg-warning-subtle text-warning" style="width:36px; height:36px; font-size:0.9rem;">
                                                <i class="fas fa-shopping-cart"></i>
                                            </span>
                                            <div>
                                                <div class="small text-body-secondary">Pedidos Pendentes</div>
                                                <div class="fw-bold">${data.orders_pending}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="dash-mini-stat">
                                            <span class="dash-tile-icon bg-info-subtle text-info" style="width:36px; height:36px; font-size:0.9rem;">
                                                <i class="fas fa-tasks"></i>
                                            </span>
                                            <div>
                                                <div class="small text-body-secondary">Jobs na Fila</div>
                                                <div class="fw-bold">${data.queue.pending_jobs}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                ${hasQuota && diskPercent > 90 ? '<div class="alert alert-danger mt-3 mb-0 py-2"><i class="fas fa-exclamation-triangle me-2"></i><strong>Atenção:</strong> Conta com ' + diskPercent + '% de uso. Apenas ' + data.disk.free_gb + ' GB livres.</div>' : ''}
                                ${!data.cron.active ? '<div class="alert alert-warning mt-3 mb-0 py-2"><i class="fas fa-exclamation-circle me-2"></i><strong>Cron inativo.</strong> Verifique se o middleware RunInternalCron está ativo e se há acessos regulares ao sistema.</div>' : ''}
                            `;
                        })
                        .catch(() => {
                            container.innerHTML = '<div class="alert alert-secondary mb-0"><i class="fas fa-exclamation-circle me-2"></i>Não foi possível carregar informações do sistema.</div>';
                        })
                        .finally(() => {
                            if (icon) icon.classList.remove('fa-spin');
                        });
                    }

                    loadSystemHealth();
                    const refreshBtn = document.getElementById('btn-refresh-health');
                    if (refreshBtn && !refreshBtn.dataset.bound) {
                        refreshBtn.dataset.bound = '1';
                        refreshBtn.addEventListener('click', loadSystemHealth);
                    }
                @endif
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAdminDashboard);
            } else {
                initAdminDashboard();
            }
        })();
    </script>
@endpush
