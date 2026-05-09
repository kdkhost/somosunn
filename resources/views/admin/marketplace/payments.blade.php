@extends('admin.layouts.app')

@section('title', 'Pagamentos do Marketplace')
@section('page_title', 'Pagamentos do Marketplace')

@section('content')
    @php
        $isAdmin = auth()->user() && auth()->user()->isAdmin();

        $mpConfigured = $mpConfigured ?? ($paymentsConfigured ?? false);
        $mpEnabled = $mpEnabled ?? false;
        $mpMethods = $mpMethods ?? [];
        $mpWebhookUrl = $mpWebhookUrl ?? ($webhookUrl ?? '');

        $sumupConfigured = $sumupConfigured ?? false;
        $sumupEnabled = $sumupEnabled ?? false;
        $sumupMethods = $sumupMethods ?? [];
        $sumupWebhookUrl = $sumupWebhookUrl ?? '';

        $totalGateways = ($mpEnabled && $mpConfigured ? 1 : 0) + ($sumupEnabled && $sumupConfigured ? 1 : 0);
    @endphp

    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="info-box elevation-1">
                <span class="info-box-icon bg-gradient-primary"><i class="fas fa-credit-card"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Gateways ativos</span>
                    <span class="info-box-number">{{ $totalGateways }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="info-box elevation-1">
                <span class="info-box-icon bg-gradient-{{ $mpEnabled && $mpConfigured ? 'success' : 'secondary' }}"><i class="fas fa-hand-holding-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">MercadoPago</span>
                    <span class="info-box-number">{{ $mpEnabled && $mpConfigured ? 'Ativo' : ($mpConfigured ? 'Desativado' : 'Nao configurado') }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="info-box elevation-1">
                <span class="info-box-icon bg-gradient-{{ $sumupEnabled && $sumupConfigured ? 'success' : 'secondary' }}"><i class="fas fa-credit-card"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">SumUp</span>
                    <span class="info-box-number">{{ $sumupEnabled && $sumupConfigured ? 'Ativo' : ($sumupConfigured ? 'Desativado' : 'Nao configurado') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumo geral --}}
    <div class="alert alert-{{ $totalGateways === 0 ? 'danger' : ($totalGateways >= 2 ? 'success' : 'warning') }} shadow-sm">
        <i class="fas fa-info-circle mr-2"></i>
        @if($totalGateways === 0)
            <strong>Nenhum gateway ativo.</strong> Configure ao menos um gateway para aceitar pagamentos na plataforma.
        @elseif($totalGateways === 1)
            <strong>1 gateway ativo.</strong> Voce pode adicionar o segundo gateway para oferecer mais opcoes aos compradores.
        @else
            <strong>{{ $totalGateways }} gateways ativos em paralelo.</strong> Os compradores poderao escolher entre {{ implode(' e ', array_filter([($mpEnabled && $mpConfigured ? 'MercadoPago' : null), ($sumupEnabled && $sumupConfigured ? 'SumUp' : null)])) }} no checkout.
        @endif
    </div>

    <div class="row">
        {{-- MercadoPago --}}
        <div class="col-lg-6">
            <div class="card card-outline card-primary shadow-sm h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title font-weight-bold mb-0">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-lg bg-primary text-white mr-2" style="width:32px;height:32px;">
                            <i class="fas fa-hand-holding-dollar"></i>
                        </span>
                        MercadoPago
                    </h3>
                    <div>
                        @if($mpEnabled && $mpConfigured)
                            <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Ativo</span>
                        @elseif($mpConfigured)
                            <span class="badge badge-warning"><i class="fas fa-pause mr-1"></i> Configurado (desativado)</span>
                        @else
                            <span class="badge badge-secondary"><i class="fas fa-times mr-1"></i> Nao configurado</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($mpEnabled && $mpConfigured)
                        <div class="alert alert-success mb-3">
                            <i class="fas fa-check-circle mr-2"></i> Pagamentos habilitados e funcionando.
                        </div>
                    @elseif($mpConfigured)
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-pause-circle mr-2"></i> Credenciais configuradas, mas o gateway esta desativado.
                        </div>
                    @else
                        <div class="alert alert-danger mb-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i> MercadoPago ainda nao foi configurado.
                        </div>
                    @endif

                    @if(!empty($mpMethods))
                        <p class="text-muted small mb-2"><strong>Metodos ativos:</strong></p>
                        <div class="mb-3">
                            @foreach($mpMethods as $method)
                                <span class="badge badge-light border mr-1 mb-1">
                                    <i class="fas fa-check text-success mr-1"></i>{{ $method }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <p class="text-muted small mb-3">
                        Configuracao unica multi-tenant. Cada venda e registrada com vendedor e tipo (curso, mentoria, evento, marketplace).
                    </p>

                    @if($isAdmin)
                        <a href="{{ route('admin.settings', ['group' => 'gateway']) }}" class="btn btn-primary btn-block rounded-pill elevation-1">
                            <i class="fas fa-cogs mr-1"></i> Configurar MercadoPago
                        </a>
                    @endif

                    @if($mpWebhookUrl)
                        <div class="mt-3 pt-3 border-top">
                            <p class="small font-weight-bold mb-2">
                                <i class="fas fa-link mr-1 text-muted"></i> Webhook URL:
                            </p>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" readonly value="{{ $mpWebhookUrl }}">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button"
                                        onclick="navigator.clipboard.writeText(this.parentElement.previousElementSibling.value); (window.toastr && toastr.success('Copiado!'))">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- SumUp --}}
        <div class="col-lg-6">
            <div class="card card-outline card-dark shadow-sm h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title font-weight-bold mb-0">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-lg bg-dark text-white mr-2" style="width:32px;height:32px;">
                            <i class="fas fa-credit-card"></i>
                        </span>
                        SumUp
                    </h3>
                    <div>
                        @if($sumupEnabled && $sumupConfigured)
                            <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Ativo</span>
                        @elseif($sumupConfigured)
                            <span class="badge badge-warning"><i class="fas fa-pause mr-1"></i> Configurado (desativado)</span>
                        @else
                            <span class="badge badge-secondary"><i class="fas fa-times mr-1"></i> Nao configurado</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($sumupEnabled && $sumupConfigured)
                        <div class="alert alert-success mb-3">
                            <i class="fas fa-check-circle mr-2"></i> Pagamentos via SumUp habilitados.
                        </div>
                    @elseif($sumupConfigured)
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-pause-circle mr-2"></i> Credenciais configuradas, mas o gateway esta desativado.
                        </div>
                    @else
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle mr-2"></i> SumUp ainda nao foi configurado. Use-o em paralelo ao MercadoPago ou como gateway unico.
                        </div>
                    @endif

                    @if(!empty($sumupMethods))
                        <p class="text-muted small mb-2"><strong>Metodos ativos:</strong></p>
                        <div class="mb-3">
                            @foreach($sumupMethods as $method)
                                <span class="badge badge-light border mr-1 mb-1">
                                    <i class="fas fa-check text-success mr-1"></i>{{ $method }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <p class="text-muted small mb-3">
                        Alternativa ao MercadoPago. Quando ativos em paralelo, o comprador escolhe no checkout qual gateway usar.
                    </p>

                    @if($isAdmin)
                        <a href="{{ route('admin.settings', ['group' => 'gateway']) }}#sumup-tab" class="btn btn-dark btn-block rounded-pill elevation-1">
                            <i class="fas fa-cogs mr-1"></i> Configurar SumUp
                        </a>
                    @endif

                    @if($sumupWebhookUrl)
                        <div class="mt-3 pt-3 border-top">
                            <p class="small font-weight-bold mb-2">
                                <i class="fas fa-link mr-1 text-muted"></i> Webhook URL:
                            </p>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" readonly value="{{ $sumupWebhookUrl }}">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button"
                                        onclick="navigator.clipboard.writeText(this.parentElement.previousElementSibling.value); (window.toastr && toastr.success('Copiado!'))">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Info tecnica --}}
    <div class="card card-outline card-primary shadow-sm mt-3">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Como funciona</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="d-flex">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mr-3" style="width:40px;height:40px;flex-shrink:0;">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold mb-1">Multi-gateway</h6>
                            <p class="text-muted small mb-0">Voce pode manter MercadoPago e SumUp ativos simultaneamente ou usar apenas um.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="d-flex">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mr-3" style="width:40px;height:40px;flex-shrink:0;">
                            <i class="fas fa-hand-pointer"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold mb-1">Escolha do cliente</h6>
                            <p class="text-muted small mb-0">Quando ambos estao ativos, o comprador escolhe qual gateway prefere no checkout.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="d-flex">
                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mr-3" style="width:40px;height:40px;flex-shrink:0;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold mb-1">Fallback automatico</h6>
                            <p class="text-muted small mb-0">Se um gateway falhar, o sistema tenta usar o outro (quando configurado).</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
