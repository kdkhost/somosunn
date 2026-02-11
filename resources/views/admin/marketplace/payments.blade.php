@extends('admin.layouts.app')

@section('title', 'Pagamentos do Marketplace')
@section('page_title', 'Pagamentos do Marketplace')

@section('content')
    @php
        $paymentsConfigured = (bool) ($paymentsConfigured ?? false);
        $webhookUrl = (string) ($webhookUrl ?? '');
        $isAdmin = auth()->user() && auth()->user()->isAdmin();
    @endphp

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-credit-card mr-2"></i>MercadoPago</h3>
        </div>
        <div class="card-body">
            @if($paymentsConfigured)
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i> Pagamentos configurados e habilitados na plataforma.
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Pagamento indisponível: o MercadoPago ainda não foi configurado na plataforma.
                </div>
            @endif

            <p class="text-muted mb-3">
                Este sistema utiliza <strong>uma única configuração</strong> do gateway (multi-tenant) para toda a plataforma.
                Cada venda é registrada com <strong>vendedor</strong> e <strong>tipo</strong> (curso, mentoria, evento e marketplace).
            </p>

            @if($isAdmin)
                <a href="{{ route('admin.settings', ['group' => 'gateway']) }}" class="btn btn-primary">
                    <i class="fas fa-cogs mr-1"></i> Abrir configurações do gateway
                </a>
            @else
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle mr-2"></i> As credenciais do gateway são gerenciadas pelos administradores da plataforma.
                </div>
            @endif
        </div>
    </div>

    @if($webhookUrl !== '')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-link mr-2"></i>URL de notificação (Webhook)</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Caso precise informar manualmente no painel do MercadoPago, utilize:</p>
                <div class="input-group">
                    <input type="text" class="form-control" readonly value="{{ $webhookUrl }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button"
                            onclick="navigator.clipboard.writeText(this.parentElement.previousElementSibling.value); toastr.success('Copiado!')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">O sistema também envia esta URL automaticamente no checkout.</small>
            </div>
        </div>
    @endif
@endsection

