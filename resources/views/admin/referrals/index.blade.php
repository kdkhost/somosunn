@extends('admin.layouts.app')

@section('page_title', 'Afiliados e Indicações')
@section('breadcrumb')<li class="breadcrumb-item active">Afiliados e Indicações</li>@endsection

@section('content')
    @php
        $pointsRule = \App\Models\PointsRule::where('key', 'referral')->where('active', true)->first();
        $pointsPerReferral = $pointsRule?->points ?? 0;
        $conversionRate = $totalReferred > 0 ? round(($convertedCount / $totalReferred) * 100) : 0;
        $potentialPoints = $pendingCount * $pointsPerReferral;
        $selectedScopeLabel = $selectedReferrer?->name ? 'Visão filtrada do afiliado' : 'Visão global da plataforma';
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="callout callout-info">
                <h5 class="mb-2"><i class="fas fa-bullhorn mr-2"></i>Programa de Afiliados no Admin legado</h5>
                <p class="mb-0">
                    Aqui o superadmin acompanha seu link pessoal, gerencia tokens da API e consulta o rastreio consolidado de toda a plataforma no AdminLTE 3.2.
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-link mr-2"></i>Seu link de indicação</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Compartilhe este link. O sistema registra automaticamente quem entrou pelo seu convite.</p>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-link"></i></span>
                        </div>
                        <input id="adminReferralLinkInput" type="text" readonly class="form-control" value="{{ $referralLink }}">
                        <div class="input-group-append">
                            <button type="button"
                                class="btn btn-primary font-weight-bold"
                                onclick="copyAdminReferralMaterial(this, document.getElementById('adminReferralLinkInput').value, 'legacy-admin-link', '{{ $referralLink }}')">
                                <i class="fas fa-copy mr-1"></i> Copiar
                            </button>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap mb-4" style="gap:.75rem;">
                        <a href="https://wa.me/?text={{ urlencode('Ei! Faça parte da maior comunidade de empreendedores e networking do Brasil. Use meu link: ' . $referralLink) }}"
                            target="_blank" rel="noopener noreferrer"
                            onclick="trackAdminReferralShare('share', 'whatsapp', '{{ $referralLink }}')"
                            class="btn btn-success font-weight-bold">
                            <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                        </a>
                        <a href="https://t.me/share/url?url={{ urlencode($referralLink) }}&text={{ urlencode('Entre na plataforma com meu convite e comece a fazer networking!') }}"
                            target="_blank" rel="noopener noreferrer"
                            onclick="trackAdminReferralShare('share', 'telegram', '{{ $referralLink }}')"
                            class="btn btn-info font-weight-bold text-white">
                            <i class="fab fa-telegram-plane mr-1"></i> Telegram
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($referralLink) }}"
                            target="_blank" rel="noopener noreferrer"
                            onclick="trackAdminReferralShare('share', 'linkedin', '{{ $referralLink }}')"
                            class="btn btn-primary font-weight-bold">
                            <i class="fab fa-linkedin mr-1"></i> LinkedIn
                        </a>
                        <a href="mailto:?subject={{ urlencode('Convite para a comunidade UNN') }}&body={{ urlencode('Olá! Quero te convidar para a maior plataforma de networking para empreendedores. Acesse: ' . $referralLink) }}"
                            onclick="trackAdminReferralShare('share', 'email', '{{ $referralLink }}')"
                            class="btn btn-secondary font-weight-bold">
                            <i class="fas fa-envelope mr-1"></i> E-mail
                        </a>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="small-box bg-light border">
                                <div class="inner">
                                    <p class="text-muted text-uppercase font-weight-bold mb-2" style="font-size: .75rem;">Código único</p>
                                    <h4 class="mb-0 font-weight-bold">{{ $user->referral_code }}</h4>
                                </div>
                                <div class="icon"><i class="fas fa-barcode"></i></div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="alert alert-light border mb-0">
                                <i class="fas fa-info-circle text-primary mr-2"></i>
                                Pontos são creditados somente após o indicado assinar um plano pago.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="row">
                <div class="col-sm-6 col-xl-12">
                    <div class="info-box bg-primary">
                        <span class="info-box-icon"><i class="fas fa-user-plus"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total indicados</span>
                            <span class="info-box-number">{{ number_format($totalReferred) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-12">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Convertidos</span>
                            <span class="info-box-number">{{ number_format($convertedCount) }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $conversionRate }}%"></div>
                            </div>
                            <span class="progress-description">{{ $conversionRate }}% de conversão</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-12">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pontos ganhos</span>
                            <span class="info-box-number">{{ number_format($totalReferralPoints) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-12">
                    <div class="info-box bg-light border">
                        <span class="info-box-icon bg-light"><i class="fas fa-gift text-success"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Por indicação</span>
                            <span class="info-box-number">{{ $pointsPerReferral ? '+' . number_format($pointsPerReferral) . ' pts' : 'Não configurado' }}</span>
                            @if($pendingCount > 0 && $pointsPerReferral > 0)
                                <span class="progress-description">Potencial pendente: +{{ number_format($potentialPoints) }} pts</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-users mr-2"></i>Pessoas que você indicou</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-valign-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Membro indicado</th>
                                    <th>Cadastro</th>
                                    <th>Plano / Status</th>
                                    <th class="text-right">Pontos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($referredUsers as $referred)
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
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $referred->photo ? asset($referred->photo) : asset('img/user.png') }}"
                                                    alt="{{ $referred->name }}"
                                                    class="img-circle img-size-40 mr-3"
                                                    style="object-fit: cover;">
                                                <div>
                                                    <div class="font-weight-bold">{{ $referred->name }}</div>
                                                    <small class="text-muted">{{ $referred->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ optional($referred->created_at)->format('d/m/Y H:i') ?: '—' }}</td>
                                        <td>
                                            @if($planName)
                                                <div class="font-weight-bold">{{ $planName }}</div>
                                            @endif
                                            <span class="badge {{ $planStatusClass }}">{{ $planStatusLabel }}</span>
                                        </td>
                                        <td class="text-right font-weight-bold {{ $pointsFromThisUser > 0 ? 'text-success' : 'text-muted' }}">
                                            {{ $pointsFromThisUser > 0 ? '+' . number_format($pointsFromThisUser) : '0' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Nenhuma indicação registrada ainda.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($referredUsers->hasPages())
                    <div class="card-footer clearfix">
                        {{ $referredUsers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-dark card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-route mr-2"></i>Como funciona</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <div class="border rounded p-3 h-100">
                                <div class="badge badge-primary mb-2">1</div>
                                <h5 class="font-weight-bold">Copie seu link</h5>
                                <p class="text-muted mb-0">Cada membro tem um link e um código únicos de indicação.</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <div class="border rounded p-3 h-100">
                                <div class="badge badge-purple mb-2">2</div>
                                <h5 class="font-weight-bold">Compartilhe</h5>
                                <p class="text-muted mb-0">Envie para contatos, comunidades, site pessoal ou blog.</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                            <div class="border rounded p-3 h-100">
                                <div class="badge badge-info mb-2">3</div>
                                <h5 class="font-weight-bold">O indicado assina</h5>
                                <p class="text-muted mb-0">O sistema atribui o cadastro, checkout e compra ao seu link.</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="border rounded p-3 h-100">
                                <div class="badge badge-warning mb-2">4</div>
                                <h5 class="font-weight-bold">Você pontua</h5>
                                <p class="text-muted mb-0">Os pontos entram após a confirmação do pagamento do plano.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-key mr-2"></i>Acesso API pessoal</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Gere tokens por dispositivo, copie na hora, renomeie integrações e acompanhe último uso e IP.</p>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($errors->has('api_tokens'))
                        <div class="alert alert-danger">{{ $errors->first('api_tokens') }}</div>
                    @endif

                    @if(!$apiTokensEnabled)
                        <div class="alert alert-warning mb-0">
                            A tabela de tokens da API ainda não está disponível neste ambiente. Rode as migrations para liberar esta área.
                        </div>
                    @else
                        @if($apiTokenPlainText)
                            <div class="alert alert-primary">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                                    <div class="mb-3 mb-lg-0">
                                        <h5 class="font-weight-bold mb-1">Token gerado para {{ $apiTokenDeviceName ?: 'integração' }}</h5>
                                        <p class="mb-0">Este token aparece uma única vez. Se perder, gere outro e revogue o antigo.</p>
                                    </div>
                                    <button type="button"
                                        class="btn btn-primary font-weight-bold"
                                        onclick="copyAdminReferralMaterial(this, '{{ e($apiTokenPlainText) }}', 'api-token', '{{ url('/api/v1/affiliate/overview') }}')">
                                        <i class="fas fa-copy mr-1"></i> Copiar token
                                    </button>
                                </div>
                                <pre class="mt-3 mb-0 p-3 bg-white border rounded text-break" style="white-space: pre-wrap;">{{ $apiTokenPlainText }}</pre>
                            </div>
                        @endif

                        @if(!$apiTokenIpTrackingEnabled)
                            <div class="alert alert-warning">
                                O campo de IP ainda não existe neste ambiente. Depois de rodar `php artisan migrate`, esta tela também mostrará o último IP de uso.
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-lg-5">
                                <div class="border rounded p-4 bg-light h-100">
                                    <h5 class="font-weight-bold">Gerar novo token</h5>
                                    <p class="text-muted">Use um nome claro para o dispositivo, blog, CRM ou página privada que vai consumir sua API.</p>
                                    <form action="{{ route('admin.referrals.tokens.store') }}" method="POST" class="mt-4">
                                        @csrf
                                        <div class="form-group">
                                            <label for="device_name">Dispositivo / integração</label>
                                            <input id="device_name"
                                                name="device_name"
                                                type="text"
                                                maxlength="120"
                                                value="{{ old('device_name') }}"
                                                placeholder="Ex.: blog-marce, painel-privado, crm-comercial"
                                                class="form-control @error('device_name') is-invalid @enderror">
                                            @error('device_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                                            <i class="fas fa-key mr-1"></i> Gerar token agora
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-7 mt-4 mt-lg-0">
                                <div class="border rounded p-4 h-100">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <h5 class="font-weight-bold mb-1">Tokens ativos</h5>
                                            <p class="text-muted mb-0">Renomeie por dispositivo, acompanhe o último uso e revogue o que não precisa mais.</p>
                                        </div>
                                        <span class="badge badge-secondary">{{ $apiTokens->count() }} ativo{{ $apiTokens->count() !== 1 ? 's' : '' }}</span>
                                    </div>
                                    @forelse($apiTokens as $token)
                                        <div class="border rounded p-3 mb-3">
                                            <div class="d-flex flex-column flex-xl-row justify-content-between">
                                                <div class="mb-3 mb-xl-0">
                                                    <div class="d-flex align-items-center flex-wrap">
                                                        <h6 class="font-weight-bold mb-0 mr-2">{{ $token->name }}</h6>
                                                        <span class="badge badge-primary">Token #{{ $token->id }}</span>
                                                    </div>
                                                    <p class="text-muted mb-1 mt-2">Criado em {{ optional($token->created_at)->format('d/m/Y H:i') ?: '—' }}</p>
                                                    <div class="small text-muted">
                                                        <span class="d-inline-block mr-3">Último uso: {{ optional($token->last_used_at)->format('d/m/Y H:i') ?: 'Nunca usado' }}</span>
                                                        <span class="d-inline-block">IP: {{ $token->last_used_ip ?: 'Indisponível' }}</span>
                                                    </div>
                                                </div>
                                                <div class="w-100 w-xl-auto" style="min-width: 18rem;">
                                                    <form action="{{ route('admin.referrals.tokens.update', $token->id) }}" method="POST" class="d-flex flex-column flex-sm-row mb-2">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="text"
                                                            name="device_name"
                                                            maxlength="120"
                                                            value="{{ old('device_name', $token->name) }}"
                                                            class="form-control mr-sm-2 mb-2 mb-sm-0">
                                                        <button type="submit" class="btn btn-outline-primary font-weight-bold">
                                                            <i class="fas fa-pen mr-1"></i> Renomear
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.referrals.tokens.destroy', $token->id) }}" method="POST" onsubmit="return confirm('Revogar este token agora?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-block font-weight-bold">
                                                            <i class="fas fa-ban mr-1"></i> Revogar token
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="alert alert-light border mb-0">
                                            Nenhum token ativo ainda. Gere o primeiro token para usar a API em blog, página privada ou painel próprio.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                        <div class="mb-3 mb-lg-0">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-chart-line mr-2"></i>Rastreio global de indicações</h3>
                            <p class="text-muted mb-0">{{ $selectedScopeLabel }} com cliques, origens, compartilhamentos, checkouts e compras.</p>
                        </div>
                        <div class="d-flex flex-wrap" style="gap:.75rem;">
                            @if($selectedReferrer)
                                <div class="alert alert-info py-2 px-3 mb-0">
                                    <strong>{{ $selectedReferrer->name }}</strong>
                                    <small class="d-block">{{ $selectedReferrer->referral_code ?: 'Sem código' }}</small>
                                </div>
                                <a href="{{ route('admin.referrals.index') }}" class="btn btn-outline-secondary font-weight-bold">
                                    <i class="fas fa-filter-circle-xmark mr-1"></i> Limpar filtro
                                </a>
                            @endif
                            <a href="{{ route('admin.referrals.export', ['referrer' => $selectedReferrer?->id]) }}"
                                class="btn btn-primary font-weight-bold">
                                <i class="fas fa-file-csv mr-1"></i> Exportar CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert {{ $trackingStatusTone === 'success' ? 'alert-success' : 'alert-warning' }}">
                        {{ $trackingStatusMessage }}
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-xl-2">
                            <div class="info-box bg-light border">
                                <span class="info-box-icon bg-primary"><i class="fas fa-mouse-pointer"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cliques</span>
                                    <span class="info-box-number">{{ number_format($trackingSummary['clicks'] ?? 0) }}</span>
                                    <small class="text-muted">{{ number_format($trackingSummary['visits'] ?? 0) }} visitas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-2">
                            <div class="info-box bg-light border">
                                <span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cadastros</span>
                                    <span class="info-box-number">{{ number_format($trackingSummary['registrations'] ?? 0) }}</span>
                                    <small class="text-muted">{{ number_format($trackingSummary['registration_conversion'] ?? 0) }}% conv.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-2">
                            <div class="info-box bg-light border">
                                <span class="info-box-icon bg-warning"><i class="fas fa-cash-register"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Checkouts</span>
                                    <span class="info-box-number">{{ number_format($trackingSummary['checkout_starts'] ?? 0) }}</span>
                                    <small class="text-muted">Inícios atribuídos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-2">
                            <div class="info-box bg-light border">
                                <span class="info-box-icon bg-purple"><i class="fas fa-bag-shopping"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Compras</span>
                                    <span class="info-box-number">{{ number_format($trackingSummary['purchases'] ?? 0) }}</span>
                                    <small class="text-muted">{{ number_format($trackingSummary['purchase_conversion'] ?? 0) }}% conv.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-2">
                            <div class="info-box bg-light border">
                                <span class="info-box-icon bg-info"><i class="fas fa-share-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Compart.</span>
                                    <span class="info-box-number">{{ number_format(($trackingSummary['shares'] ?? 0) + ($trackingSummary['reshares'] ?? 0) + ($trackingSummary['copies'] ?? 0)) }}</span>
                                    <small class="text-muted">{{ number_format($trackingSummary['copies'] ?? 0) }} cópias</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-2">
                            <div class="info-box bg-light border">
                                <span class="info-box-icon bg-dark"><i class="fas fa-wallet"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Receita</span>
                                    <span class="info-box-number">R$ {{ number_format((float) ($trackingSummary['revenue'] ?? 0), 2, ',', '.') }}</span>
                                    <small class="text-muted">Atualizado às {{ $trackingUpdatedAtLabel }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-primary card-outline mt-4">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-ranking-star mr-2"></i>Ranking de afiliados</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-valign-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Afiliado</th>
                                            <th class="text-right">Cliques</th>
                                            <th class="text-right">Visitas</th>
                                            <th class="text-right">Cadastros</th>
                                            <th class="text-right">Compras</th>
                                            <th class="text-right">Receita</th>
                                            <th class="text-right">Compart.</th>
                                            <th class="text-right">Pontos</th>
                                            <th>Última atividade</th>
                                            <th class="text-right">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($affiliateLeaderboard as $affiliate)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $affiliate->photo ? asset($affiliate->photo) : asset('img/user.png') }}"
                                                            alt="{{ $affiliate->name }}"
                                                            class="img-circle img-size-40 mr-3"
                                                            style="object-fit: cover;">
                                                        <div>
                                                            <div class="font-weight-bold">{{ $affiliate->name }}</div>
                                                            <small class="text-muted d-block">{{ $affiliate->email }}</small>
                                                            <span class="badge badge-info mt-1">{{ $affiliate->referral_code ?: 'Sem código' }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-right font-weight-bold">{{ number_format($affiliate->clicks) }}</td>
                                                <td class="text-right font-weight-bold">{{ number_format($affiliate->visits) }}</td>
                                                <td class="text-right text-success font-weight-bold">{{ number_format($affiliate->registrations) }}</td>
                                                <td class="text-right text-purple font-weight-bold">{{ number_format($affiliate->purchases) }}</td>
                                                <td class="text-right font-weight-bold">R$ {{ number_format($affiliate->revenue, 2, ',', '.') }}</td>
                                                <td class="text-right font-weight-bold">{{ number_format($affiliate->shares_total) }}</td>
                                                <td class="text-right text-warning font-weight-bold">{{ number_format($affiliate->referral_points) }}</td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $affiliate->last_activity_label }}</div>
                                                    <small class="text-muted">{{ $affiliate->last_activity_human }}</small>
                                                </td>
                                                <td class="text-right">
                                                    <a href="{{ route('admin.referrals.index', ['referrer' => $affiliate->id]) }}"
                                                        class="btn btn-sm btn-outline-primary font-weight-bold">
                                                        <i class="fas fa-chart-line mr-1"></i> Ver detalhes
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4">Ainda não há afiliados com dados rastreados.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($affiliateLeaderboard->hasPages())
                            <div class="card-footer clearfix">
                                {{ $affiliateLeaderboard->links() }}
                            </div>
                        @endif
                    </div>

                    <div class="row mt-4">
                        <div class="col-lg-4">
                            <div class="card card-info card-outline h-100">
                                <div class="card-header">
                                    <h3 class="card-title font-weight-bold"><i class="fas fa-sitemap mr-2"></i>Funil por canal</h3>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Canal</th>
                                                    <th class="text-right">Visitas</th>
                                                    <th class="text-right">Cad.</th>
                                                    <th class="text-right">Compras</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($channelFunnels as $funnel)
                                                    <tr>
                                                        <td>
                                                            <div class="font-weight-bold">{{ $funnel->channel }}</div>
                                                            <small class="text-muted">{{ number_format($funnel->registration_conversion) }}% cad. · {{ number_format($funnel->purchase_conversion) }}% compra</small>
                                                        </td>
                                                        <td class="text-right">{{ number_format($funnel->visits) }}</td>
                                                        <td class="text-right text-success">{{ number_format($funnel->registrations) }}</td>
                                                        <td class="text-right text-purple">{{ number_format($funnel->purchases) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">Ainda não há canais rastreados para este escopo.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 mt-4 mt-lg-0">
                            <div class="card card-dark card-outline h-100">
                                <div class="card-header">
                                    <h3 class="card-title font-weight-bold"><i class="fas fa-history mr-2"></i>Log detalhado de cliques, visitas e compartilhamentos</h3>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-valign-middle table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Quando</th>
                                                    <th>Afiliado</th>
                                                    <th>Ação</th>
                                                    <th>Canal / origem</th>
                                                    <th>Landing / URL</th>
                                                    <th>Dispositivo</th>
                                                    <th>Localização</th>
                                                    <th>Resultado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($detailedEvents as $event)
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
                                                        <td>
                                                            <div class="font-weight-bold">{{ $event->occurred_at_label }}</div>
                                                            <small class="text-muted">{{ $event->occurred_at_human }}</small>
                                                        </td>
                                                        <td>
                                                            <div class="font-weight-bold">{{ $event->referrer_name }}</div>
                                                            <small class="text-muted">{{ $event->referral_code }}</small>
                                                        </td>
                                                        <td><span class="badge {{ $badgeClass }}">{{ $event->event_label }}</span></td>
                                                        <td>
                                                            <div class="font-weight-bold">{{ $event->channel_label }}</div>
                                                            <small class="text-muted text-break">{{ $event->source_url }}</small>
                                                        </td>
                                                        <td>
                                                            <div class="font-weight-bold">{{ $event->landing_page_path }}</div>
                                                            <small class="text-muted text-break">{{ $event->tracked_page_url }}</small>
                                                        </td>
                                                        <td>
                                                            <div>{{ $event->device_label }}</div>
                                                            <small class="text-muted">{{ $event->browser_label }} · {{ $event->os_label }}</small>
                                                        </td>
                                                        <td>{{ $event->location_label }}</td>
                                                        <td>
                                                            <div class="font-weight-bold">{{ $event->result_user_label }}</div>
                                                            <small class="text-muted">{{ $event->result_value_label }}</small>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center text-muted py-4">Ainda não há eventos rastreados para este escopo.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @if($detailedEvents->hasPages())
                                    <div class="card-footer clearfix">
                                        {{ $detailedEvents->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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

            setTimeout(() => {
                button.innerHTML = originalHtml;
            }, 1800);
        }
    </script>
@endpush
