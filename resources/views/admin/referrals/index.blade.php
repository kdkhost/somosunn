@extends('admin.layouts.app')

@section('page_title', 'Afiliados e Indicações')
@section('breadcrumb')<li class="breadcrumb-item active">Afiliados e Indicações</li>@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        .referral-legacy-tabs .nav-link { font-weight: 600; color: #495057; }
        .referral-legacy-tabs .nav-link.active { color: #007bff; }
        .referral-legacy-subtabs { gap: .65rem; }
        .referral-legacy-subtabs .nav-link { border-radius: 999px; font-weight: 700; color: #495057; background: #f4f6f9; }
        .referral-legacy-subtabs .nav-link.active { background: #007bff; color: #fff; box-shadow: 0 .35rem .85rem rgba(0, 123, 255, .18); }
        .referral-avatar, .referral-avatar-lg { border-radius: 999px; object-fit: cover; flex-shrink: 0; background: #f4f6f9; }
        .referral-avatar { width: 42px; height: 42px; }
        .referral-avatar-lg { width: 56px; height: 56px; }
        .referral-hero-card { border-radius: 1rem; }
        .referral-stat-card { border-radius: .9rem; min-height: 100%; }
        .referral-stat-card .info-box-icon { border-radius: .85rem; }
        .referral-mini-card { border: 1px solid #e9ecef; border-radius: .9rem; padding: 1rem; background: #fff; height: 100%; }
        .referral-steps-card .step-badge { width: 2rem; height: 2rem; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; margin-bottom: .75rem; }
        .referral-activity-feed .list-group-item { border-left: 0; border-right: 0; padding-left: 0; padding-right: 0; }
        .referral-activity-feed .list-group-item:first-child { border-top: 0; }
        .referral-activity-feed .list-group-item:last-child { border-bottom: 0; }
        .referral-source-url, .referral-break-anywhere { word-break: break-word; }
        .referral-empty-state { min-height: 180px; display: flex; align-items: center; justify-content: center; text-align: center; color: #6c757d; background: #f8f9fa; border: 1px dashed #ced4da; border-radius: .9rem; padding: 2rem; }
        .referral-table-actions { white-space: nowrap; }
        .dataTables_wrapper .row:first-child, .dataTables_wrapper .row:last-child { margin-left: 0; margin-right: 0; }
        .dataTables_wrapper .dataTables_filter label, .dataTables_wrapper .dataTables_length label { font-weight: 600; }
        .dataTables_wrapper .dataTables_filter input, .dataTables_wrapper .dataTables_length select { margin-left: .35rem; }
        
        /* Novo Design de Cartão de Rastreio (Flexível e Vertical) */
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
            <button type="button" class="close" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="callout callout-info">
        <h5 class="mb-2"><i class="fas fa-bullhorn mr-2"></i>Afiliados e indicações no AdminLTE 3.2</h5>
        <p class="mb-0">O superadmin acompanha o link pessoal, os indicados, os tokens da API, os tickets de sandbox e o rastreio consolidado sem misturar tudo na mesma rolagem.</p>
    </div>

    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs referral-legacy-tabs" id="admin-referrals-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link {{ $activeTab === 'programa' ? 'active' : '' }}" id="admin-referrals-programa-tab" data-toggle="pill" href="#admin-referrals-programa" role="tab" data-tab-name="programa"><i class="fas fa-link mr-1"></i> Programa e link pessoal</a></li>
                <li class="nav-item"><a class="nav-link {{ $activeTab === 'indicados' ? 'active' : '' }}" id="admin-referrals-indicados-tab" data-toggle="pill" href="#admin-referrals-indicados" role="tab" data-tab-name="indicados"><i class="fas fa-users mr-1"></i> Indicados</a></li>
                <li class="nav-item"><a class="nav-link {{ $activeTab === 'api' ? 'active' : '' }}" id="admin-referrals-api-tab" data-toggle="pill" href="#admin-referrals-api" role="tab" data-tab-name="api"><i class="fas fa-key mr-1"></i> API pessoal</a></li>
                <li class="nav-item"><a class="nav-link {{ $activeTab === 'sandbox' ? 'active' : '' }}" id="admin-referrals-sandbox-tab" data-toggle="pill" href="#admin-referrals-sandbox" role="tab" data-tab-name="sandbox"><i class="fas fa-vial mr-1"></i> Sandbox</a></li>
                <li class="nav-item"><a class="nav-link {{ $activeTab === 'tracking' ? 'active' : '' }}" id="admin-referrals-tracking-tab" data-toggle="pill" href="#admin-referrals-tracking" role="tab" data-tab-name="tracking"><i class="fas fa-chart-line mr-1"></i> Rastreio global</a></li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade {{ $activeTab === 'programa' ? 'show active' : '' }}" id="admin-referrals-programa" role="tabpanel">
                    <div class="row">
                        <div class="col-xl-7">
                            <div class="card card-primary card-outline referral-hero-card shadow-sm">
                                <div class="card-header border-0"><h3 class="card-title font-weight-bold"><i class="fas fa-link mr-2"></i>Seu link de indicação</h3></div>
                                <div class="card-body">
                                    <p class="text-muted mb-4">Compartilhe este link. O sistema registra automaticamente quem entrou pelo seu convite.</p>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-link"></i></span></div>
                                        <input id="adminReferralLinkInput" type="text" readonly class="form-control" value="{{ $referralLink }}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary font-weight-bold" onclick="copyAdminReferralMaterial(this, document.getElementById('adminReferralLinkInput').value, 'legacy-admin-link', @json($referralLink))"><i class="fas fa-copy mr-1"></i> Copiar</button>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap mb-4" style="gap:.75rem;">
                                        <a href="https://wa.me/?text={{ urlencode('Ei! Faça parte da maior comunidade de empreendedores e networking do Brasil. Use meu link: ' . $referralLink) }}" target="_blank" rel="noopener noreferrer" onclick="trackAdminReferralShare('share', 'whatsapp', @json($referralLink))" class="btn btn-success font-weight-bold"><i class="fab fa-whatsapp mr-1"></i> WhatsApp</a>
                                        <a href="https://t.me/share/url?url={{ urlencode($referralLink) }}&text={{ urlencode('Entre na plataforma com meu convite e comece a fazer networking!') }}" target="_blank" rel="noopener noreferrer" onclick="trackAdminReferralShare('share', 'telegram', @json($referralLink))" class="btn btn-info font-weight-bold text-white"><i class="fab fa-telegram-plane mr-1"></i> Telegram</a>
                                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($referralLink) }}" target="_blank" rel="noopener noreferrer" onclick="trackAdminReferralShare('share', 'linkedin', @json($referralLink))" class="btn btn-primary font-weight-bold"><i class="fab fa-linkedin mr-1"></i> LinkedIn</a>
                                        <a href="mailto:?subject={{ urlencode('Convite para a comunidade UNN') }}&body={{ urlencode('Olá! Quero te convidar para a maior plataforma de networking para empreendedores. Acesse: ' . $referralLink) }}" onclick="trackAdminReferralShare('share', 'email', @json($referralLink))" class="btn btn-secondary font-weight-bold"><i class="fas fa-envelope mr-1"></i> E-mail</a>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3 mb-md-0"><div class="referral-mini-card h-100"><div class="text-muted text-uppercase font-weight-bold mb-2" style="font-size: .75rem;">Código único</div><h4 class="mb-0 font-weight-bold">{{ $user->referral_code }}</h4></div></div>
                                        <div class="col-md-8"><div class="alert alert-light border mb-0 h-100 d-flex align-items-center"><div><i class="fas fa-info-circle text-primary mr-2"></i>Pontos são creditados somente após o indicado assinar um plano pago.</div></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-5 mt-4 mt-xl-0">
                            <div class="row h-100">
                                <div class="col-sm-6 mb-3"><div class="info-box bg-primary referral-stat-card shadow-sm mb-0"><span class="info-box-icon"><i class="fas fa-user-plus"></i></span><div class="info-box-content"><span class="info-box-text">Total indicados</span><span class="info-box-number">{{ number_format($totalReferred) }}</span></div></div></div>
                                <div class="col-sm-6 mb-3"><div class="info-box bg-success referral-stat-card shadow-sm mb-0"><span class="info-box-icon"><i class="fas fa-check-circle"></i></span><div class="info-box-content"><span class="info-box-text">Convertidos</span><span class="info-box-number">{{ number_format($convertedCount) }}</span><div class="progress"><div class="progress-bar" style="width: {{ $conversionRate }}%"></div></div><span class="progress-description">{{ $conversionRate }}% de conversão</span></div></div></div>
                                <div class="col-sm-6 mb-3 mb-sm-0"><div class="info-box bg-warning referral-stat-card shadow-sm mb-0"><span class="info-box-icon"><i class="fas fa-coins"></i></span><div class="info-box-content"><span class="info-box-text">Pontos ganhos</span><span class="info-box-number">{{ number_format($totalReferralPoints) }}</span></div></div></div>
                                <div class="col-sm-6"><div class="info-box bg-light border referral-stat-card shadow-sm mb-0"><span class="info-box-icon bg-light"><i class="fas fa-gift text-success"></i></span><div class="info-box-content"><span class="info-box-text">Por indicação</span><span class="info-box-number">{{ $pointsPerReferral ? '+' . number_format($pointsPerReferral) . ' pts' : 'Não configurado' }}</span>@if($pendingCount > 0 && $pointsPerReferral > 0)<span class="progress-description">Potencial pendente: +{{ number_format($potentialPoints) }} pts</span>@endif</div></div></div>
                            </div>
                        </div>
                    </div>                    <div class="row mt-4">
                        <div class="col-lg-7">
                            <div class="card card-secondary card-outline shadow-sm mb-0">
                                <div class="card-header border-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                    <div>
                                        <h3 class="card-title font-weight-bold"><i class="fas fa-users mr-2"></i>Pessoas que você indicou</h3>
                                        <p class="text-muted mb-0 mt-2">Prévia dos membros mais recentes. A listagem completa fica na guia <strong>Indicados</strong>.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary font-weight-bold mt-3 mt-md-0" data-referral-tab-target="indicados"><i class="fas fa-table mr-1"></i> Ver listagem completa</button>
                                </div>
                                <div class="card-body p-0">
                                    @if($referralsPreview->isEmpty())
                                        <div class="referral-empty-state m-3">Nenhuma indicação registrada ainda.</div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped mb-0">
                                                <thead><tr><th>Membro</th><th>Cadastro</th><th>Plano</th><th class="text-right">Pontos</th></tr></thead>
                                                <tbody>
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
                                                                    $planStatusClass = 'badge-purple';
                                                                } elseif (\Carbon\Carbon::parse($referred->plan_expires_at)->isFuture()) {
                                                                    $planStatusLabel = 'Assinante ativo';
                                                                    $planStatusClass = 'badge-success';
                                                                } else {
                                                                    $planStatusLabel = 'Plano expirado';
                                                                    $planStatusClass = 'badge-warning';
                                                                }
                                                                $planName = $plansMap[$referred->plan_id] ?? 'Plano';
                                                            } else {
                                                                $planStatusLabel = 'Sem plano';
                                                                $planStatusClass = 'badge-secondary';
                                                                $planName = null;
                                                            }
                                                        @endphp
                                                        <tr>
                                                            <td><div class="d-flex align-items-center"><img src="{{ $referred->photo ? asset($referred->photo) : asset('img/user.png') }}" alt="{{ $referred->name }}" class="referral-avatar mr-3"><div><div class="font-weight-bold">{{ $referred->name }}</div><small class="text-muted">{{ $referred->email }}</small></div></div></td>
                                                            <td>{{ optional($referred->created_at)->format('d/m/Y H:i') ?: '—' }}</td>
                                                            <td>@if($planName)<div class="font-weight-bold">{{ $planName }}</div>@endif<span class="badge {{ $planStatusClass }}">{{ $planStatusLabel }}</span></td>
                                                            <td class="text-right font-weight-bold {{ $pointsFromThisUser > 0 ? 'text-success' : 'text-muted' }}">{{ $pointsFromThisUser > 0 ? '+' . number_format($pointsFromThisUser) : '0' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 mt-4 mt-lg-0">
                            <div class="card card-dark card-outline referral-steps-card shadow-sm mb-0 h-100">
                                <div class="card-header border-0"><h3 class="card-title font-weight-bold"><i class="fas fa-route mr-2"></i>Como funciona</h3></div>
                                <div class="card-body">
                                    <div class="referral-mini-card mb-3"><div class="step-badge bg-primary text-white">1</div><h5 class="font-weight-bold">Copie seu link</h5><p class="text-muted mb-0">Cada membro tem um link e um código únicos de indicação.</p></div>
                                    <div class="referral-mini-card mb-3"><div class="step-badge bg-purple text-white">2</div><h5 class="font-weight-bold">Compartilhe</h5><p class="text-muted mb-0">Envie para contatos, comunidades, site pessoal ou blog.</p></div>
                                    <div class="referral-mini-card mb-3"><div class="step-badge bg-info text-white">3</div><h5 class="font-weight-bold">O indicado assina</h5><p class="text-muted mb-0">O sistema atribui o cadastro, checkout e compra ao seu link.</p></div>
                                    <div class="referral-mini-card mb-0"><div class="step-badge bg-warning text-white">4</div><h5 class="font-weight-bold">Você pontua</h5><p class="text-muted mb-0">Os pontos entram após a confirmação do pagamento do plano.</p></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'indicados' ? 'show active' : '' }}" id="admin-referrals-indicados" role="tabpanel">
                    <div class="card card-secondary card-outline shadow-sm mb-0">
                        <div class="card-header border-0"><h3 class="card-title font-weight-bold"><i class="fas fa-users mr-2"></i>Todos os indicados do seu link</h3></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="admin-referrals-members-table" class="table table-bordered table-hover table-striped w-100">
                                    <thead><tr><th>Membro indicado</th><th>Cadastro</th><th>Plano / Status</th><th class="text-right">Pontos</th></tr></thead>
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
                                                        $planStatusClass = 'badge-success';
                                                    } else {
                                                        $planStatusLabel = 'Plano expirado';
                                                        $planStatusClass = 'badge-warning';
                                                    }
                                                    $planName = $plansMap[$referred->plan_id] ?? 'Plano';
                                                } else {
                                                    $planStatusLabel = 'Sem plano';
                                                    $planStatusClass = 'badge-secondary';
                                                    $planName = null;
                                                }
                                            @endphp
                                            <tr>
                                                <td><div class="d-flex align-items-center"><img src="{{ $referred->photo ? asset($referred->photo) : asset('img/user.png') }}" alt="{{ $referred->name }}" class="referral-avatar mr-3"><div><div class="font-weight-bold">{{ $referred->name }}</div><small class="text-muted d-block">{{ $referred->email }}</small></div></div></td>
                                                <td data-order="{{ optional($referred->created_at)->timestamp ?? 0 }}">{{ optional($referred->created_at)->format('d/m/Y H:i') ?: '—' }}</td>
                                                <td data-order="{{ $planStatusLabel }}">@if($planName)<div class="font-weight-bold">{{ $planName }}</div>@endif<span class="badge {{ $planStatusClass }}">{{ $planStatusLabel }}</span></td>
                                                <td data-order="{{ $pointsFromThisUser }}" class="text-right font-weight-bold {{ $pointsFromThisUser > 0 ? 'text-success' : 'text-muted' }}">{{ $pointsFromThisUser > 0 ? '+' . number_format($pointsFromThisUser) : '0' }}</td>
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
                        <div class="card-header border-0"><h3 class="card-title font-weight-bold"><i class="fas fa-key mr-2"></i>Acesso API pessoal</h3></div>
                        <div class="card-body">
                            <p class="text-muted mb-4">Gere tokens por dispositivo, copie na hora, renomeie integrações e acompanhe último uso e IP.</p>
                            @if($errors->has('api_tokens'))<div class="alert alert-danger">{{ $errors->first('api_tokens') }}</div>@endif
                            @if(!$apiTokensEnabled)
                                <div class="alert alert-warning mb-0">A tabela de tokens da API ainda não está disponível neste ambiente. Rode as migrations para liberar esta área.</div>
                            @else
                                @if($apiTokenPlainText)
                                    <div class="alert alert-primary">
                                        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between"><div class="mb-3 mb-lg-0"><h5 class="font-weight-bold mb-1">Token gerado para {{ $apiTokenDeviceName ?: 'integração' }}</h5><p class="mb-0">Este token aparece uma única vez. Se perder, gere outro e revogue o antigo.</p></div><button type="button" class="btn btn-primary font-weight-bold" onclick='copyAdminReferralMaterial(this, @json($apiTokenPlainText), "api-token", @json(url('/api/v1/affiliate/overview')))'><i class="fas fa-copy mr-1"></i> Copiar token</button></div>
                                        <pre class="mt-3 mb-0 p-3 bg-white border rounded text-break" style="white-space: pre-wrap;">{{ $apiTokenPlainText }}</pre>
                                    </div>
                                @endif
                                @if(!$apiTokenIpTrackingEnabled)<div class="alert alert-warning">O campo de IP ainda não existe neste ambiente. Depois de rodar <code>php artisan migrate</code>, esta tela também mostrará o último IP de uso.</div>@endif                                <div class="row">
                                    <div class="col-lg-4"><div class="referral-mini-card h-100"><h5 class="font-weight-bold">Gerar novo token</h5><p class="text-muted">Use um nome claro para o dispositivo, blog, CRM ou página privada que vai consumir sua API.</p><form action="{{ route('admin.referrals.tokens.store') }}" method="POST" class="mt-4">@csrf<div class="form-group"><label for="device_name">Dispositivo / integração</label><input id="device_name" name="device_name" type="text" maxlength="120" value="{{ old('device_name') }}" placeholder="Ex.: blog-marce, painel-privado, crm-comercial" class="form-control @error('device_name') is-invalid @enderror">@error('device_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><button type="submit" class="btn btn-primary btn-block font-weight-bold"><i class="fas fa-key mr-1"></i> Gerar token agora</button></form></div></div>
                                    <div class="col-lg-8 mt-4 mt-lg-0"><div class="referral-mini-card h-100"><div class="d-flex align-items-center justify-content-between mb-3"><div><h5 class="font-weight-bold mb-1">Tokens ativos</h5><p class="text-muted mb-0">Renomeie por dispositivo, acompanhe o último uso e revogue o que não precisa mais.</p></div><span class="badge badge-secondary">{{ $apiTokens->count() }} ativo{{ $apiTokens->count() !== 1 ? 's' : '' }}</span></div>@forelse($apiTokens as $token)<div class="border rounded p-3 mb-3"><div class="d-flex flex-column flex-xl-row justify-content-between"><div class="mb-3 mb-xl-0 pr-xl-3"><div class="d-flex align-items-center flex-wrap"><h6 class="font-weight-bold mb-0 mr-2">{{ $token->name }}</h6><span class="badge badge-primary">Token #{{ $token->id }}</span></div><p class="text-muted mb-1 mt-2">Criado em {{ optional($token->created_at)->format('d/m/Y H:i') ?: '—' }}</p><div class="small text-muted"><span class="d-inline-block mr-3">Último uso: {{ optional($token->last_used_at)->format('d/m/Y H:i') ?: 'Nunca usado' }}</span><span class="d-inline-block">IP: {{ $token->last_used_ip ?: 'Indisponível' }}</span></div></div><div class="w-100 w-xl-auto" style="min-width: 18rem;"><form action="{{ route('admin.referrals.tokens.update', $token->id) }}" method="POST" class="d-flex flex-column flex-sm-row mb-2">@csrf @method('PUT')<input type="text" name="device_name" maxlength="120" value="{{ old('device_name', $token->name) }}" class="form-control mr-sm-2 mb-2 mb-sm-0"><button type="submit" class="btn btn-outline-primary font-weight-bold"><i class="fas fa-pen mr-1"></i> Renomear</button></form><form action="{{ route('admin.referrals.tokens.destroy', $token->id) }}" method="POST" onsubmit="return confirm('Revogar este token agora?');">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-block font-weight-bold"><i class="fas fa-ban mr-1"></i> Revogar token</button></form></div></div></div>@empty<div class="alert alert-light border mb-0">Nenhum token ativo ainda. Gere o primeiro token para usar a API em blog, página privada ou painel próprio.</div>@endforelse</div></div>
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
                                <div class="mb-3 mb-xl-0"><h3 class="card-title font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Rastreio global de indicações</h3><p class="text-muted mb-0 mt-2">{{ $selectedScopeLabel }} com cliques, origens, compartilhamentos, checkouts e compras.</p></div>
                                <div class="d-flex flex-wrap" style="gap:.75rem;">@if($selectedReferrer)<a href="{{ route('admin.referrals.index', ['tab' => 'tracking', 'tracking_tab' => $activeTrackingTab]) }}" class="btn btn-outline-secondary font-weight-bold"><i class="fas fa-filter-circle-xmark mr-1"></i> Limpar filtro</a>@endif<a href="{{ route('admin.referrals.export', ['referrer' => $selectedReferrer?->id]) }}" class="btn btn-primary font-weight-bold"><i class="fas fa-file-csv mr-1"></i> Exportar CSV</a></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert {{ $trackingStatusTone === 'success' ? 'alert-success' : 'alert-warning' }} mb-4">{{ $trackingStatusMessage }}</div>
                            @if($selectedReferrer)
                                <div class="referral-mini-card mb-4"><div class="d-flex align-items-center"><img src="{{ $selectedReferrer->photo ? asset($selectedReferrer->photo) : asset('img/user.png') }}" alt="{{ $selectedReferrer->name }}" class="referral-avatar-lg mr-3"><div><div class="font-weight-bold">{{ $selectedReferrer->name }}</div><div class="text-muted">{{ $selectedReferrer->email }}</div><span class="badge badge-info mt-2">{{ $selectedReferrer->referral_code ?: 'Sem código' }}</span></div></div></div>
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
                            <a class="nav-link {{ $activeTrackingTab === 'analysis' ? 'active' : '' }}" id="admin-referrals-tracking-analysis-tab" data-toggle="pill" href="#admin-referrals-tracking-analysis" role="tab" data-tracking-tab-name="analysis">
                                <i class="fas fa-ranking-star mr-1"></i> Ranking e canais
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTrackingTab === 'events' ? 'active' : '' }}" id="admin-referrals-tracking-events-tab" data-toggle="pill" href="#admin-referrals-tracking-events" role="tab" data-tracking-tab-name="events">
                                <i class="fas fa-list-ul mr-1"></i> Log detalhado
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade {{ $activeTrackingTab === 'analysis' ? 'show active' : '' }}" id="admin-referrals-tracking-analysis" role="tabpanel">
                    <div class="row">
                        <div class="col-xl-8">
                            <div class="card card-primary card-outline shadow-sm mb-4">
                                <div class="card-header border-0"><h3 class="card-title font-weight-bold"><i class="fas fa-ranking-star mr-2"></i>Ranking de afiliados</h3></div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="admin-referrals-ranking-table" class="table table-bordered table-hover table-striped w-100">
                                            <thead><tr><th>Afiliado</th><th class="text-right">Cliques</th><th class="text-right">Visitas</th><th class="text-right">Cadastros</th><th class="text-right">Compras</th><th class="text-right">Receita</th><th class="text-right">Compart.</th><th class="text-right">Pontos</th><th>Última atividade</th><th class="text-right">Ações</th></tr></thead>
                                            <tbody>
                                                @foreach($affiliateLeaderboard as $affiliate)
                                                    <tr>
                                                        <td><div class="d-flex align-items-center"><img src="{{ $affiliate->photo ? asset($affiliate->photo) : asset('img/user.png') }}" alt="{{ $affiliate->name }}" class="referral-avatar mr-3"><div><div class="font-weight-bold">{{ $affiliate->name }}</div><small class="text-muted d-block">{{ $affiliate->email }}</small><span class="badge badge-info mt-1">{{ $affiliate->referral_code ?: 'Sem código' }}</span></div></div></td>
                                                        <td data-order="{{ $affiliate->clicks }}" class="text-right font-weight-bold">{{ number_format($affiliate->clicks) }}</td>
                                                        <td data-order="{{ $affiliate->visits }}" class="text-right font-weight-bold">{{ number_format($affiliate->visits) }}</td>
                                                        <td data-order="{{ $affiliate->registrations }}" class="text-right text-success font-weight-bold">{{ number_format($affiliate->registrations) }}</td>
                                                        <td data-order="{{ $affiliate->purchases }}" class="text-right text-purple font-weight-bold">{{ number_format($affiliate->purchases) }}</td>
                                                        <td data-order="{{ $affiliate->revenue }}" class="text-right font-weight-bold">R$ {{ number_format($affiliate->revenue, 2, ',', '.') }}</td>
                                                        <td data-order="{{ $affiliate->shares_total }}" class="text-right font-weight-bold">{{ number_format($affiliate->shares_total) }}</td>
                                                        <td data-order="{{ $affiliate->referral_points }}" class="text-right text-warning font-weight-bold">{{ number_format($affiliate->referral_points) }}</td>
                                                        <td data-order="{{ $affiliate->last_activity_timestamp ?? 0 }}"><div class="font-weight-bold">{{ $affiliate->last_activity_label }}</div><small class="text-muted">{{ $affiliate->last_activity_human }}</small></td>
                                                        <td class="text-right referral-table-actions"><a href="{{ route('admin.referrals.index', ['referrer' => $affiliate->id, 'tab' => 'tracking', 'tracking_tab' => 'analysis']) }}" class="btn btn-sm btn-outline-primary font-weight-bold"><i class="fas fa-chart-line mr-1"></i> Ver rastreio</a></td>
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
                                <div class="card-header border-0"><h3 class="card-title font-weight-bold"><i class="fas fa-globe mr-2"></i>Funil por canal</h3></div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="admin-referrals-channels-table" class="table table-bordered table-hover table-striped w-100">
                                            <thead><tr><th>Canal</th><th class="text-right">Visitas</th><th class="text-right">Cad.</th><th class="text-right">Compras</th></tr></thead>
                                            <tbody>
                                                @foreach($channelFunnels as $funnel)
                                                    <tr>
                                                        <td><div class="font-weight-bold">{{ $funnel->channel }}</div><small class="text-muted">{{ number_format($funnel->registration_conversion) }}% cad. · {{ number_format($funnel->purchase_conversion) }}% compra</small></td>
                                                        <td data-order="{{ $funnel->visits }}" class="text-right">{{ number_format($funnel->visits) }}</td>
                                                        <td data-order="{{ $funnel->registrations }}" class="text-right text-success">{{ number_format($funnel->registrations) }}</td>
                                                        <td data-order="{{ $funnel->purchases }}" class="text-right text-purple">{{ number_format($funnel->purchases) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-dark card-outline shadow-sm mb-0 referral-activity-feed">
                                <div class="card-header border-0"><h3 class="card-title font-weight-bold"><i class="fas fa-history mr-2"></i>Últimas visitas atribuídas</h3></div>
                                <div class="card-body p-0">
                                    @if(empty($trackedVisitsFeed))
                                        <div class="referral-empty-state m-3">Nenhuma visita atribuída ainda.</div>
                                    @else
                                        <ul class="list-group list-group-flush">
                                            @foreach($trackedVisitsFeed as $visit)
                                                <li class="list-group-item"><div class="d-flex justify-content-between align-items-start"><div><div class="font-weight-bold">{{ $visit['source_label'] }}</div><small class="text-muted d-block">{{ $visit['landing_page_path'] }}</small><small class="text-muted d-block">{{ $visit['clicks_count'] }} clique(s) · {{ $visit['pageviews_count'] }} visualização(ões)</small>@if(!empty($visit['registered_user_name']))<span class="badge badge-success mt-2">{{ $visit['registered_user_name'] }}</span>@endif</div><small class="text-muted text-right">{{ $visit['first_visited_at'] }}</small></div></li>
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
                        <div class="card-header border-0"><h3 class="card-title font-weight-bold"><i class="fas fa-history mr-2"></i>Log detalhado de cliques, visitas e compartilhamentos</h3></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="admin-referrals-events-table" class="table table-bordered table-hover table-striped w-100">
                                    <thead><tr><th>Quando</th><th>Afiliado</th><th>Ação</th><th>Canal / origem</th><th>Landing / URL</th><th>Dispositivo</th><th>Localização</th><th>Resultado</th></tr></thead>
                                    <tbody>
                                        @foreach($detailedEvents as $event)
                                            @php
                                                $badgeClass = match ($event->event_type) {
                                                    'purchase' => 'badge-purple',
                                                    'register' => 'badge-success',
                                                    'checkout_started' => 'badge-warning',
                                                    'share', 'reshare' => 'badge-info',
                                                    'copy' => 'badge-secondary',
                                                    'visit', 'click' => 'badge-primary',
                                                    default => 'badge-light',
                                                };
                                            @endphp
                                            <tr>
                                                <td data-order="{{ $event->occurred_at_timestamp ?? 0 }}"><div class="font-weight-bold">{{ $event->occurred_at_label }}</div><small class="text-muted">{{ $event->occurred_at_human }}</small></td>
                                                <td><div class="font-weight-bold">{{ $event->referrer_name }}</div><small class="text-muted">{{ $event->referral_code }}</small></td>
                                                <td><span class="badge {{ $badgeClass }}">{{ $event->event_label }}</span></td>
                                                <td><div class="font-weight-bold">{{ $event->channel_label }}</div><small class="text-muted referral-source-url">{{ $event->source_url }}</small></td>
                                                <td><div class="font-weight-bold">{{ $event->landing_page_path }}</div><small class="text-muted referral-break-anywhere">{{ $event->tracked_page_url }}</small></td>
                                                <td><div>{{ $event->device_label }}</div><small class="text-muted">{{ $event->browser_label }} · {{ $event->os_label }}</small></td>
                                                <td>{{ $event->location_label }}</td>
                                                <td><div class="font-weight-bold">{{ $event->result_user_label }}</div><small class="text-muted">{{ $event->result_value_label }}</small></td>
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

            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check mr-1"></i> Copiado';
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

            $('#admin-referrals-tabs a[data-toggle="pill"]').on('shown.bs.tab', function () {
                syncAdminReferralUrl();
                dataTables.forEach((table) => {
                    table.columns.adjust().responsive.recalc();
                });
            });

            $('#admin-referrals-tracking-tabs a[data-toggle="pill"]').on('shown.bs.tab', function () {
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
