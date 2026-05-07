@php
    $mpEnv = $settings['mercadopago_env'] ?? 'sandbox';
    $hasSandbox = !empty($settings['mercadopago_sandbox_public_key']) && !empty($settings['mercadopago_sandbox_access_token']);
    $hasProduction = !empty($settings['mercadopago_prod_public_key']) && !empty($settings['mercadopago_prod_access_token']);
    $isConfigured = $mpEnv === 'production' ? $hasProduction : $hasSandbox;
    $gatewayEnabled = (int) ($settings['mercadopago_enabled'] ?? 1) === 1;
    $paymentMethods = [
        ['name' => 'mercadopago_method_credit_card', 'label' => 'Cartao de credito', 'icon' => 'fas fa-credit-card', 'checked' => (int) ($settings['mercadopago_method_credit_card'] ?? 1) === 1],
        ['name' => 'mercadopago_method_debit_card', 'label' => 'Cartao de debito', 'icon' => 'fas fa-money-check-alt', 'checked' => (int) ($settings['mercadopago_method_debit_card'] ?? 0) === 1],
        ['name' => 'mercadopago_method_pix', 'label' => 'Pix', 'icon' => 'fas fa-bolt', 'checked' => (int) ($settings['mercadopago_method_pix'] ?? 1) === 1],
        ['name' => 'mercadopago_method_ticket', 'label' => 'Boleto', 'icon' => 'fas fa-barcode', 'checked' => (int) ($settings['mercadopago_method_ticket'] ?? 0) === 1],
        ['name' => 'mercadopago_method_mercadopago', 'label' => 'Carteira MP', 'icon' => 'fas fa-wallet', 'checked' => (int) ($settings['mercadopago_method_mercadopago'] ?? 0) === 1],
    ];
@endphp

