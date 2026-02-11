@extends('admin.layouts.app')

@section('title', 'Pagamentos do Marketplace')
@section('page_title', 'Pagamentos do Marketplace')

@section('content')
    @php
        $gateway = $gateway ?? null;
        $hasToken = $gateway && (string) ($gateway->access_token ?? '') !== '';
        $webhookUrl = route('webhook.mercadopago', ['seller_id' => (string) auth()->id()]);
    @endphp

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-credit-card mr-2"></i>MercadoPago</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        As vendas do marketplace são processadas na sua própria conta do MercadoPago.
                    </p>

                    <form action="{{ route('admin.marketplace.payments.update') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label>Public Key</label>
                            <input type="text" name="public_key" value="{{ old('public_key', $gateway->public_key ?? '') }}"
                                class="form-control @error('public_key') is-invalid @enderror" required>
                            @error('public_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Disponível no painel de desenvolvedores do MercadoPago.</small>
                        </div>

                        <div class="form-group">
                            <label>Access Token</label>
                            <input type="password" name="access_token" value=""
                                class="form-control @error('access_token') is-invalid @enderror" {{ $hasToken ? '' : 'required' }}>
                            @error('access_token')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            @if($hasToken)
                                <small class="text-muted">Deixe em branco para manter o token atual.</small>
                            @else
                                <small class="text-muted">Token de produção (obrigatório para receber pagamentos).</small>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Salvar credenciais
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-link mr-2"></i>URL de notificação</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Caso precise informar manualmente no MercadoPago, utilize:</p>
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
        </div>
    </div>
@endsection
