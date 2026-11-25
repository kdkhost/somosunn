@extends('admin.layouts.app')

@section('page_title', 'Afiliados e Indicações')
@section('breadcrumb')<li class="breadcrumb-item active">Afiliados e Indicações</li>@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        /* Estilos Globais e Abas Estilo React */
        .referral-legacy-tabs {
            border-bottom: 0 !important;
            gap: 0.5rem;
            padding: 0.5rem 0.5rem 0 0.5rem;
            flex-wrap: wrap;
        }
        .referral-legacy-tabs .nav-link {
            font-weight: 600;
            font-size: 0.875rem;
            color: #64748b;
            border: 1px solid transparent !important;
            border-radius: 0.75rem !important;
            padding: 0.55rem 1.15rem !important;
            transition: all 0.15s ease;
            background: transparent;
        }
        .referral-legacy-tabs .nav-link:hover {
            color: #1F5EDB;
            background: #f1f5f9;
        }
        .referral-legacy-tabs .nav-link.active {
            color: #ffffff !important;
            background: #1F5EDB !important;
            box-shadow: 0 4px 12px rgba(31, 94, 219, 0.28) !important;
        }

        .dark-mode .referral-legacy-tabs .nav-link {
            color: #94a3b8;
        }
        .dark-mode .referral-legacy-tabs .nav-link:hover {
            color: #ffffff;
            background: #1e293b;
        }
        .dark-mode .referral-legacy-tabs .nav-link.active {
            color: #ffffff !important;
            background: #1F5EDB !important;
        }

        .referral-legacy-subtabs { gap: .65rem; }
        .referral-legacy-subtabs .nav-link { border-radius: 999px; font-weight: 700; color: #495057; background: #f4f6f9; }
        .referral-legacy-subtabs .nav-link.active { background: #007bff; color: #fff; box-shadow: 0 .35rem .85rem rgba(0, 123, 255, .18); }
        .referral-avatar, .referral-avatar-lg { border-radius: 999px; object-fit: cover; flex-shrink: 0; background: #f4f6f9; }
        .referral-avatar { width: 42px; height: 42px; }
        .referral-avatar-lg { width: 56px; height: 56px; }

        /* Hero Card do Link */
        .referral-hero-card {
            border-radius: 1.25rem !important;
            border: 1px solid #e2e8f0 !important;
            background-color: #ffffff;
        }
        .dark-mode .referral-hero-card {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }

        /* Input de Link Estilo React */
        .referral-link-input-group .form-control {
            height: 48px !important;
            border-radius: 0.75rem 0 0 0.75rem !important;
            border: 1px solid #cbd5e1 !important;
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            background-color: #f8fafc !important;
            color: #1e293b !important;
        }
        .dark-mode .referral-link-input-group .form-control {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .referral-link-input-group .input-group-text {
            background-color: #f1f5f9 !important;
            border: 1px solid #cbd5e1 !important;
            border-right: 0 !important;
            border-radius: 0.75rem 0 0 0.75rem !important;
            color: #1F5EDB !important;
        }
        .dark-mode .referral-link-input-group .input-group-text {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #60a5fa !important;
        }
        .referral-link-input-group .btn-copy-link {
            height: 48px !important;
            border-radius: 0 0.75rem 0.75rem 0 !important;
            padding: 0 1.5rem !important;
            font-weight: 600 !important;
            background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 100%) !important;
            border: none !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(31, 94, 219, 0.25) !important;
            transition: all 0.2s ease !important;
        }
        .referral-link-input-group .btn-copy-link:hover {
            box-shadow: 0 6px 18px rgba(31, 94, 219, 0.35) !important;
            transform: translateY(-1px);
        }

        /* Botões de Compartilhamento Social Modernos */
        .btn-social-share {
            border-radius: 9999px !important;
            font-size: 0.8125rem !important;
            font-weight: 600 !important;
            padding: 0.5rem 1rem !important;
            display: inline-flex !important;
            align-items: center !important;
            border: none !important;
            color: #ffffff !important;
            transition: all 0.15s ease !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
        }
        .btn-social-share:hover {
            transform: translateY(-2px);
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }
        .btn-share-whatsapp { background: #25D366 !important; }
        .btn-share-telegram { background: #0088cc !important; }
        .btn-share-linkedin { background: #0a66c2 !important; }
        .btn-share-email { background: #475569 !important; }

        /* Mini Cards de Código e Regras */
        .referral-mini-card {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.15rem;
            background: #f8fafc;
            height: 100%;
            transition: all 0.2s ease;
        }
        .dark-mode .referral-mini-card {
            background: #0f172a !important;
            border-color: #334155 !important;
        }

        /* Cards de Métricas e KPIs */
        .ref-kpi-card {
            border-radius: 1rem !important;
            border: 1px solid #e2e8f0 !important;
            background-color: #ffffff;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            height: 100%;
        }
        .ref-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
        }
        .dark-mode .ref-kpi-card {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }
        .dark-mode .ref-kpi-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4) !important;
        }

        .ref-kpi-total { background: linear-gradient(135deg, rgba(31, 94, 219, 0.04) 0%, #ffffff 100%); }
        .ref-kpi-converted { background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, #ffffff 100%); }
        .ref-kpi-points { background: linear-gradient(135deg, rgba(245, 158, 11, 0.06) 0%, #ffffff 100%); }
        .ref-kpi-potential { background: linear-gradient(135deg, rgba(147, 51, 234, 0.05) 0%, #ffffff 100%); }

        .dark-mode .ref-kpi-total { background: linear-gradient(135deg, rgba(31, 94, 219, 0.16) 0%, #1e293b 100%) !important; }
        .dark-mode .ref-kpi-converted { background: linear-gradient(135deg, rgba(16, 185, 129, 0.16) 0%, #1e293b 100%) !important; }
        .dark-mode .ref-kpi-points { background: linear-gradient(135deg, rgba(245, 158, 11, 0.16) 0%, #1e293b 100%) !important; }
        .dark-mode .ref-kpi-potential { background: linear-gradient(135deg, rgba(147, 51, 234, 0.16) 0%, #1e293b 100%) !important; }

        .ref-kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .ref-kpi-icon-blue { background: rgba(31, 94, 219, 0.12); color: #1F5EDB; }
        .ref-kpi-icon-green { background: rgba(16, 185, 129, 0.12); color: #10b981; }
        .ref-kpi-icon-amber { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
        .ref-kpi-icon-purple { background: rgba(147, 51, 234, 0.12); color: #9333ea; }

        .dark-mode .ref-kpi-icon-blue { background: rgba(31, 94, 219, 0.28); color: #60a5fa; }
        .dark-mode .ref-kpi-icon-green { background: rgba(16, 185, 129, 0.28); color: #34d399; }
        .dark-mode .ref-kpi-icon-amber { background: rgba(245, 158, 11, 0.28); color: #fbbf24; }
        .dark-mode .ref-kpi-icon-purple { background: rgba(147, 51, 234, 0.28); color: #c084fc; }

        /* Card Como Funciona (Steps) */
        .step-circle {
            width: 36px;
            height: 36px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.875rem;
            color: #ffffff;
            flex-shrink: 0;
        }
        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            border-radius: 0.85rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            transition: all 0.15s ease;
        }
        .step-item:hover {
            transform: translateX(3px);
        }
        .dark-mode .step-item {
            background: #0f172a !important;
            border-color: #334155 !important;
        }

        /* Badges Suaves */
        .badge-primary-soft { background-color: rgba(31, 94, 219, 0.12) !important; color: #1F5EDB !important; }
        .badge-success-soft { background-color: rgba(16, 185, 129, 0.12) !important; color: #10b981 !important; }
        .badge-warning-soft { background-color: rgba(245, 158, 11, 0.14) !important; color: #f59e0b !important; }
        .badge-purple-soft { background-color: rgba(147, 51, 234, 0.12) !important; color: #9333ea !important; }
        .badge-secondary-soft { background-color: rgba(100, 116, 139, 0.12) !important; color: #64748b !important; }

        .dark-mode .badge-primary-soft { background-color: rgba(31, 94, 219, 0.25) !important; color: #60a5fa !important; }
        .dark-mode .badge-success-soft { background-color: rgba(16, 185, 129, 0.25) !important; color: #34d399 !important; }
        .dark-mode .badge-warning-soft { background-color: rgba(245, 158, 11, 0.25) !important; color: #fbbf24 !important; }
        .dark-mode .badge-purple-soft { background-color: rgba(147, 51, 234, 0.25) !important; color: #c084fc !important; }
        .dark-mode .badge-secondary-soft { background-color: rgba(100, 116, 139, 0.25) !important; color: #94a3b8 !important; }

        .referral-activity-feed .list-group-item { border-left: 0; border-right: 0; padding-left: 0; padding-right: 0; }
        .referral-activity-feed .list-group-item:first-child { border-top: 0; }
        .referral-activity-feed .list-group-item:last-child { border-bottom: 0; }
        .referral-source-url, .referral-break-anywhere { word-break: break-word; }
        .referral-empty-state { min-height: 180px; display: flex; align-items: center; justify-content: center; text-align: center; color: #6c757d; background: #f8f9fa; border: 1px dashed #ced4da; border-radius: .9rem; padding: 2rem; }
        .dark-mode .referral-empty-state { background: #0f172a !important; border-color: #334155 !important; color: #94a3b8 !important; }
        .referral-table-actions { white-space: nowrap; }
        .dataTables_wrapper .row:first-child, .dataTables_wrapper .row:last-child { margin-left: 0; margin-right: 0; }
        .dataTables_wrapper .dataTables_filter label, .dataTables_wrapper .dataTables_length label { font-weight: 600; }
        .dataTables_wrapper .dataTables_filter input, .dataTables_wrapper .dataTables_length select { margin-left: .35rem; }
        
        /* Cartão de Rastreio */
        .referral-tracking-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 1.5rem;
            padding: 1.5rem 1rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
            transition: transform 0.2s;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.05);
        }
        .referral-tracking-card:hover { transform: translateY(-3px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08); }
        .referral-tracking-icon {
            width: 4rem;
            height: 4rem;
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            margin-bottom: 1rem;
            box-shadow: 0 0.5rem 1.25rem -0.25rem rgba(0,0,0,0.1);
        }
        .referral-tracking-value {
            font-size: 1.75rem;
            font-weight: 900;
            line-height: 1.2;
            color: #1f2937;
            margin-bottom: 0.25rem;
            word-break: break-all;
        }
        .dark-mode .referral-tracking-card { background: #1e293b !important; border-color: #334155 !important; }
        .dark-mode .referral-tracking-value { color: #f8fafc !important; }
        .referral-tracking-label {
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        .referral-tracking-sub {
            font-size: 0.7rem;
            font-weight: 600;
            color: #9ca3af;
        }
        @media (max-width: 575.98px) {
            .referral-tracking-icon { width: 3.5rem; height: 3.5rem; font-size: 1.25rem; }
            .referral-tracking-value { font-size: 1.5rem; }
        }
    </style>
@endpush

@section('content')
    @php
        $pointsRule = \App\Models\PointsRule::where('key', 'referral')->where('active', true)->first();
        $pointsPerReferral = $pointsRule?->points ?? 0;
        $conversionRate = $totalReferred > 0 ? round(($convertedCount / $totalReferred) * 100) : 0;
        $potentialPoints = $pendingCount * $pointsPerReferral;
        $selectedScopeLabel = $selectedReferrer?->name ? 'Visão filtrada do afiliado' : 'Visão global da plataforma';
        $referralsPreview = $referredUsers->take(5);
        $activeTab = request('tab');
        $activeTrackingTab = request('tracking_tab');

        if ($errors->has('device_name') || $errors->has('api_tokens') || $apiTokenPlainText) {
            $activeTab = 'api';
        } elseif ($errors->has('status') || $errors->has('admin_notes')) {
            $activeTab = 'sandbox';
        } elseif (!in_array($activeTab, ['programa', 'indicados', 'api', 'sandbox', 'tracking'], true)) {
            $activeTab = $selectedReferrer ? 'tracking' : 'programa';
        }

        if (!in_array($activeTrackingTab, ['analysis', 'events'], true)) {
            $activeTrackingTab = 'analysis';
        }
    @endphp

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    {{-- Cabeçalho Moderno Estilo React SaaS --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h4 class="fw-bold text-dark mb-0 d-flex align-items-center" style="letter-spacing: -0.02em;">
                    <i class="fas fa-share-nodes text-primary me-2"></i> Programa de Afiliados e Indicações
                </h4>
                <span class="badge badge-primary-soft font-weight-600 px-2.5 py-1 rounded-pill" style="font-size: 0.78rem;">
                    <i class="fas fa-circle-check me-1"></i> Programa Ativo
                </span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Monitore seu link pessoal, conversão de indicados, comissões em pontos e métricas do programa de parceiros.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-none" data-referral-tab-target="tracking">
                <i class="fas fa-chart-line me-1"></i> Rastreio Global
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-none" data-referral-tab-target="api">
                <i class="fas fa-key me-1"></i> API Pessoal
            </button>
        </div>
    </div>

    <div class="card card-primary card-outline shadow-sm border-0 mb-4" style="border-radius: 1.25rem;">
        <div class="card-header p-2 border-bottom">
            <ul class="nav nav-tabs referral-legacy-tabs" id="admin-referrals-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link {{ $activeTab === 'programa' ? 'active' : '' }}" id="admin-referrals-programa-tab" data-bs-toggle="pill" href="#admin-referrals-programa" role="tab" data-tab-name="programa"><i class="fas fa-link me-1"></i> Programa e link pessoal</a></li>
                <li class="nav-item"><a class="nav-link {{ $activeTab === 'indicados' ? 'active' : '' }}" id="admin-referrals-indicados-tab" data-bs-toggle="pill" href="#admin-referrals-indicados" role="tab" data-tab-name="indicados"><i class="fas fa-users me-1"></i> Indicados</a></li>
                <li class="nav-item"><a class="nav-link {{ $activeTab === 'api' ? 'active' : '' }}" id="admin-referrals-api-tab" data-bs-toggle="pill" href="#admin-referrals-api" role="tab" data-tab-name="api"><i class="fas fa-key me-1"></i> API pessoal</a></li>
                <li class="nav-item"><a class="nav-link {{ $activeTab === 'sandbox' ? 'active' : '' }}" id="admin-referrals-sandbox-tab" data-bs-toggle="pill" href="#admin-referrals-sandbox" role="tab" data-tab-name="sandbox"><i class="fas fa-vial me-1"></i> Sandbox</a></li>
                <li class="nav-item"><a class="nav-link {{ $activeTab === 'tracking' ? 'active' : '' }}" id="admin-referrals-tracking-tab" data-bs-toggle="pill" href="#admin-referrals-tracking" role="tab" data-tab-name="tracking"><i class="fas fa-chart-line me-1"></i> Rastreio global</a></li>
            </ul>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="tab-content">
                {{-- ABA: PROGRAMA E LINK PESSOAL (REMODELADA COM DESIGN REACT SAAS) --}}
                <div class="tab-pane fade {{ $activeTab === 'programa' ? 'show active' : '' }}" id="admin-referrals-programa" role="tabpanel">
                    <div class="row g-3">
                        {{-- Hero Card: Link de Indicação --}}
                        <div class="col-xl-7 mb-3 mb-xl-0">
                            <div class="card referral-hero-card shadow-sm h-100">
                                <div class="card-header bg-transparent border-0 pt-3.5 pb-2 px-3.5 d-flex align-items-center justify-content-between">
                                    <h5 class="fw-bold mb-0 d-flex align-items-center text-dark">
                                        <i class="fas fa-link text-primary me-2"></i> Seu Link de Indicação
                                    </h5>
                                    <span class="badge badge-success-soft font-weight-600 px-2.5 py-1 rounded-pill" style="font-size: 0.75rem;">
                                        <i class="fas fa-bolt me-1"></i> Rastreamento Ativo
                                    </span>
                                </div>
                                <div class="card-body px-3.5 pt-1 pb-3.5">
                                    <p class="text-muted small mb-3">
                                        Compartilhe seu link exclusivo. O sistema atribui automaticamente cadastros, checkouts e planos contratados ao seu perfil.
                                    </p>

                                    {{-- Campo de Cópia Estilizado --}}
                                    <div class="input-group referral-link-input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-link"></i></span>
                                        </div>
                                        <input id="adminReferralLinkInput" type="text" readonly class="form-control" value="{{ $referralLink }}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-copy-link" onclick="copyAdminReferralMaterial(this, document.getElementById('adminReferralLinkInput').value, 'legacy-admin-link', @json($referralLink))">
                                                <i class="fas fa-copy me-1"></i> Copiar Link
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Botões de Compartilhamento Social Modernos --}}
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3.5">
                                        <a href="https://wa.me/?text={{ urlencode('Ei! Faça parte da maior comunidade de empreendedores e networking do Brasil. Use meu link: ' . $referralLink) }}" target="_blank" rel="noopener noreferrer" onclick="trackAdminReferralShare('share', 'whatsapp', @json($referralLink))" class="btn btn-social-share btn-share-whatsapp">
                                            <i class="fab fa-whatsapp mr-1.5" style="font-size: 1rem;"></i> WhatsApp
                                        </a>
                                        <a href="https://t.me/share/url?url={{ urlencode($referralLink) }}&text={{ urlencode('Entre na plataforma com meu convite e comece a fazer networking!') }}" target="_blank" rel="noopener noreferrer" onclick="trackAdminReferralShare('share', 'telegram', @json($referralLink))" class="btn btn-social-share btn-share-telegram">
                                            <i class="fab fa-telegram-plane mr-1.5" style="font-size: 1rem;"></i> Telegram
                                        </a>
                                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($referralLink) }}" target="_blank" rel="noopener noreferrer" onclick="trackAdminReferralShare('share', 'linkedin', @json($referralLink))" class="btn btn-social-share btn-share-linkedin">
                                            <i class="fab fa-linkedin-in mr-1.5" style="font-size: 1rem;"></i> LinkedIn
                                        </a>
                                        <a href="mailto:?subject={{ urlencode('Convite para a comunidade UNN') }}&body={{ urlencode('Olá! Quero te convidar para a maior plataforma de networking para empreendedores. Acesse: ' . $referralLink) }}" onclick="trackAdminReferralShare('share', 'email', @json($referralLink))" class="btn btn-social-share btn-share-email">
                                            <i class="fas fa-envelope mr-1.5" style="font-size: 0.95rem;"></i> E-mail
                                        </a>
                                    </div>

                                    {{-- Mini Cards de Apoio --}}
                                    <div class="row g-2">
                                        <div class="col-md-5 mb-2 mb-md-0">
                                            <div class="referral-mini-card d-flex align-items-center justify-content-between">
                                                <div>
                                                    <span class="text-uppercase text-muted fw-bold d-block" style="font-size: 0.7rem; letter-spacing: 0.05em;">Código Único</span>
                                                    <h5 class="mb-0 fw-bold text-dark" style="font-family: monospace;">{{ $user->referral_code }}</h5>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted" title="Copiar código" onclick="copyAdminReferralMaterial(this, '{{ $user->referral_code }}', 'code', @json($referralLink))">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <div class="referral-mini-card d-flex align-items-center">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center mr-2.5 flex-shrink-0" style="width: 38px; height: 38px; background: rgba(31,94,219,0.12); color: #1F5EDB;">
                                                    <i class="fas fa-award"></i>
                                                </div>
                                                <div class="small">
                                                    <strong class="text-dark d-block">Comissão em Pontos</strong>
                                                    <span class="text-muted">Crédito gerado automaticamente após a assinatura de um plano pago.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Cards de Métricas e Performance (4 KPIs React) --}}
                        <div class="col-xl-5">
                            <div class="row g-2 h-100">
                                {{-- 1. Total Indicados --}}
                                <div class="col-sm-6 mb-2">
                                    <div class="card ref-kpi-card ref-kpi-total p-3 d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Indicados</span>
                                            <div class="ref-kpi-icon ref-kpi-icon-blue">
                                                <i class="fas fa-user-plus"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="fw-bold text-dark mb-0">{{ number_format($totalReferred) }}</h3>
                                            <small class="text-muted">Cadastros via seu convite</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- 2. Convertidos --}}
                                <div class="col-sm-6 mb-2">
                                    <div class="card ref-kpi-card ref-kpi-converted p-3 d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Convertidos</span>
                                            <div class="ref-kpi-icon ref-kpi-icon-green">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-baseline justify-content-between">
                                                <h3 class="fw-bold text-success mb-0">{{ number_format($convertedCount) }}</h3>
                                                <span class="badge badge-success-soft fw-bold" style="font-size: 0.75rem;">{{ $conversionRate }}%</span>
                                            </div>
                                            <div class="progress my-1.5" style="height: 5px; border-radius: 9999px; background: rgba(16,185,129,0.15);">
                                                <div class="progress-bar bg-success rounded-pill" style="width: {{ $conversionRate }}%"></div>
                                            </div>
                                            <small class="text-muted">Taxa de conversão em vendas</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- 3. Pontos Ganhos --}}
                                <div class="col-sm-6 mb-2 mb-sm-0">
                                    <div class="card ref-kpi-card ref-kpi-points p-3 d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Pontos Ganhos</span>
                                            <div class="ref-kpi-icon ref-kpi-icon-amber">
                                                <i class="fas fa-coins"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="fw-bold text-warning mb-0">{{ number_format($totalReferralPoints) }}</h3>
                                            <small class="text-muted">Créditos de indicação creditados</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- 4. Por Indicação & Potencial --}}
                                <div class="col-sm-6">
                                    <div class="card ref-kpi-card ref-kpi-potential p-3 d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Por Indicação</span>
                                            <div class="ref-kpi-icon ref-kpi-icon-purple">
                                                <i class="fas fa-gift"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="fw-bold text-dark mb-0" style="font-size: 1.25rem;">
                                                {{ $pointsPerReferral ? '+' . number_format($pointsPerReferral) . ' pts' : 'Configurar' }}
                                            </h4>
                                            @if($pendingCount > 0 && $pointsPerReferral > 0)
                                                <small class="text-muted d-block text-truncate" title="Potencial pendente: +{{ number_format($potentialPoints) }} pts">
                                                    Pendente: +{{ number_format($potentialPoints) }} pts
                                                </small>
                                            @else
                                                <small class="text-muted">Valor por plano ativo</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Grid Inferior: Prévia de Indicados & Como Funciona --}}
                    <div class="row mt-4 g-3">
                        {{-- Coluna 1: Pessoas que você indicou --}}
                        <div class="col-lg-7 mb-3 mb-lg-0">
                            <div class="card referral-hero-card shadow-sm mb-0 h-100">
                                <div class="card-header bg-transparent border-bottom py-3 px-3.5 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                                            <i class="fas fa-users text-primary me-2"></i> Pessoas que Você Indicou
                                        </h5>
                                        <small class="text-muted">Prévia dos cadastros mais recentes vinculados ao seu convite</small>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill font-weight-600 px-3 shadow-none" data-referral-tab-target="indicados">
                                        <i class="fas fa-table me-1"></i> Ver Todos ({{ number_format($totalReferred) }})
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    @if($referralsPreview->isEmpty())
                                        <div class="referral-empty-state m-3">
                                            <div>
                                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-light border mb-2" style="width: 54px; height: 54px;">
                                                    <i class="fas fa-user-group text-muted" style="font-size: 1.5rem;"></i>
                                                </div>
                                                <h6 class="fw-bold text-dark mb-1">Nenhuma indicação registrada ainda</h6>
                                                <p class="text-muted small mb-0">Compartilhe seu link exclusivo com parceiros para começar a pontuar.</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="bg-light">
                                                    <tr class="text-uppercase text-muted" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                                        <th>Membro</th>
                                                        <th>Data de Entrada</th>
                                                        <th>Plano Contratado</th>
                                                        <th class="text-end">Pontos</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y">
                                                    @foreach($referralsPreview as $referred)
                                                        @php
                                                            $logsFromUser = $referralPointsLogs->filter(function ($log) use ($referred) {
                                                                $meta = json_decode($log->meta ?? '{}', true);
                                                                return ($meta['new_user_id'] ?? null) == $referred->id;
                                                            });
                                                            $pointsFromThisUser = $logsFromUser->sum('points');
                                                            if ($referred->plan_id) {
                                                                if (!$referred->plan_expires_at) {
                                                                    $planStatusLabel = 'Vitalício';
                                                                    $planStatusClass = 'badge-purple-soft text-purple';
                                                                } elseif (\Carbon\Carbon::parse($referred->plan_expires_at)->isFuture()) {
                                                                    $planStatusLabel = 'Assinante ativo';
                                                                    $planStatusClass = 'badge-success-soft text-success';
                                                                } else {
                                                                    $planStatusLabel = 'Plano expirado';
                                                                    $planStatusClass = 'badge-warning-soft text-warning';
                                                                }
                                                                $planName = $plansMap[$referred->plan_id] ?? 'Plano';
                                                            } else {
                                                                $planStatusLabel = 'Sem plano';
                                                                $planStatusClass = 'badge-secondary-soft text-muted';
                                                                $planName = null;
                                                            }
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <img src="{{ $referred->photo ? asset($referred->photo) : asset('img/user.png') }}" alt="{{ $referred->name }}" class="referral-avatar mr-2.5 shadow-xs" loading="lazy" decoding="async">
                                                                    <div class="min-w-0">
                                                                        <div class="fw-bold text-dark text-truncate mb-0.5" style="font-size: 0.875rem;">{{ $referred->name }}</div>
                                                                        <small class="text-muted text-truncate d-block">{{ $referred->email }}</small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-muted small">
                                                                {{ optional($referred->created_at)->format('d/m/Y H:i') ?: '—' }}
                                                            </td>
                                                            <td>
                                                                @if($planName)
                                                                    <div class="font-weight-600 text-dark small mb-0.5">{{ $planName }}</div>
                                                                @endif
                                                                <span class="badge {{ $planStatusClass }} px-2 py-0.5 rounded-pill" style="font-size: 0.72rem;">{{ $planStatusLabel }}</span>
                                                            </td>
                                                            <td class="text-end fw-bold {{ $pointsFromThisUser > 0 ? 'text-success' : 'text-muted' }}" style="font-size: 0.9rem;">
                                                                {{ $pointsFromThisUser > 0 ? '+' . number_format($pointsFromThisUser) : '0' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Coluna 2: Como Funciona (Steps Estilizados) --}}
                        <div class="col-lg-5">
                            <div class="card referral-hero-card shadow-sm mb-0 h-100">
                                <div class="card-header bg-transparent border-bottom py-3 px-3.5">
                                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                                        <i class="fas fa-route text-primary me-2"></i> Como Funciona o Programa
                                    </h5>
                                </div>
                                <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                                    <div class="d-flex flex-column gap-2.5 mb-3">
                                        {{-- Step 1 --}}
                                        <div class="step-item">
                                            <div class="step-circle bg-primary shadow-xs">1</div>
                                            <div class="min-w-0">
                                                <h6 class="fw-bold text-dark mb-0.5">Copie seu Link Exclusivo</h6>
                                                <p class="text-muted small mb-0">Cada afiliado possui link e código únicos registrados na plataforma.</p>
                                            </div>
                                        </div>

                                        {{-- Step 2 --}}
                                        <div class="step-item">
                                            <div class="step-circle shadow-xs" style="background: #9333ea;">2</div>
                                            <div class="min-w-0">
                                                <h6 class="fw-bold text-dark mb-0.5">Compartilhe na sua Rede</h6>
                                                <p class="text-muted small mb-0">Envie pelo WhatsApp, LinkedIn, redes sociais, blog ou site pessoal.</p>
                                            </div>
                                        </div>

                                        {{-- Step 3 --}}
                                        <div class="step-item">
                                            <div class="step-circle bg-info shadow-xs">3</div>
                                            <div class="min-w-0">
                                                <h6 class="fw-bold text-dark mb-0.5">O Indicado Assina um Plano</h6>
                                                <p class="text-muted small mb-0">O sistema monitora todo o funil: cliques, cadastro e pagamento do plano.</p>
                                            </div>
                                        </div>

                                        {{-- Step 4 --}}
                                        <div class="step-item">
                                            <div class="step-circle bg-warning shadow-xs">4</div>
                                            <div class="min-w-0">
                                                <h6 class="fw-bold text-dark mb-0.5">Você Pontua e Resgata</h6>
                                                <p class="text-muted small mb-0">Os pontos entram após confirmação e podem ser trocados no marketplace.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-xl border d-flex align-items-center justify-content-between" style="background: rgba(31,94,219,0.04); border-color: rgba(31,94,219,0.2) !important;">
                                        <div class="small">
                                            <span class="fw-bold text-dark d-block">Pronto para divulgar?</span>
                                            <span class="text-muted">Copie seu link e envie para sua rede.</span>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm rounded-pill fw-bold px-3 shadow-none" onclick="copyAdminReferralMaterial(this, document.getElementById('adminReferralLinkInput').value, 'steps-cta', @json($referralLink))">
                                            <i class="fas fa-copy me-1"></i> Copiar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'indicados' ? 'show active' : '' }}" id="admin-referrals-indicados" role="tabpanel">
                    <div class="card card-secondary card-outline shadow-sm mb-0">
                        <div class="card-header border-0"><h3 class="card-title fw-bold"><i class="fas fa-users me-2"></i>Todos os indicados do seu link</h3></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="admin-referrals-members-table" class="table table-bordered table-hover table-striped w-100">
                                    <thead><tr><th>Membro indicado</th><th>Cadastro</th><th>Plano / Status</th><th class="text-end">Pontos</th></tr></thead>
                                    <tbody>
                                        @foreach($referredUsers as $referred)
                                            @php
                                                $logsFromUser = $referralPointsLogs->filter(function ($log) use ($referred) {
                                                    $meta = json_decode($log->meta ?? '{}', true);
                                                    return ($meta['new_user_id'] ?? null) == $referred->id;
                                                });
                                                $pointsFromThisUser = $logsFromUser->sum('points');
                                                if ($referred->plan_id) {
                                                    if (!$referred->plan_expires_at) {
                                                        $planStatusLabel = 'Vitalício';
                                                        $planStatusClass = 'badge-purple';
                                                    } elseif (\Carbon\Carbon::parse($referred->plan_expires_at)->isFuture()) {
                                                        $planStatusLabel = 'Assinante ativo';
                                                        $planStatusClass = 'text-bg-success';
                                                    } else {
                                                        $planStatusLabel = 'Plano expirado';
                                                        $planStatusClass = 'text-bg-warning';
                                                    }
                                                    $planName = $plansMap[$referred->plan_id] ?? 'Plano';
                                                } else {
                                                    $planStatusLabel = 'Sem plano';
                                                    $planStatusClass = 'text-bg-secondary';
                                                    $planName = null;
                                                }
                                            @endphp
                                            <tr>
                                                <td><div class="d-flex align-items-center"><img src="{{ $referred->photo ? asset($referred->photo) : asset('img/user.png') }}" alt="{{ $referred->name }}" class="referral-avatar me-3" loading="lazy" decoding="async"><div><div class="fw-bold">{{ $referred->name }}</div><small class="text-muted d-block">{{ $referred->email }}</small></div></div></td>
                                                <td data-order="{{ optional($referred->created_at)->timestamp ?? 0 }}">{{ optional($referred->created_at)->format('d/m/Y H:i') ?: '—' }}</td>
                                                <td data-order="{{ $planStatusLabel }}">@if($planName)<div class="fw-bold">{{ $planName }}</div>@endif<span class="badge {{ $planStatusClass }}">{{ $planStatusLabel }}</span></td>
                                                <td data-order="{{ $pointsFromThisUser }}" class="text-end fw-bold {{ $pointsFromThisUser > 0 ? 'text-success' : 'text-muted' }}">{{ $pointsFromThisUser > 0 ? '+' . number_format($pointsFromThisUser) : '0' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'api' ? 'show active' : '' }}" id="admin-referrals-api" role="tabpanel">
                    <div class="card card-info card-outline shadow-sm mb-0">
                        <div class="card-header border-0"><h3 class="card-title fw-bold"><i class="fas fa-key me-2"></i>Acesso API pessoal</h3></div>
                        <div class="card-body">
                            <p class="text-muted mb-4">Gere tokens por dispositivo, copie na hora, renomeie integrações e acompanhe último uso e IP.</p>
                            @if($errors->has('api_tokens'))<div class="alert alert-danger">{{ $errors->first('api_tokens') }}</div>@endif
                            @if(!$apiTokensEnabled)
                                <div class="alert alert-warning mb-0">A tabela de tokens da API ainda não está disponível neste ambiente. Rode as migrations para liberar esta área.</div>
                            @else
                                @if($apiTokenPlainText)
                                    <div class="alert alert-primary">
                                        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between"><div class="mb-3 mb-lg-0"><h5 class="fw-bold mb-1">Token gerado para {{ $apiTokenDeviceName ?: 'integração' }}</h5><p class="mb-0">Este token aparece uma única vez. Se perder, gere outro e revogue o antigo.</p></div><button type="button" class="btn btn-primary fw-bold" onclick='copyAdminReferralMaterial(this, @json($apiTokenPlainText), "api-token", @json(url('/api/v1/affiliate/overview')))'><i class="fas fa-copy me-1"></i> Copiar token</button></div>
                                        <pre class="mt-3 mb-0 p-3 bg-white border rounded text-break" style="white-space: pre-wrap;">{{ $apiTokenPlainText }}</pre>
                                    </div>
                                @endif
                                @if(!$apiTokenIpTrackingEnabled)<div class="alert alert-warning">O campo de IP ainda não existe neste ambiente. Depois de rodar <code>php artisan migrate</code>, esta tela também mostrará o último IP de uso.</div>@endif                                <div class="row">
                                    <div class="col-lg-4"><div class="referral-mini-card h-100"><h5 class="fw-bold">Gerar novo token</h5><p class="text-muted">Use um nome claro para o dispositivo, blog, CRM ou página privada que vai consumir sua API.</p><form action="{{ route('admin.referrals.tokens.store') }}" method="POST" class="mt-4">@csrf<div class="form-group"><label for="device_name">Dispositivo / integração</label><input id="device_name" name="device_name" type="text" maxlength="120" value="{{ old('device_name') }}" placeholder="Ex.: blog-marce, painel-privado, crm-comercial" class="form-control @error('device_name') is-invalid @enderror">@error('device_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><button type="submit" class="btn btn-primary w-100 fw-bold"><i class="fas fa-key me-1"></i> Gerar token agora</button></form></div></div>
                                    <div class="col-lg-8 mt-4 mt-lg-0"><div class="referral-mini-card h-100"><div class="d-flex align-items-center justify-content-between mb-3"><div><h5 class="fw-bold mb-1">Tokens ativos</h5><p class="text-muted mb-0">Renomeie por dispositivo, acompanhe o último uso e revogue o que não precisa mais.</p></div><span class="badge text-bg-secondary">{{ $apiTokens->count() }} ativo{{ $apiTokens->count() !== 1 ? 's' : '' }}</span></div>@forelse($apiTokens as $token)<div class="border rounded p-3 mb-3"><div class="d-flex flex-column flex-xl-row justify-content-between"><div class="mb-3 mb-xl-0 pe-xl-3"><div class="d-flex align-items-center flex-wrap"><h6 class="fw-bold mb-0 me-2">{{ $token->name }}</h6><span class="badge text-bg-primary">Token #{{ $token->id }}</span></div><p class="text-muted mb-1 mt-2">Criado em {{ optional($token->created_at)->format('d/m/Y H:i') ?: '—' }}</p><div class="small text-muted"><span class="d-inline-block me-3">Último uso: {{ optional($token->last_used_at)->format('d/m/Y H:i') ?: 'Nunca usado' }}</span><span class="d-inline-block">IP: {{ $token->last_used_ip ?: 'Indisponível' }}</span></div></div><div class="w-100 w-xl-auto" style="min-width: 18rem;"><form action="{{ route('admin.referrals.tokens.update', $token->id) }}" method="POST" class="d-flex flex-column flex-sm-row mb-2">@csrf @method('PUT')<input type="text" name="device_name" maxlength="120" value="{{ old('device_name', $token->name) }}" class="form-control me-sm-2 mb-2 mb-sm-0"><button type="submit" class="btn btn-outline-primary fw-bold"><i class="fas fa-pen me-1"></i> Renomear</button></form><form action="{{ route('admin.referrals.tokens.destroy', $token->id) }}" method="POST" data-confirm-title="Revogar token" data-confirm-text="Revogar este token agora?" data-confirm-button="Sim, revogar">@csrf @method('DELETE')<button type="submit" class="btn btn-danger w-100 fw-bold"><i class="fas fa-ban me-1"></i> Revogar token</button></form></div></div></div>@empty<div class="alert alert-light border mb-0">Nenhum token ativo ainda. Gere o primeiro token para usar a API em blog, página privada ou painel próprio.</div>@endforelse</div></div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'sandbox' ? 'show active' : '' }}" id="admin-referrals-sandbox" role="tabpanel">
                    @include('admin.referrals.partials.sandbox-requests', [
                        'sandboxRequestsAvailable' => $sandboxRequestsAvailable ?? false,
                        'sandboxRequests' => $sandboxRequests ?? collect(),
                    ])
                </div>

                <div class="tab-pane fade {{ $activeTab === 'tracking' ? 'show active' : '' }}" id="admin-referrals-tracking" role="tabpanel">
                    <div class="card card-warning card-outline shadow-sm mb-4">
                        <div class="card-header border-0">
                            <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between">
                                <div class="mb-3 mb-xl-0"><h3 class="card-title fw-bold"><i class="fas fa-chart-line me-2"></i>Rastreio global de indicações</h3><p class="text-muted mb-0 mt-2">{{ $selectedScopeLabel }} com cliques, origens, compartilhamentos, checkouts e compras.</p></div>
                                <div class="d-flex flex-wrap" style="gap:.75rem;">@if($selectedReferrer)<a href="{{ route('admin.referrals.index', ['tab' => 'tracking', 'tracking_tab' => $activeTrackingTab]) }}" class="btn btn-outline-secondary fw-bold"><i class="fas fa-filter-circle-xmark me-1"></i> Limpar filtro</a>@endif<a href="{{ route('admin.referrals.export', ['referrer' => $selectedReferrer?->id]) }}" class="btn btn-primary fw-bold"><i class="fas fa-file-csv me-1"></i> Exportar CSV</a></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert {{ $trackingStatusTone === 'success' ? 'alert-success' : 'alert-warning' }} mb-4">{{ $trackingStatusMessage }}</div>
                            @if($selectedReferrer)
                                <div class="referral-mini-card mb-4"><div class="d-flex align-items-center"><img src="{{ $selectedReferrer->photo ? asset($selectedReferrer->photo) : asset('img/user.png') }}" alt="{{ $selectedReferrer->name }}" class="referral-avatar-lg me-3" loading="lazy" decoding="async"><div><div class="fw-bold">{{ $selectedReferrer->name }}</div><div class="text-muted">{{ $selectedReferrer->email }}</div><span class="badge text-bg-info mt-2">{{ $selectedReferrer->referral_code ?: 'Sem código' }}</span></div></div></div>
                            @endif
                            <div class="row" style="row-gap: 1.25rem;">
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="referral-tracking-card h-100">
                                        <div class="referral-tracking-icon bg-primary shadow-primary-sm"><i class="fas fa-mouse-pointer"></i></div>
                                        <div class="referral-tracking-label">Cliques</div>
                                        <div class="referral-tracking-value">{{ number_format($trackingSummary['clicks'] ?? 0) }}</div>
                                        <div class="referral-tracking-sub">{{ number_format($trackingSummary['visits'] ?? 0) }} visitas únicas</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="referral-tracking-card h-100">
                                        <div class="referral-tracking-icon bg-success shadow-success-sm"><i class="fas fa-user-check"></i></div>
                                        <div class="referral-tracking-label">Cadastros</div>
                                        <div class="referral-tracking-value">{{ number_format($trackingSummary['registrations'] ?? 0) }}</div>
                                        <div class="referral-tracking-sub">{{ number_format($trackingSummary['registration_conversion'] ?? 0) }}% conversão</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="referral-tracking-card h-100">
                                        <div class="referral-tracking-icon bg-warning shadow-warning-sm"><i class="fas fa-cash-register text-white"></i></div>
                                        <div class="referral-tracking-label">Checkouts</div>
                                        <div class="referral-tracking-value">{{ number_format($trackingSummary['checkout_starts'] ?? 0) }}</div>
                                        <div class="referral-tracking-sub">Inícios atribuídos</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="referral-tracking-card h-100">
                                        <div class="referral-tracking-icon bg-purple shadow-purple-sm"><i class="fas fa-shopping-bag"></i></div>
                                        <div class="referral-tracking-label">Compras</div>
                                        <div class="referral-tracking-value">{{ number_format($trackingSummary['purchases'] ?? 0) }}</div>
                                        <div class="referral-tracking-sub">{{ number_format($trackingSummary['purchase_conversion'] ?? 0) }}% conversão</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="referral-tracking-card h-100">
                                        <div class="referral-tracking-icon bg-info shadow-info-sm"><i class="fas fa-share-alt"></i></div>
                                        <div class="referral-tracking-label">Compart.</div>
                                        <div class="referral-tracking-value">{{ number_format(($trackingSummary['shares'] ?? 0) + ($trackingSummary['reshares'] ?? 0) + ($trackingSummary['copies'] ?? 0)) }}</div>
                                        <div class="referral-tracking-sub">{{ number_format($trackingSummary['copies'] ?? 0) }} cópias e envios</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="referral-tracking-card h-100">
                                        <div class="referral-tracking-icon bg-dark shadow-dark-sm"><i class="fas fa-wallet"></i></div>
                                        <div class="referral-tracking-label">Receita</div>
                                        <div class="referral-tracking-value" style="font-size: 1.25rem;">R$ {{ number_format((float) ($trackingSummary['revenue'] ?? 0), 2, ',', '.') }}</div>
                                        <div class="referral-tracking-sub">Atualizado recentemente</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-pills referral-legacy-subtabs mb-4" id="admin-referrals-tracking-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTrackingTab === 'analysis' ? 'active' : '' }}" id="admin-referrals-tracking-analysis-tab" data-bs-toggle="pill" href="#admin-referrals-tracking-analysis" role="tab" data-tracking-tab-name="analysis">
                                <i class="fas fa-ranking-star me-1"></i> Ranking e canais
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTrackingTab === 'events' ? 'active' : '' }}" id="admin-referrals-tracking-events-tab" data-bs-toggle="pill" href="#admin-referrals-tracking-events" role="tab" data-tracking-tab-name="events">
                                <i class="fas fa-list-ul me-1"></i> Log detalhado
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade {{ $activeTrackingTab === 'analysis' ? 'show active' : '' }}" id="admin-referrals-tracking-analysis" role="tabpanel">
                    <div class="row">
                        <div class="col-xl-8">
                            <div class="card card-primary card-outline shadow-sm mb-4">
                                <div class="card-header border-0"><h3 class="card-title fw-bold"><i class="fas fa-ranking-star me-2"></i>Ranking de afiliados</h3></div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="admin-referrals-ranking-table" class="table table-bordered table-hover table-striped w-100">
                                            <thead><tr><th>Afiliado</th><th class="text-end">Cliques</th><th class="text-end">Visitas</th><th class="text-end">Cadastros</th><th class="text-end">Compras</th><th class="text-end">Receita</th><th class="text-end">Compart.</th><th class="text-end">Pontos</th><th>Última atividade</th><th class="text-end">Ações</th></tr></thead>
                                            <tbody>
                                                @foreach($affiliateLeaderboard as $affiliate)
                                                    <tr>
                                                        <td><div class="d-flex align-items-center"><img src="{{ $affiliate->photo ? asset($affiliate->photo) : asset('img/user.png') }}" alt="{{ $affiliate->name }}" class="referral-avatar me-3" loading="lazy" decoding="async"><div><div class="fw-bold">{{ $affiliate->name }}</div><small class="text-muted d-block">{{ $affiliate->email }}</small><span class="badge text-bg-info mt-1">{{ $affiliate->referral_code ?: 'Sem código' }}</span></div></div></td>
                                                        <td data-order="{{ $affiliate->clicks }}" class="text-end fw-bold">{{ number_format($affiliate->clicks) }}</td>
                                                        <td data-order="{{ $affiliate->visits }}" class="text-end fw-bold">{{ number_format($affiliate->visits) }}</td>
                                                        <td data-order="{{ $affiliate->registrations }}" class="text-end text-success fw-bold">{{ number_format($affiliate->registrations) }}</td>
                                                        <td data-order="{{ $affiliate->purchases }}" class="text-end text-purple fw-bold">{{ number_format($affiliate->purchases) }}</td>
                                                        <td data-order="{{ $affiliate->revenue }}" class="text-end fw-bold">R$ {{ number_format($affiliate->revenue, 2, ',', '.') }}</td>
                                                        <td data-order="{{ $affiliate->shares_total }}" class="text-end fw-bold">{{ number_format($affiliate->shares_total) }}</td>
                                                        <td data-order="{{ $affiliate->referral_points }}" class="text-end text-warning fw-bold">{{ number_format($affiliate->referral_points) }}</td>
                                                        <td data-order="{{ $affiliate->last_activity_timestamp ?? 0 }}"><div class="fw-bold">{{ $affiliate->last_activity_label }}</div><small class="text-muted">{{ $affiliate->last_activity_human }}</small></td>
                                                        <td class="text-end referral-table-actions"><a href="{{ route('admin.referrals.index', ['referrer' => $affiliate->id, 'tab' => 'tracking', 'tracking_tab' => 'analysis']) }}" class="btn btn-sm btn-outline-primary fw-bold"><i class="fas fa-chart-line me-1"></i> Ver rastreio</a></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card card-info card-outline shadow-sm mb-4">
                                <div class="card-header border-0"><h3 class="card-title fw-bold"><i class="fas fa-globe me-2"></i>Funil por canal</h3></div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="admin-referrals-channels-table" class="table table-bordered table-hover table-striped w-100">
                                            <thead><tr><th>Canal</th><th class="text-end">Visitas</th><th class="text-end">Cad.</th><th class="text-end">Compras</th></tr></thead>
                                            <tbody>
                                                @foreach($channelFunnels as $funnel)
                                                    <tr>
                                                        <td><div class="fw-bold">{{ $funnel->channel }}</div><small class="text-muted">{{ number_format($funnel->registration_conversion) }}% cad. · {{ number_format($funnel->purchase_conversion) }}% compra</small></td>
                                                        <td data-order="{{ $funnel->visits }}" class="text-end">{{ number_format($funnel->visits) }}</td>
                                                        <td data-order="{{ $funnel->registrations }}" class="text-end text-success">{{ number_format($funnel->registrations) }}</td>
                                                        <td data-order="{{ $funnel->purchases }}" class="text-end text-purple">{{ number_format($funnel->purchases) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-dark card-outline shadow-sm mb-0 referral-activity-feed">
                                <div class="card-header border-0"><h3 class="card-title fw-bold"><i class="fas fa-history me-2"></i>Últimas visitas atribuídas</h3></div>
                                <div class="card-body p-0">
                                    @if(empty($trackedVisitsFeed))
                                        <div class="referral-empty-state m-3">Nenhuma visita atribuída ainda.</div>
                                    @else
                                        <ul class="list-group list-group-flush">
                                            @foreach($trackedVisitsFeed as $visit)
                                                <li class="list-group-item"><div class="d-flex justify-content-between align-items-start"><div><div class="fw-bold">{{ $visit['source_label'] }}</div><small class="text-muted d-block">{{ $visit['landing_page_path'] }}</small><small class="text-muted d-block">{{ $visit['clicks_count'] }} clique(s) · {{ $visit['pageviews_count'] }} visualização(ões)</small>@if(!empty($visit['registered_user_name']))<span class="badge text-bg-success mt-2">{{ $visit['registered_user_name'] }}</span>@endif</div><small class="text-muted text-end">{{ $visit['first_visited_at'] }}</small></div></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    </div>
                    <div class="tab-pane fade {{ $activeTrackingTab === 'events' ? 'show active' : '' }}" id="admin-referrals-tracking-events" role="tabpanel">
                    <div class="card card-dark card-outline shadow-sm mb-0">
                        <div class="card-header border-0"><h3 class="card-title fw-bold"><i class="fas fa-history me-2"></i>Log detalhado de cliques, visitas e compartilhamentos</h3></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="admin-referrals-events-table" class="table table-bordered table-hover table-striped w-100">
                                    <thead><tr><th>Quando</th><th>Afiliado</th><th>Ação</th><th>Canal / origem</th><th>Landing / URL</th><th>Dispositivo</th><th>Localização</th><th>Resultado</th></tr></thead>
                                    <tbody>
                                        @foreach($detailedEvents as $event)
                                            @php
                                                $badgeClass = match ($event->event_type) {
                                                    'purchase' => 'badge-purple',
                                                    'register' => 'text-bg-success',
                                                    'checkout_started' => 'text-bg-warning',
                                                    'share', 'reshare' => 'text-bg-info',
                                                    'copy' => 'text-bg-secondary',
                                                    'visit', 'click' => 'text-bg-primary',
                                                    default => 'text-bg-light',
                                                };
                                            @endphp
                                            <tr>
                                                <td data-order="{{ $event->occurred_at_timestamp ?? 0 }}"><div class="fw-bold">{{ $event->occurred_at_label }}</div><small class="text-muted">{{ $event->occurred_at_human }}</small></td>
                                                <td><div class="fw-bold">{{ $event->referrer_name }}</div><small class="text-muted">{{ $event->referral_code }}</small></td>
                                                <td><span class="badge {{ $badgeClass }}">{{ $event->event_label }}</span></td>
                                                <td><div class="fw-bold">{{ $event->channel_label }}</div><small class="text-muted referral-source-url">{{ $event->source_url }}</small></td>
                                                <td><div class="fw-bold">{{ $event->landing_page_path }}</div><small class="text-muted referral-break-anywhere">{{ $event->tracked_page_url }}</small></td>
                                                <td><div>{{ $event->device_label }}</div><small class="text-muted">{{ $event->browser_label }} · {{ $event->os_label }}</small></td>
                                                <td>{{ $event->location_label }}</td>
                                                <td><div class="fw-bold">{{ $event->result_user_label }}</div><small class="text-muted">{{ $event->result_value_label }}</small></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script>
        function trackAdminReferralShare(action, channel, targetUrl) {
            fetch(@json(route('admin.referrals.track')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    channel: channel,
                    target_url: targetUrl,
                    context: 'admin_legacy_referral',
                }),
            }).catch(() => {});
        }

        async function copyAdminReferralMaterial(button, text, trackChannel, targetUrl) {
            try {
                await navigator.clipboard.writeText(text);
            } catch (error) {
                const fallbackInput = document.createElement('textarea');
                fallbackInput.value = text;
                document.body.appendChild(fallbackInput);
                fallbackInput.select();
                document.execCommand('copy');
                document.body.removeChild(fallbackInput);
            }

            if (trackChannel) {
                trackAdminReferralShare('copy', trackChannel, targetUrl);
            }

            if (typeof toastr !== 'undefined') {
                toastr.success(trackChannel === 'code' ? 'Código copiado com sucesso!' : 'Link copiado para a área de transferência!', 'Afiliados');
            }

            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check me-1"></i> Copiado';
            setTimeout(() => { button.innerHTML = originalHtml; }, 1800);
        }

        $(function () {
            const dtLanguage = {
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Nenhum registro disponível',
                infoFiltered: '(filtrado de _MAX_ registros)',
                zeroRecords: 'Nenhum resultado encontrado',
                emptyTable: 'Nenhum registro disponível',
                paginate: { first: 'Primeiro', last: 'Último', next: 'Próximo', previous: 'Anterior' },
            };

            const dataTables = [];
            const configs = [
                { selector: '#admin-referrals-members-table', order: [[1, 'desc']] },
                { selector: '#admin-referrals-ranking-table', order: [[5, 'desc']], columnDefs: [{ targets: 9, orderable: false, searchable: false }] },
                { selector: '#admin-referrals-channels-table', order: [[1, 'desc']] },
                { selector: '#admin-referrals-events-table', order: [[0, 'desc']] },
                { selector: '#admin-referrals-sandbox-table', order: [[4, 'desc']], columnDefs: [{ targets: 5, orderable: false, searchable: false }] },
            ];

            configs.forEach((config) => {
                if (!$(config.selector).length) {
                    return;
                }

                dataTables.push($(config.selector).DataTable({
                    responsive: true,
                    autoWidth: false,
                    pageLength: 10,
                    lengthChange: true,
                    language: dtLanguage,
                    order: config.order || [],
                    columnDefs: config.columnDefs || [],
                }));
            });

            function syncAdminReferralUrl() {
                const url = new URL(window.location.href);
                const activeMainTab = $('#admin-referrals-tabs a.nav-link.active').data('tab-name');
                const activeTrackingTab = $('#admin-referrals-tracking-tabs a.nav-link.active').data('tracking-tab-name');

                if (activeMainTab) {
                    url.searchParams.set('tab', activeMainTab);
                }

                if (activeMainTab === 'tracking' && activeTrackingTab) {
                    url.searchParams.set('tracking_tab', activeTrackingTab);
                } else {
                    url.searchParams.delete('tracking_tab');
                }

                window.history.replaceState({}, '', url.toString());
            }

            $('#admin-referrals-tabs a[data-bs-toggle="pill"]').on('shown.bs.tab', function () {
                syncAdminReferralUrl();
                dataTables.forEach((table) => {
                    table.columns.adjust().responsive.recalc();
                });
            });

            $('#admin-referrals-tracking-tabs a[data-bs-toggle="pill"]').on('shown.bs.tab', function () {
                syncAdminReferralUrl();
                dataTables.forEach((table) => {
                    table.columns.adjust().responsive.recalc();
                });
            });

            $('[data-referral-tab-target]').on('click', function () {
                const target = $(this).data('referral-tab-target');
                $('#admin-referrals-' + target + '-tab').tab('show');
            });

            syncAdminReferralUrl();
        });
    </script>
@endpush
