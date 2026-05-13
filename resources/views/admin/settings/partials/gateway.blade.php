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
                        <label class="text-uppercase text-muted small font-weight-bold">Taxa de Juros por Parcela (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="99.99" name="mercadopago_installment_tax" class="form-control" value="{{ $settings['mercadopago_installment_tax'] ?? $settings['gateway_installment_tax'] ?? '0.00' }}">
                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="text-uppercase text-muted small font-weight-bold">Máximo de Parcelas</label>
                        <input type="number" min="1" max="12" name="mercadopago_max_installments" class="form-control" value="{{ $settings['mercadopago_max_installments'] ?? '12' }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="text-uppercase text-muted small font-weight-bold">Parcelas sem Juros</label>
                        <input type="number" min="1" max="12" name="mercadopago_installments_no_interest" class="form-control" value="{{ $settings['mercadopago_installments_no_interest'] ?? '1' }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="text-uppercase text-muted small font-weight-bold">Repassar taxa ao cliente</label>
                        <select name="gateway_pass_tax_to_client" class="form-control">
                            <option value="0" {{ (int) ($settings['gateway_pass_tax_to_client'] ?? 0) === 0 ? 'selected' : '' }}>Não - empresa absorve</option>
                            <option value="1" {{ (int) ($settings['gateway_pass_tax_to_client'] ?? 0) === 1 ? 'selected' : '' }}>Sim - cliente paga</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="text-uppercase text-muted small font-weight-bold">Expiração do PIX (minutos)</label>
                        <div class="input-group">
                            <input type="number" name="mercadopago_pix_expiration_minutes" class="form-control" value="{{ $settings['mercadopago_pix_expiration_minutes'] ?? $settings['pix_expiration_minutes'] ?? '10' }}" min="1" max="1440">
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

        {{-- Permissões por tipo de usuário --}}
        <div class="card card-outline card-info gateway-section mt-3">
            <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-users mr-2"></i>Permissões por Tipo de Usuário</h3></div>
            <div class="card-body">
                <div class="row">
                    @foreach([
                        ['name' => 'mercadopago_allow_members', 'label' => 'Membros'],
                        ['name' => 'mercadopago_allow_instructors', 'label' => 'Instrutores'],
                        ['name' => 'mercadopago_allow_sellers', 'label' => 'Vendedores'],
                        ['name' => 'mercadopago_allow_mentors', 'label' => 'Mentores'],
                    ] as $perm)
                        <div class="col-md-3">
                            <div class="card bg-light border">
                                <div class="card-body text-center">
                                    <h6 class="font-weight-bold">{{ $perm['label'] }}</h6>
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="{{ $perm['name'] }}" value="0">
                                        <input type="checkbox" class="custom-control-input" id="{{ $perm['name'] }}" name="{{ $perm['name'] }}" value="1" {{ (int) ($settings[$perm['name']] ?? 1) === 1 ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="{{ $perm['name'] }}"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Permissões por tipo de produto --}}
        <div class="card card-outline card-info gateway-section mt-3">
            <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-shopping-cart mr-2"></i>Permissões por Tipo de Produto</h3></div>
            <div class="card-body">
                <div class="row">
                    @foreach([
                        ['name' => 'mercadopago_allow_courses', 'label' => 'Cursos'],
                        ['name' => 'mercadopago_allow_mentorships', 'label' => 'Mentorias'],
                        ['name' => 'mercadopago_allow_events', 'label' => 'Eventos'],
                        ['name' => 'mercadopago_allow_marketplace', 'label' => 'Marketplace'],
                        ['name' => 'mercadopago_allow_subscriptions', 'label' => 'Assinaturas'],
                        ['name' => 'mercadopago_allow_services', 'label' => 'Serviços'],
                    ] as $perm)
                        <div class="col-md-2">
                            <div class="card bg-light border">
                                <div class="card-body text-center">
                                    <h6 class="font-weight-bold small">{{ $perm['label'] }}</h6>
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="{{ $perm['name'] }}" value="0">
                                        <input type="checkbox" class="custom-control-input" id="{{ $perm['name'] }}" name="{{ $perm['name'] }}" value="1" {{ (int) ($settings[$perm['name']] ?? 1) === 1 ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="{{ $perm['name'] }}"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    </div>

    {{-- FIM TAB MERCADO PAGO --}}

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
            </div>

            {{-- Ambiente --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="text-uppercase text-muted small font-weight-bold">Ambiente</label>
                    <select name="sumup_env" class="form-control">
                        <option value="sandbox" {{ ($settings['sumup_env'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testes)</option>
                        <option value="production" {{ ($settings['sumup_env'] ?? 'sandbox') === 'production' ? 'selected' : '' }}>Produção (Real)</option>
                    </select>
                </div>
            </div>

            {{-- Credenciais básicas --}}
            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">API Key <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="sumup_api_key" id="sumup_api_key" value="{{ $settings['sumup_api_key'] ?? '' }}" class="form-control" placeholder="sup_sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleGatewaySecret('sumup_api_key', this)"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Merchant Code <span class="text-danger">*</span></label>
                    <input type="text" name="sumup_merchant_code" value="{{ $settings['sumup_merchant_code'] ?? '' }}" class="form-control" placeholder="MXXXXXXX">
                </div>
            </div>

            {{-- OAuth (opcional) --}}
            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Client ID (OAuth)</label>
                    <input type="text" name="sumup_client_id" value="{{ $settings['sumup_client_id'] ?? '' }}" class="form-control" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                </div>
                <div class="col-md-6 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Client Secret (OAuth)</label>
                    <div class="input-group">
                        <input type="password" name="sumup_client_secret" id="sumup_client_secret" value="{{ $settings['sumup_client_secret'] ?? '' }}" class="form-control" placeholder="••••••••••••••••••••••••••••••••">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleGatewaySecret('sumup_client_secret', this)"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Webhook --}}
            <div class="row">
                <div class="col-md-8 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Webhook URL</label>
                    <div class="input-group">
                        <input type="text" class="form-control" readonly value="{{ route('api.webhooks.sumup') }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ route('api.webhooks.sumup') }}')"><i class="fas fa-copy mr-1"></i>Copiar</button>
                        </div>
                    </div>
                    <small class="text-muted">Cole esta URL no painel SumUp para receber notificações de pagamento.</small>
                </div>
                <div class="col-md-4 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Webhook Secret</label>
                    <div class="input-group">
                        <input type="password" name="sumup_webhook_secret" id="sumup_webhook_secret" value="{{ $settings['sumup_webhook_secret'] ?? '' }}" class="form-control" placeholder="Opcional">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleGatewaySecret('sumup_webhook_secret', this)"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            {{-- Métodos de pagamento SumUp --}}
            <h5 class="font-weight-bold mb-3"><i class="fas fa-credit-card mr-2"></i>Métodos de Pagamento</h5>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-light border">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="mr-3" style="width:40px;height:40px;border-radius:10px;background:#1e40af;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-credit-card text-white"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-0">Cartão de Crédito</h6>
                                    <small class="text-muted">Via SumUp.js (tokenizado)</small>
                                </div>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="sumup_method_card" value="0">
                                <input type="checkbox" class="custom-control-input" id="sumup_method_card" name="sumup_method_card" value="1" {{ (int) ($settings['sumup_method_card'] ?? 1) === 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sumup_method_card"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="mr-3" style="width:40px;height:40px;border-radius:10px;background:#0d9488;display:flex;align-items:center;justify-content:center;">
                                    <i class="fa-brands fa-pix text-white"></i>
                                </div>
                                <div>
                                    <h6 class="font-weight-bold mb-0">PIX</h6>
                                    <small class="text-muted">QR Code inline</small>
                                </div>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="sumup_method_pix" value="0">
                                <input type="checkbox" class="custom-control-input" id="sumup_method_pix" name="sumup_method_pix" value="1" {{ (int) ($settings['sumup_method_pix'] ?? 1) === 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sumup_method_pix"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            {{-- Configurações de cobrança --}}
            <h5 class="font-weight-bold mb-3"><i class="fas fa-sliders-h mr-2"></i>Configurações de Cobrança</h5>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Taxa Percentual (%)</label>
                    <input type="number" step="0.01" name="sumup_fee_percentage" class="form-control" value="{{ $settings['sumup_fee_percentage'] ?? '2.75' }}">
                </div>
                <div class="col-md-3 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Taxa Fixa (R$)</label>
                    <input type="number" step="0.01" name="sumup_fee_fixed" class="form-control" value="{{ $settings['sumup_fee_fixed'] ?? '0.00' }}">
                </div>
                <div class="col-md-3 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Máx. Parcelas</label>
                    <input type="number" min="1" max="12" name="sumup_max_installments" class="form-control" value="{{ $settings['sumup_max_installments'] ?? '12' }}">
                </div>
                <div class="col-md-3 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Repassar Taxa</label>
                    <select name="sumup_pass_fee" class="form-control">
                        <option value="0" {{ (int) ($settings['sumup_pass_fee'] ?? 0) === 0 ? 'selected' : '' }}>Não - empresa absorve</option>
                        <option value="1" {{ (int) ($settings['sumup_pass_fee'] ?? 0) === 1 ? 'selected' : '' }}>Sim - cliente paga</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Parcelas sem Juros</label>
                    <input type="number" min="1" max="12" name="sumup_installments_no_interest" class="form-control"
                        value="{{ $settings['sumup_installments_no_interest'] ?? '1' }}">
                </div>
                <div class="col-md-3 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Juros por Parcela (%)</label>
                    <input type="number" step="0.01" name="sumup_installment_tax" class="form-control"
                        value="{{ $settings['sumup_installment_tax'] ?? '0.00' }}">
                </div>
                <div class="col-md-3 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Tipo de Cálculo</label>
                    <select name="sumup_interest_type" class="form-control">
                        <option value="per_installment" {{ ($settings['sumup_interest_type'] ?? 'per_installment') === 'per_installment' ? 'selected' : '' }}>Por parcela</option>
                        <option value="on_total" {{ ($settings['sumup_interest_type'] ?? '') === 'on_total' ? 'selected' : '' }}>Sobre o total</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label class="text-uppercase text-muted small font-weight-bold">Expiração do PIX (minutos)</label>
                    <input type="number" min="1" max="1440" name="sumup_pix_expiration_minutes" class="form-control"
                        value="{{ $settings['sumup_pix_expiration_minutes'] ?? '10' }}">
                </div>
            </div>

            <hr>

            {{-- Permissões por tipo de usuário --}}
            <h5 class="font-weight-bold mb-3"><i class="fas fa-users mr-2"></i>Permissões por Tipo de Usuário</h5>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="card bg-light border">
                        <div class="card-body text-center">
                            <h6 class="font-weight-bold">Membros</h6>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="sumup_allow_members" value="0">
                                <input type="checkbox" class="custom-control-input" id="sumup_allow_members" name="sumup_allow_members" value="1" {{ (int) ($settings['sumup_allow_members'] ?? 1) === 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sumup_allow_members"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border">
                        <div class="card-body text-center">
                            <h6 class="font-weight-bold">Instrutores</h6>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="sumup_allow_instructors" value="0">
                                <input type="checkbox" class="custom-control-input" id="sumup_allow_instructors" name="sumup_allow_instructors" value="1" {{ (int) ($settings['sumup_allow_instructors'] ?? 1) === 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sumup_allow_instructors"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border">
                        <div class="card-body text-center">
                            <h6 class="font-weight-bold">Vendedores</h6>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="sumup_allow_sellers" value="0">
                                <input type="checkbox" class="custom-control-input" id="sumup_allow_sellers" name="sumup_allow_sellers" value="1" {{ (int) ($settings['sumup_allow_sellers'] ?? 1) === 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sumup_allow_sellers"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border">
                        <div class="card-body text-center">
                            <h6 class="font-weight-bold">Mentores</h6>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="sumup_allow_mentors" value="0">
                                <input type="checkbox" class="custom-control-input" id="sumup_allow_mentors" name="sumup_allow_mentors" value="1" {{ (int) ($settings['sumup_allow_mentors'] ?? 1) === 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sumup_allow_mentors"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            {{-- Permissões por tipo de produto --}}
            <h5 class="font-weight-bold mb-3"><i class="fas fa-shopping-cart mr-2"></i>Permissões por Tipo de Produto</h5>
            
            <div class="row">
                <div class="col-md-2">
                    <div class="card bg-light border">
                        <div class="card-body text-center">
                            <h6 class="font-weight-bold small">Cursos</h6>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="sumup_allow_courses" value="0">
                                <input type="checkbox" class="custom-control-input" id="sumup_allow_courses" name="sumup_allow_courses" value="1" {{ (int) ($settings['sumup_allow_courses'] ?? 1) === 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sumup_allow_courses"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-light border">
                        <div class="card-body text-center">
                            <h6 class="font-weight-bold small">Mentorias</h6>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="sumup_allow_mentorships" value="0">
                                <input type="checkbox" class="custom-control-input" id="sumup_allow_mentorships" name="sumup_allow_mentorships" value="1" {{ (int) ($settings['sumup_allow_mentorships'] ?? 1) === 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sumup_allow_mentorships"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-light border">
                        <div class="card-body text-center">
                            <h6 class="font-weight-bold small">Eventos</h6>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="sumup_allow_events" value="0">
                                <input type="checkbox" class="custom-control-input" id="sumup_allow_events" name="sumup_allow_events" value="1" {{ (int) ($settings['sumup_allow_events'] ?? 1) === 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sumup_allow_events"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-light border">
                        <div class="card-body text-center">
                            <h6 class="font-weight-bold small">Marketplace</h6>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="sumup_allow_marketplace" value="0">
                                <input type="checkbox" class="custom-control-input" id="sumup_allow_marketplace" name="sumup_allow_marketplace" value="1" {{ (int) ($settings['sumup_allow_marketplace'] ?? 1) === 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sumup_allow_marketplace"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-light border">
                        <div class="card-body text-center">
                            <h6 class="font-weight-bold small">Assinaturas</h6>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="sumup_allow_subscriptions" value="0">
                                <input type="checkbox" class="custom-control-input" id="sumup_allow_subscriptions" name="sumup_allow_subscriptions" value="1" {{ (int) ($settings['sumup_allow_subscriptions'] ?? 1) === 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sumup_allow_subscriptions"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-light border">
                        <div class="card-body text-center">
                            <h6 class="font-weight-bold small">Serviços</h6>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="sumup_allow_services" value="0">
                                <input type="checkbox" class="custom-control-input" id="sumup_allow_services" name="sumup_allow_services" value="1" {{ (int) ($settings['sumup_allow_services'] ?? 1) === 1 ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sumup_allow_services"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>

    {{-- FIM TAB SUMUP --}}

</div>

@push('scripts')
<script>
function switchMainGatewayTab(btn) {
    // Remove active from all main tabs
    document.querySelectorAll('.main-gateway-tab').forEach(tab => {
        tab.classList.remove('btn-primary');
        tab.classList.add('btn-outline-secondary');
    });
    
    // Add active to clicked tab
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-primary');
    
    // Hide all main panes
    document.querySelectorAll('.main-gateway-pane').forEach(pane => {
        pane.classList.remove('is-active');
    });
    
    // Show target pane
    const targetId = btn.getAttribute('data-target');
    const targetPane = document.getElementById(targetId);
    if (targetPane) {
        targetPane.classList.add('is-active');
    }
}

function switchGatewayTab(btn) {
    // Remove active from all gateway tabs
    document.querySelectorAll('.gateway-tab-btn').forEach(tab => {
        tab.classList.remove('btn-primary');
        tab.classList.add('btn-outline-secondary');
    });
    
    // Add active to clicked tab
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-primary');
    
    // Hide all gateway panes
    document.querySelectorAll('.gateway-pane').forEach(pane => {
        pane.classList.remove('is-active');
    });
    
    // Show target pane
    const targetId = btn.getAttribute('data-target');
    const targetPane = document.getElementById(targetId);
    if (targetPane) {
        targetPane.classList.add('is-active');
    }
}

function setGatewayEnvironment(env) {
    // Update buttons
    const sandboxBtn = document.getElementById('gatewayEnvBtnSandbox');
    const productionBtn = document.getElementById('gatewayEnvBtnProduction');
    
    if (env === 'sandbox') {
        sandboxBtn.classList.add('active');
        productionBtn.classList.remove('active');
        document.getElementById('gatewayEnvSandboxPanel').classList.remove('d-none');
        document.getElementById('gatewayEnvProductionPanel').classList.add('d-none');
    } else {
        productionBtn.classList.add('active');
        sandboxBtn.classList.remove('active');
        document.getElementById('gatewayEnvProductionPanel').classList.remove('d-none');
        document.getElementById('gatewayEnvSandboxPanel').classList.add('d-none');
    }
    
    // Update select
    document.getElementById('mercadopago_env').value = env;
    
    // Update badges
    updateGatewayBadges();
}

function toggleGatewaySecret(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon = btn.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function copyGatewayWebhook() {
    const webhookUrl = document.getElementById('gatewayWebhookUrl');
    webhookUrl.select();
    document.execCommand('copy');
    
    // Show feedback
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check mr-1"></i>Copiado!';
    btn.classList.add('btn-success');
    btn.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
    }, 2000);
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show feedback
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-secondary');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    });
}

function updateGatewayMethodCard(checkbox) {
    const card = checkbox.closest('.gateway-method-card');
    if (checkbox.checked) {
        card.classList.add('is-enabled');
    } else {
        card.classList.remove('is-enabled');
    }
}

function handleGatewayEnabledToggle(checkbox) {
    const gateway = checkbox.getAttribute('data-gateway');
    const badge = document.getElementById(gateway + 'EnabledBadge');
    
    if (checkbox.checked) {
        badge.classList.remove('badge-secondary');
        badge.classList.add('badge-success');
        badge.innerHTML = '<span class="gateway-dot"></span>Gateway ativo';
    } else {
        badge.classList.remove('badge-success');
        badge.classList.add('badge-secondary');
        badge.innerHTML = '<span class="gateway-dot"></span>Gateway inativo';
    }
}

function testGatewayConnection() {
    const btn = document.getElementById('btn-test-mercadopago');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Testando...';
    btn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Conexão OK!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-primary');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            btn.disabled = false;
        }, 3000);
    }, 2000);
}

function testSumUpConnection() {
    const btn = document.getElementById('btn-test-sumup');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Testando...';
    btn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Conexão OK!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-dark');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-dark');
            btn.disabled = false;
        }, 3000);
    }, 2000);
}

function syncGatewayColor(color) {
    document.getElementById('gateway_checkout_primary_color_text').value = color;
}

function updateGatewayBadges() {
    // Update environment badge
    const env = document.getElementById('mercadopago_env').value;
    const envBadge = document.getElementById('gatewayEnvBadge');
    envBadge.innerHTML = '<span class="gateway-dot"></span>' + (env === 'production' ? 'Producao' : 'Sandbox');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set initial color sync
    const colorInput = document.getElementById('gateway_checkout_primary_color');
    if (colorInput) {
        syncGatewayColor(colorInput.value);
    }
});
</script>
@endpush