@push('styles')
<style>
    .gateway-shell .gateway-top{border-radius:1rem;background:linear-gradient(135deg,#1548c0 0%,#1f67ef 55%,#37a4ff 100%);color:#fff;padding:1.5rem;box-shadow:0 18px 42px rgba(31,103,239,.2)}
    .gateway-shell .gateway-badge{display:inline-flex;align-items:center;gap:.45rem;padding:.45rem .8rem;border-radius:999px;font-size:.75rem;font-weight:700;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.15)}
    .gateway-shell .gateway-badge.badge-success{background:rgba(40,199,111,.25);border-color:rgba(40,199,111,.4);color:#fff}
    .gateway-shell .gateway-badge.badge-secondary{background:rgba(108,117,125,.25);border-color:rgba(108,117,125,.4);color:#fff}
    .gateway-shell .gateway-dot{width:8px;height:8px;border-radius:999px;background:currentColor}
    .gateway-shell .gateway-tab-btn{border-radius:999px}
    .gateway-shell .main-gateway-tab{border-radius:12px;font-size:1.1rem;padding:.75rem 1.5rem;transition:all .3s ease}
    .gateway-shell .main-gateway-tab .badge{font-size:.7rem;padding:.25rem .5rem}
    .gateway-shell .main-gateway-pane{display:none}
    .gateway-shell .main-gateway-pane.is-active{display:block}
    .gateway-shell .gateway-pane{display:none}
    .gateway-shell .gateway-pane.is-active{display:block}
    .gateway-shell .gateway-method-card{border:1px solid #dfe7f2;border-radius:1rem;padding:1rem;height:100%;transition:.2s ease;background:#fff}
    .gateway-shell .gateway-method-card.is-enabled{border-color:rgba(31,103,239,.3);box-shadow:0 10px 24px rgba(31,103,239,.08);background:#f8fbff}
    .gateway-shell .gateway-method-icon{width:46px;height:46px;border-radius:.9rem;background:#eff6ff;color:#1d4ed8;display:flex;align-items:center;justify-content:center;font-size:1.1rem}
    .gateway-shell .gateway-section{border-radius:1rem;overflow:hidden;box-shadow:0 10px 28px rgba(15,23,42,.06)}
    .gateway-shell .gateway-color-preview{width:100%;min-height:54px;border:1px solid #d8dee9;border-radius:.85rem}
    @media (max-width:767.98px){.gateway-shell .gateway-tab-btn{width:100%;margin-bottom:.5rem}}
</style>
@endpush

<div class="card-body gateway-shell">
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i>
        Configure os gateways de pagamento da plataforma. <strong>Apenas um gateway pode ficar ativo por vez.</strong>
    </div>

    {{-- ============================================================
         TABS PRINCIPAIS: MERCADO PAGO | SUMUP
         ============================================================ --}}
    <div class="mb-4">
        <button type="button" class="btn btn-lg btn-primary main-gateway-tab mr-2 mb-2" data-target="main-tab-mercadopago" onclick="switchMainGatewayTab(this)">
            <i class="fas fa-handshake mr-2"></i>Mercado Pago
            @if($gatewayEnabled)
                <span class="badge badge-success ml-2">Ativo</span>
            @endif
        </button>
        <button type="button" class="btn btn-lg btn-outline-secondary main-gateway-tab mb-2" data-target="main-tab-sumup" onclick="switchMainGatewayTab(this)">
            <i class="fas fa-credit-card mr-2"></i>SumUp
            @if(($settings['sumup_enabled'] ?? 0))
                <span class="badge badge-success ml-2">Ativo</span>
            @endif
        </button>
    </div>

    {{-- ============================================================
         TAB MERCADO PAGO
         ============================================================ --}}
    <div class="main-gateway-pane is-active" id="main-tab-mercadopago">

    <div class="gateway-top mb-4" id="gatewayHero" data-sandbox-configured="{{ $hasSandbox ? 1 : 0 }}" data-production-configured="{{ $hasProduction ? 1 : 0 }}">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">
            <div class="pr-lg-4 mb-3 mb-lg-0">
                <div class="text-uppercase font-weight-bold mb-1" style="font-size:.72rem;letter-spacing:.14em;opacity:.8;">Pagamentos</div>
                <h3 class="mb-2 font-weight-bold">MercadoPago</h3>
                <p class="mb-3" style="opacity:.86;max-width:720px;">Credenciais, webhook, metodos, cobranca e checkout reunidos em blocos separados para evitar quebra visual no AdminLTE.</p>
                <div class="d-flex flex-wrap" style="gap:.5rem;">
                    <span class="gateway-badge" id="gatewayEnvBadge"><span class="gateway-dot"></span>{{ $mpEnv === 'production' ? 'Producao' : 'Sandbox' }}</span>
                    <span class="gateway-badge" id="gatewayConfigBadge"><span class="gateway-dot"></span>{{ $isConfigured ? 'Configurado' : 'Pendente' }}</span>
                    <span class="gateway-badge {{ $gatewayEnabled ? 'badge-success' : 'badge-secondary' }}" id="gatewayEnabledBadge"><span class="gateway-dot"></span>{{ $gatewayEnabled ? 'Gateway ativo' : 'Gateway inativo' }}</span>
                </div>
            </div>
            <div class="bg-white rounded-lg p-3 text-dark" style="min-width:280px;max-width:320px;width:100%;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Status rapido</strong>
                    <div class="custom-control custom-switch mb-0">
                        <input type="hidden" name="mercadopago_enabled" value="0">
                        <input type="checkbox" class="custom-control-input gateway-enable-toggle" id="mercadopago_enabled" name="mercadopago_enabled" value="1" data-gateway="mercadopago" onchange="handleGatewayEnabledToggle(this)" {{ $gatewayEnabled ? 'checked' : '' }}>
                        <label class="custom-control-label" for="mercadopago_enabled"></label>
                    </div>
                </div>
                <small class="text-muted d-block mb-3">Desligue para esconder o MercadoPago como gateway principal da plataforma.</small>
                <button type="button" class="btn btn-primary btn-block font-weight-bold" id="btn-test-mercadopago" onclick="testGatewayConnection()">
                    <i class="fas fa-plug mr-1"></i> Testar conexao
                </button>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <button type="button" class="btn btn-primary gateway-tab-btn mr-2 mb-2" data-target="gateway-pane-credentials" onclick="switchGatewayTab(this)">Credenciais</button>
        <button type="button" class="btn btn-outline-secondary gateway-tab-btn mr-2 mb-2" data-target="gateway-pane-methods" onclick="switchGatewayTab(this)">Metodos</button>
        <button type="button" class="btn btn-outline-secondary gateway-tab-btn mr-2 mb-2" data-target="gateway-pane-billing" onclick="switchGatewayTab(this)">Cobranca</button>
        <button type="button" class="btn btn-outline-secondary gateway-tab-btn mr-2 mb-2" data-target="gateway-pane-checkout" onclick="switchGatewayTab(this)">Checkout</button>
        <button type="button" class="btn btn-outline-secondary gateway-tab-btn mb-2" data-target="gateway-pane-advanced" onclick="switchGatewayTab(this)">Avancado</button>
    </div>

    <div class="gateway-pane is-active" id="gateway-pane-credentials">
        <div class="row">
            <div class="col-12 col-xl-8">
                <div class="card card-outline card-primary gateway-section">
                    <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-key mr-2"></i>Ambiente e chaves</h3></div>
                    <div class="card-body">
                        <div class="mb-4">
                            <button type="button" class="btn btn-outline-warning mr-2 {{ $mpEnv !== 'production' ? 'active' : '' }}" id="gatewayEnvBtnSandbox" onclick="setGatewayEnvironment('sandbox')">Sandbox</button>
                            <button type="button" class="btn btn-outline-success {{ $mpEnv === 'production' ? 'active' : '' }}" id="gatewayEnvBtnProduction" onclick="setGatewayEnvironment('production')">Producao</button>
                            <select class="d-none" name="mercadopago_env" id="mercadopago_env">
                                <option value="sandbox" {{ $mpEnv !== 'production' ? 'selected' : '' }}>sandbox</option>
                                <option value="production" {{ $mpEnv === 'production' ? 'selected' : '' }}>production</option>
                            </select>
                        </div>

                        <div id="gatewayEnvSandboxPanel" class="{{ $mpEnv === 'production' ? 'd-none' : '' }}">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="text-uppercase text-muted small font-weight-bold">Public key sandbox</label>
                                    <input type="text" name="mercadopago_sandbox_public_key" value="{{ $settings['mercadopago_sandbox_public_key'] ?? '' }}" class="form-control" placeholder="TEST-xxxxxxxx">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="text-uppercase text-muted small font-weight-bold">Access token sandbox</label>
                                    <div class="input-group">
                                        <input type="password" name="mercadopago_sandbox_access_token" id="mercadopago_sandbox_access_token" value="{{ $settings['mercadopago_sandbox_access_token'] ?? '' }}" class="form-control" placeholder="TEST-xxxxxxxx">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="toggleGatewaySecret('mercadopago_sandbox_access_token', this)"><i class="fas fa-eye"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="gatewayEnvProductionPanel" class="{{ $mpEnv !== 'production' ? 'd-none' : '' }}">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="text-uppercase text-muted small font-weight-bold">Public key producao</label>
                                    <input type="text" name="mercadopago_prod_public_key" value="{{ $settings['mercadopago_prod_public_key'] ?? '' }}" class="form-control" placeholder="APP_USR-xxxxxxxx">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="text-uppercase text-muted small font-weight-bold">Access token producao</label>
                                    <div class="input-group">
                                        <input type="password" name="mercadopago_prod_access_token" id="mercadopago_prod_access_token" value="{{ $settings['mercadopago_prod_access_token'] ?? '' }}" class="form-control" placeholder="APP_USR-xxxxxxxx">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="toggleGatewaySecret('mercadopago_prod_access_token', this)"><i class="fas fa-eye"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="text-uppercase text-muted small font-weight-bold">Client ID</label>
                                <input type="text" name="mercadopago_client_id" value="{{ $settings['mercadopago_client_id'] ?? '' }}" class="form-control" placeholder="1234567890123456">
                            </div>
                            <div class="col-md-6 form-group mb-0">
                                <label class="text-uppercase text-muted small font-weight-bold">Client secret</label>
                                <div class="input-group">
                                    <input type="password" name="mercadopago_client_secret" id="mercadopago_client_secret" value="{{ $settings['mercadopago_client_secret'] ?? '' }}" class="form-control" placeholder="Segredo da aplicacao">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleGatewaySecret('mercadopago_client_secret', this)"><i class="fas fa-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card card-outline card-info gateway-section">
                    <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-link mr-2"></i>Webhook</h3></div>
                    <div class="card-body">
                        <p class="text-muted">Copie este endereco e cadastre no MercadoPago para confirmacao automatica.</p>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="gatewayWebhookUrl" readonly value="{{ route('api.webhooks.mercadopago') }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyGatewayWebhook()"><i class="fas fa-copy mr-1"></i>Copiar</button>
                            </div>
                        </div>
                        <small class="text-muted">Tipos recomendados: pagamentos e merchant_order.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="gateway-pane" id="gateway-pane-methods">
        <div class="card card-outline card-primary gateway-section">
            <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-credit-card mr-2"></i>Metodos de pagamento</h3></div>
            <div class="card-body">
                <div class="row">
                    @foreach($paymentMethods as $method)
                        <div class="col-md-6 col-xl-4 mb-3">
                            <div class="gateway-method-card {{ $method['checked'] ? 'is-enabled' : '' }}" data-method-card>
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex">
                                        <div class="gateway-method-icon mr-3"><i class="{{ $method['icon'] }}"></i></div>
                                        <div>
                                            <div class="font-weight-bold">{{ $method['label'] }}</div>
                                            <small class="text-muted">Salvo ao confirmar as alteracoes.</small>
                                        </div>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="{{ $method['name'] }}" value="0">
                                        <input type="checkbox" class="custom-control-input" id="{{ $method['name'] }}" name="{{ $method['name'] }}" value="1" onchange="updateGatewayMethodCard(this)" {{ $method['checked'] ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="{{ $method['name'] }}"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="gateway-pane" id="gateway-pane-billing">
        <div class="card card-outline card-primary gateway-section">
            <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-sliders-h mr-2"></i>Regras de cobranca</h3></div>
            <div class="card-body">
                <div class="card bg-light border mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                            <div class="pr-md-3 mb-3 mb-md-0">
                                <h5 class="mb-1">Checkout transparente</h5>
                                <p class="text-muted mb-0">Mantem o usuario no site. Desligado, o checkout pode redirecionar para o gateway.</p>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="gateway_transparent_checkout" value="0">
                                <input type="checkbox" class="custom-control-input" id="gateway_transparent_checkout" name="gateway_transparent_checkout" value="1" {{ (int) ($settings['gateway_transparent_checkout'] ?? 0) === 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="gateway_transparent_checkout"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="text-uppercase text-muted small font-weight-bold">Juros (% a.m.)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="gateway_installment_tax" class="form-control" value="{{ $settings['gateway_installment_tax'] ?? '0.00' }}">
                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="text-uppercase text-muted small font-weight-bold">Parcelas sem juros</label>
                        <input type="number" name="gateway_max_installments_no_interest" class="form-control" value="{{ $settings['gateway_max_installments_no_interest'] ?? '1' }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="text-uppercase text-muted small font-weight-bold">Repassar taxa</label>
                        <select name="gateway_pass_tax_to_client" class="form-control">
                            <option value="0" {{ (int) ($settings['gateway_pass_tax_to_client'] ?? 0) === 0 ? 'selected' : '' }}>Nao - empresa absorve</option>
                            <option value="1" {{ (int) ($settings['gateway_pass_tax_to_client'] ?? 1) === 1 ? 'selected' : '' }}>Sim - cliente paga</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="text-uppercase text-muted small font-weight-bold">Expiração do Pix (min)</label>
                        <div class="input-group">
                            <input type="number" name="pix_expiration_minutes" class="form-control" value="{{ $settings['pix_expiration_minutes'] ?? '15' }}" min="5" max="1440">
                            <div class="input-group-append"><span class="input-group-text">min</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="gateway-pane" id="gateway-pane-checkout">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-primary gateway-section">
                    <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-palette mr-2"></i>Tema do checkout</h3></div>
                    <div class="card-body">
                        <label class="text-uppercase text-muted small font-weight-bold">Tema</label>
                        <select name="gateway_checkout_theme" class="form-control">
                            <option value="default" {{ ($settings['gateway_checkout_theme'] ?? 'default') === 'default' ? 'selected' : '' }}>Padrao do MercadoPago</option>
                            <option value="dark" {{ ($settings['gateway_checkout_theme'] ?? '') === 'dark' ? 'selected' : '' }}>Dark</option>
                            <option value="bootstrap" {{ ($settings['gateway_checkout_theme'] ?? '') === 'bootstrap' ? 'selected' : '' }}>Bootstrap</option>
                            <option value="flat" {{ ($settings['gateway_checkout_theme'] ?? '') === 'flat' ? 'selected' : '' }}>Flat</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-outline card-primary gateway-section">
                    <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-brush mr-2"></i>Cor principal</h3></div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-sm-4 mb-3 mb-sm-0">
                                <input type="color" name="gateway_checkout_primary_color" id="gateway_checkout_primary_color" class="gateway-color-preview" value="{{ $settings['gateway_checkout_primary_color'] ?? '#1F5EDB' }}" oninput="syncGatewayColor(this.value)">
                            </div>
                            <div class="col-sm-8">
                                <label class="text-uppercase text-muted small font-weight-bold">Hex</label>
                                <input type="text" class="form-control" id="gateway_checkout_primary_color_text" value="{{ $settings['gateway_checkout_primary_color'] ?? '#1F5EDB' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="gateway-pane" id="gateway-pane-advanced">
        <div class="card card-outline card-secondary gateway-section">
            <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-fingerprint mr-2"></i>Identificacao da plataforma</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="text-uppercase text-muted small font-weight-bold">Integrator ID</label>
                        <input type="text" name="mercadopago_integrator_id" value="{{ $settings['mercadopago_integrator_id'] ?? '' }}" class="form-control" placeholder="dev_1234567890">
                    </div>
                    <div class="col-md-6 form-group mb-0">
                        <label class="text-uppercase text-muted small font-weight-bold">Platform ID</label>
                        <input type="text" name="mercadopago_platform_id" value="{{ $settings['mercadopago_platform_id'] ?? '' }}" class="form-control" placeholder="plat_1234567890">
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>

    {{-- FIM TAB MERCADO PAGO --}}
    </div>

    {{-- ============================================================
         TAB SUMUP
         ============================================================ --}}
    <div class="main-gateway-pane" id="main-tab-sumup">

    <div class="gateway-top mb-4" style="border-radius:1rem;background:linear-gradient(135deg,#1a1a2e 0%,#0f3460 100%);color:#fff;padding:1.5rem;box-shadow:0 18px 42px rgba(15,52,96,.2)">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">
            <div class="pr-lg-4 mb-3 mb-lg-0">
                <div class="text-uppercase font-weight-bold mb-1" style="font-size:.72rem;letter-spacing:.14em;opacity:.8;">Pagamentos</div>
                <h3 class="mb-2 font-weight-bold">SumUp</h3>
                <p class="mb-3" style="opacity:.86;max-width:720px;">Configure as credenciais e opções do gateway SumUp para processar pagamentos.</p>
                <div class="d-flex flex-wrap" style="gap:.5rem;">
                    @php
                        $sumupEnv = $settings['sumup_env'] ?? 'sandbox';
                        $sumupEnabled = (int) ($settings['sumup_enabled'] ?? 0) === 1;
                        $sumupConfigured = !empty($settings['sumup_api_key']) && !empty($settings['sumup_merchant_code']);
                    @endphp
                    <span class="gateway-badge"><span class="gateway-dot"></span>{{ $sumupEnv === 'production' ? 'Producao' : 'Sandbox' }}</span>
                    <span class="gateway-badge"><span class="gateway-dot"></span>{{ $sumupConfigured ? 'Configurado' : 'Pendente' }}</span>
                    <span class="gateway-badge {{ $sumupEnabled ? 'badge-success' : 'badge-secondary' }}" id="sumupEnabledBadge"><span class="gateway-dot"></span>{{ $sumupEnabled ? 'Gateway ativo' : 'Gateway inativo' }}</span>
                </div>
            </div>
            <div class="bg-white rounded-lg p-3 text-dark" style="min-width:280px;max-width:320px;width:100%;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Status rapido</strong>
                    <div class="custom-control custom-switch mb-0">
                        <input type="hidden" name="sumup_enabled" value="0">
                        <input type="checkbox" class="custom-control-input gateway-enable-toggle" id="sumup_enabled" name="sumup_enabled" value="1" data-gateway="sumup" onchange="handleGatewayEnabledToggle(this)" {{ $sumupEnabled ? 'checked' : '' }}>
                        <label class="custom-control-label" for="sumup_enabled"></label>
                    </div>
                </div>
                <small class="text-muted d-block mb-3">Desligue para desativar o SumUp como gateway da plataforma.</small>
                <button type="button" class="btn btn-dark btn-block font-weight-bold" id="btn-test-sumup" onclick="testSumUpConnection()">
                    <i class="fas fa-plug mr-1"></i> Testar conexao
                </button>
            </div>
        </div>
    </div>

    <div class="card card-outline card-dark gateway-section mb-0">
        <div class="card-header d-flex justify-content-between align-items-center"
             style="background:linear-gradient(135deg,#1a1a2e 0%,#0f3460 100%);color:#fff;">
            <h3 class="card-title font-weight-bold mb-0">
                <i class="fas fa-cog mr-2"></i> Configurações SumUp
            </h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info small">
                <div class="font-weight-bold mb-2">
                    <i class="fas fa-info-circle mr-1"></i> Como preencher as credenciais SumUp
                </div>
                <ol class="mb-2 pl-3">
                    <li><strong>API Key:</strong> acesse <a href="https://me.sumup.com" target="_blank" rel="noopener noreferrer">me.sumup.com</a> &gt; perfil &gt; Settings &gt; For Developers &gt; Toolkit &gt; API Keys, crie uma chave secreta e cole aqui. Não use a SumUp Public Key.</li>
                    <li><strong>Merchant Code:</strong> use o código da mesma conta lojista. Se não souber, cole a API Key, clique em Testar Conexão e copie o Merchant Code retornado pela SumUp.</li>
                    <li><strong>Client ID e Client Secret:</strong> opcionais para OAuth. Pegue em For Developers &gt; OAuth Apps &gt; Create client secret e baixe o JSON das credenciais.</li>
                    <li><strong>Webhook Secret:</strong> preencha somente se você configurou assinatura HMAC nos webhooks da SumUp. Se ficar vazio, o sistema valida pela URL única da transação.</li>
                </ol>
                <a href="https://developer.sumup.com/tools/authorization/api-keys" target="_blank" rel="noopener noreferrer" class="font-weight-bold">
                    Abrir guia oficial de API Keys
                </a>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">API Key</label>
                    <div class="input-group">
                        <input type="password" name="sumup_api_key" id="sumup_api_key_legacy"
                            value="{{ $settings['sumup_api_key'] ?? '' }}"
                            class="form-control font-monospace" placeholder="sup_sk_••••••••••••••••••••••••••••••••">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="toggleGatewaySecret('sumup_api_key_legacy', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted">Chave secreta server-to-server criada em API Keys. Ela aparece uma única vez.</small>
                </div>
                <div class="col-md-6 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Merchant Code</label>
                    <input type="text" name="sumup_merchant_code"
                        value="{{ $settings['sumup_merchant_code'] ?? '' }}"
                        class="form-control font-monospace" placeholder="MXXXXXXXX">
                    <small class="text-muted">Código curto da conta lojista. Também aparece no retorno do teste de conexão.</small>
                </div>
                <div class="col-md-6 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Client ID (OAuth)</label>
                    <input type="text" name="sumup_client_id"
                        value="{{ $settings['sumup_client_id'] ?? '' }}"
                        class="form-control font-monospace" placeholder="com.sumup.app.xxxxxxxx">
                    <small class="text-muted">Opcional. Use apenas se a integração OAuth da SumUp estiver ativa.</small>
                </div>
                <div class="col-md-6 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Client Secret (OAuth)</label>
                    <div class="input-group">
                        <input type="password" name="sumup_client_secret" id="sumup_client_secret_legacy"
                            value="{{ $settings['sumup_client_secret'] ?? '' }}"
                            class="form-control font-monospace" placeholder="••••••••••••••••••••••••••••••••">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="toggleGatewaySecret('sumup_client_secret_legacy', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted">Baixado no JSON da credencial OAuth. Não é necessário para API Key simples.</small>
                </div>
                <div class="col-md-6 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Webhook Secret (HMAC)</label>
                    <div class="input-group">
                        <input type="password" name="sumup_webhook_secret" id="sumup_webhook_secret_legacy"
                            value="{{ $settings['sumup_webhook_secret'] ?? '' }}"
                            class="form-control font-monospace" placeholder="••••••••••••••••••••••••••••••••">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="toggleGatewaySecret('sumup_webhook_secret_legacy', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted">Opcional. Se preenchido, a assinatura enviada pela SumUp precisa bater com este segredo.</small>
                </div>
                <div class="col-md-6 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Ambiente</label>
                    <select name="sumup_env" class="form-control">
                        <option value="sandbox"    {{ ($settings['sumup_env'] ?? 'sandbox') === 'sandbox'    ? 'selected' : '' }}>Sandbox (testes)</option>
                        <option value="production" {{ ($settings['sumup_env'] ?? '') === 'production' ? 'selected' : '' }}>Produção (real)</option>
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Taxa Percentual (%)</label>
                    <input type="number" step="0.01" name="sumup_fee_percentage"
                        value="{{ $settings['sumup_fee_percentage'] ?? '2.75' }}"
                        class="form-control">
                </div>
                <div class="col-md-4 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Taxa Fixa (R$)</label>
                    <input type="number" step="0.01" name="sumup_fee_fixed"
                        value="{{ $settings['sumup_fee_fixed'] ?? '0.00' }}"
                        class="form-control">
                </div>
                <div class="col-md-4 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Repassar Taxa ao Comprador</label>
                    <select name="sumup_pass_fee" class="form-control">
                        <option value="0" {{ ($settings['sumup_pass_fee'] ?? 0) == 0 ? 'selected' : '' }}>Não — plataforma absorve</option>
                        <option value="1" {{ ($settings['sumup_pass_fee'] ?? 0) == 1 ? 'selected' : '' }}>Sim — comprador paga</option>
                    </select>
                </div>
                {{-- Parcelamento SumUp --}}
                <div class="col-12"><hr><h6 class="text-uppercase text-muted small font-weight-bold mb-3"><i class="fas fa-layer-group mr-1"></i> Parcelamento SumUp</h6></div>
                <div class="col-md-4 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Máx. Parcelas</label>
                    <input type="number" min="1" max="12" step="1" name="sumup_max_installments"
                        value="{{ $settings['sumup_max_installments'] ?? '12' }}"
                        class="form-control">
                    <small class="text-muted">1 = somente à vista</small>
                </div>
                <div class="col-md-4 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Parcelas sem Juros</label>
                    <input type="number" min="1" max="12" step="1" name="sumup_installments_no_interest"
                        value="{{ $settings['sumup_installments_no_interest'] ?? '1' }}"
                        class="form-control">
                    <small class="text-muted">Parcelas até este número não têm juros</small>
                </div>
                <div class="col-md-4 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Juros por Parcela (%)</label>
                    <div class="input-group">
                        <input type="number" min="0" max="99.99" step="0.01" name="sumup_installment_tax"
                            value="{{ $settings['sumup_installment_tax'] ?? '0.00' }}"
                            class="form-control">
                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                    </div>
                    <small class="text-muted">Aplicado a partir da {{ ($settings['sumup_installments_no_interest'] ?? 1) + 1 }}ª parcela</small>
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-dark btn-block font-weight-bold d-none" id="btn-test-sumup-legacy"
                        onclick="testSumUpConnectionLegacy()">
                        <i class="fas fa-plug mr-1"></i> Testar Conexão SumUp (Legacy)
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- FIM TAB SUMUP --}}
    </div>

</div>

@push('scripts')
<script>
    function gatewayToast(type, message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3200, timerProgressBar: true, icon: type, title: message });
            return;
        }
        if (typeof toastr !== 'undefined') {
            toastr[type === 'error' ? 'error' : 'success'](message);
        }
    }

    function switchMainGatewayTab(button) {
        const targetId = button.getAttribute('data-target');
        document.querySelectorAll('.main-gateway-tab').forEach((item) => {
            item.classList.remove('btn-primary');
            item.classList.add('btn-outline-secondary');
        });
        document.querySelectorAll('.main-gateway-pane').forEach((pane) => pane.classList.remove('is-active'));
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-primary');
        document.getElementById(targetId)?.classList.add('is-active');
    }

    function switchGatewayTab(button) {
        const targetId = button.getAttribute('data-target');
        document.querySelectorAll('.gateway-tab-btn').forEach((item) => {
            item.classList.remove('btn-primary');
            item.classList.add('btn-outline-secondary');
        });
        document.querySelectorAll('.gateway-pane').forEach((pane) => pane.classList.remove('is-active'));
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-primary');
        document.getElementById(targetId)?.classList.add('is-active');
    }

    function updateGatewayHeroState() {
        const env = document.getElementById('mercadopago_env')?.value || 'sandbox';
        const hero = document.getElementById('gatewayHero');
        const envBadge = document.getElementById('gatewayEnvBadge');
        const configBadge = document.getElementById('gatewayConfigBadge');
        const enabledInput = document.getElementById('mercadopago_enabled');
        const enabledBadge = document.getElementById('gatewayEnabledBadge');
        if (!hero || !envBadge || !configBadge || !enabledInput || !enabledBadge) return;

        const configured = env === 'production' ? hero.dataset.productionConfigured === '1' : hero.dataset.sandboxConfigured === '1';
        envBadge.innerHTML = '<span class="gateway-dot"></span>' + (env === 'production' ? 'Producao' : 'Sandbox');
        configBadge.innerHTML = '<span class="gateway-dot"></span>' + (configured ? 'Configurado' : 'Pendente');
        enabledBadge.innerHTML = '<span class="gateway-dot"></span>' + (enabledInput.checked ? 'Gateway ativo' : 'Gateway inativo');
    }

    function setGatewayEnvironment(env) {
        const envSelect = document.getElementById('mercadopago_env');
        if (!envSelect) return;
        envSelect.value = env;
        document.getElementById('gatewayEnvSandboxPanel')?.classList.toggle('d-none', env === 'production');
        document.getElementById('gatewayEnvProductionPanel')?.classList.toggle('d-none', env !== 'production');
        document.getElementById('gatewayEnvBtnSandbox')?.classList.toggle('active', env === 'sandbox');
        document.getElementById('gatewayEnvBtnProduction')?.classList.toggle('active', env === 'production');
        updateGatewayHeroState();
    }

    function toggleGatewaySecret(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');
        if (!input || !icon) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    }

    function copyGatewayWebhook() {
        const input = document.getElementById('gatewayWebhookUrl');
        if (!input) return;
        const text = input.value;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => gatewayToast('success', 'Webhook copiado com sucesso.'));
            return;
        }
        input.select();
        document.execCommand('copy');
        gatewayToast('success', 'Webhook copiado com sucesso.');
    }

    function syncGatewayColor(color) {
        const textInput = document.getElementById('gateway_checkout_primary_color_text');
        if (textInput) textInput.value = color;
    }

    function updateGatewayMethodCard(checkbox) {
        checkbox.closest('[data-method-card]')?.classList.toggle('is-enabled', checkbox.checked);
    }

    function toggleSetting(key, checked) {
        return fetch("{{ route('admin.settings.toggle') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ key: key, value: checked ? 1 : 0 })
        }).then((response) => response.json());
    }

    function handleGatewayEnabledToggle(checkbox) {
        const gatewayName = checkbox.getAttribute('data-gateway') || 'mercadopago';
        const isEnabling = checkbox.checked;
        
        // Se está ativando um gateway, verificar se outro está ativo
        if (isEnabling) {
            const otherGateway = gatewayName === 'mercadopago' ? 'sumup' : 'mercadopago';
            const otherCheckbox = document.querySelector(`input[data-gateway="${otherGateway}"]`) || document.getElementById(otherGateway + '_enabled');
            
            if (otherCheckbox && otherCheckbox.checked) {
                // Mostrar confirmação
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Atenção!',
                        html: `Apenas um gateway pode ficar ativo por vez.<br><br>Ativar <strong>${gatewayName === 'mercadopago' ? 'Mercado Pago' : 'SumUp'}</strong> irá desativar automaticamente <strong>${otherGateway === 'mercadopago' ? 'Mercado Pago' : 'SumUp'}</strong>.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sim, ativar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Desativar o outro gateway
                            otherCheckbox.checked = false;
                            updateGatewayBadge(otherGateway, false);
                            
                            // Ativar o gateway atual
                            updateGatewayBadge(gatewayName, true);
                            saveGatewayToggle(checkbox, gatewayName);
                        } else {
                            // Cancelou, reverter o checkbox
                            checkbox.checked = false;
                        }
                    });
                } else {
                    // Sem Swal, usar confirm nativo
                    const confirmed = confirm(`Apenas um gateway pode ficar ativo por vez.\n\nAtivar ${gatewayName === 'mercadopago' ? 'Mercado Pago' : 'SumUp'} irá desativar automaticamente ${otherGateway === 'mercadopago' ? 'Mercado Pago' : 'SumUp'}.\n\nDeseja continuar?`);
                    if (confirmed) {
                        otherCheckbox.checked = false;
                        updateGatewayBadge(otherGateway, false);
                        updateGatewayBadge(gatewayName, true);
                        saveGatewayToggle(checkbox, gatewayName);
                    } else {
                        checkbox.checked = false;
                    }
                }
                return;
            }
        }
        
        // Se está desativando ou não há conflito, apenas atualizar
        updateGatewayBadge(gatewayName, isEnabling);
        saveGatewayToggle(checkbox, gatewayName);
    }

    function updateGatewayBadge(gatewayName, isEnabled) {
        if (gatewayName === 'mercadopago') {
            updateGatewayHeroState();
        } else if (gatewayName === 'sumup') {
            const badge = document.getElementById('sumupEnabledBadge');
            if (badge) {
                badge.innerHTML = '<span class="gateway-dot"></span>' + (isEnabled ? 'Gateway ativo' : 'Gateway inativo');
                badge.classList.toggle('badge-success', isEnabled);
                badge.classList.toggle('badge-secondary', !isEnabled);
            }
        }
        
        // Atualizar badges nas abas principais
        updateMainTabBadges();
    }

    function updateMainTabBadges() {
        const mpEnabled = document.getElementById('mercadopago_enabled')?.checked || false;
        const sumupEnabled = document.getElementById('sumup_enabled')?.checked || false;
        
        // Atualizar badge do Mercado Pago
        const mpTab = document.querySelector('[data-target="main-tab-mercadopago"]');
        if (mpTab) {
            let mpBadge = mpTab.querySelector('.badge');
            if (mpEnabled) {
                if (!mpBadge) {
                    mpBadge = document.createElement('span');
                    mpBadge.className = 'badge badge-success ml-2';
                    mpTab.appendChild(mpBadge);
                }
                mpBadge.textContent = 'Ativo';
                mpBadge.className = 'badge badge-success ml-2';
            } else if (mpBadge) {
                mpBadge.remove();
            }
        }
        
        // Atualizar badge do SumUp
        const sumupTab = document.querySelector('[data-target="main-tab-sumup"]');
        if (sumupTab) {
            let sumupBadge = sumupTab.querySelector('.badge');
            if (sumupEnabled) {
                if (!sumupBadge) {
                    sumupBadge = document.createElement('span');
                    sumupBadge.className = 'badge badge-success ml-2';
                    sumupTab.appendChild(sumupBadge);
                }
                sumupBadge.textContent = 'Ativo';
                sumupBadge.className = 'badge badge-success ml-2';
            } else if (sumupBadge) {
                sumupBadge.remove();
            }
        }
    }

    function saveGatewayToggle(checkbox, gatewayName) {
        const settingKey = gatewayName === 'mercadopago' ? 'mercadopago_enabled' : 'sumup_enabled';
        toggleSetting(settingKey, checkbox.checked)
            .then((data) => {
                if (!data || !data.success) throw new Error((data && data.message) || 'Falha ao atualizar o gateway.');
                gatewayToast('success', 'Status do gateway atualizado.');
            })
            .catch((error) => {
                checkbox.checked = !checkbox.checked;
                updateGatewayBadge(gatewayName, checkbox.checked);
                gatewayToast('error', error.message || 'Nao foi possivel atualizar o gateway.');
            });
    }

    function testGatewayConnection() {
        const button = document.getElementById('btn-test-mercadopago');
        const env = document.getElementById('mercadopago_env')?.value || 'sandbox';
        const tokenField = env === 'production' ? document.getElementById('mercadopago_prod_access_token') : document.getElementById('mercadopago_sandbox_access_token');
        const token = tokenField ? tokenField.value.trim() : '';
        if (!token) {
            gatewayToast('error', 'Preencha o Access Token do ambiente ativo antes do teste.');
            return;
        }
        const originalHtml = button ? button.innerHTML : '';
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Testando conexao...';
        }
        fetch('{{ route("admin.settings.test_gateway") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ gateway: 'mercadopago', env: env, access_token: token })
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data || !data.success) throw new Error((data && data.message) || 'Falha ao validar a conexao.');
                const hero = document.getElementById('gatewayHero');
                if (hero) hero.dataset[env === 'production' ? 'productionConfigured' : 'sandboxConfigured'] = '1';
                updateGatewayHeroState();
                gatewayToast('success', data.message || 'Conexao validada com sucesso.');
            })
            .catch((error) => gatewayToast('error', error.message || 'Erro ao testar a conexao com o gateway.'))
            .finally(() => {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        setGatewayEnvironment(document.getElementById('mercadopago_env')?.value || 'sandbox');
        syncGatewayColor(document.getElementById('gateway_checkout_primary_color')?.value || '#1F5EDB');
        document.querySelectorAll('[data-method-card] input[type="checkbox"]').forEach((checkbox) => updateGatewayMethodCard(checkbox));
        updateMainTabBadges();
    });

    function testSumUpConnection() {
        const btn = document.getElementById('btn-test-sumup');
        const orig = btn.innerHTML;
        const token = document.getElementById('sumup_api_key_legacy')?.value || '';

        if (!token) {
            gatewayToast('error', 'Preencha a API Key do SumUp antes do teste.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Testando conexao...';

        fetch('{{ route("admin.settings.test_gateway") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ gateway: 'sumup', access_token: token, env: 'production' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                gatewayToast('success', data.message);
                btn.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Conexao OK';
            } else {
                gatewayToast('error', data.message);
                btn.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Falhou';
            }
            setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; }, 3500);
        })
        .catch(() => {
            gatewayToast('error', 'Erro ao testar conexao SumUp.');
            btn.innerHTML = orig;
            btn.disabled = false;
        });
    }

    function testSumUpConnectionLegacy() {
        const btn   = document.getElementById('btn-test-sumup-legacy');
        const orig  = btn.innerHTML;
        const token = document.getElementById('sumup_api_key_legacy')?.value || '';

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Testando...';

        fetch('{{ route("admin.settings.test_gateway") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ gateway: 'sumup', access_token: token, env: 'production' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                gatewayToast('success', data.message);
                btn.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Conexão OK';
            } else {
                gatewayToast('error', data.message);
                btn.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Falhou';
            }
            setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; }, 3500);
        })
        .catch(() => {
            gatewayToast('error', 'Erro ao testar conexão SumUp.');
            btn.innerHTML = orig;
            btn.disabled = false;
        });
    }
</script>
@endpush
