<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-credit-card mr-2"></i> Configure os métodos de pagamento aceitos na plataforma. Webhooks são
        essenciais para aprovação automática.
    </div>



    {{-- MERCADO PAGO --}}
    <div class="card card-outline card-success collapsed-card mb-3">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title font-weight-bold text-success"><i class="fas fa-handshake mr-2"></i> MercadoPago</h3>

            <div class="ml-auto mr-3">
                @php
                    $mpEnv = $settings['mercadopago_env'] ?? 'sandbox';
                    $hasMP = $mpEnv == 'sandbox'
                        ? !empty($settings['mercadopago_sandbox_access_token'])
                        : !empty($settings['mercadopago_prod_access_token']);
                @endphp

                @if($mpEnv == 'sandbox')
                    <span class="badge badge-warning"><i class="fas fa-flask mr-1"></i> Sandbox</span>
                @else
                    <span class="badge badge-success"><i class="fas fa-rocket mr-1"></i> Produção</span>
                @endif

                @if($hasMP)
                    <span class="badge badge-primary"><i class="fas fa-check mr-1"></i> Configurado</span>
                @else
                    <span class="badge badge-secondary"><i class="fas fa-clock mr-1"></i> Pendente</span>
                @endif
            </div>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label>Ambiente de Execução</label>
                    <select name="mercadopago_env" class="form-control gateway-env-select" data-gateway="mercadopago">
                        <option value="sandbox" {{ ($settings['mercadopago_env'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Ambiente de Testes)</option>
                        <option value="production" {{ ($settings['mercadopago_env'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>Produção (Ambiente Real)</option>
                    </select>
                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle mr-1"></i> Use <b>Sandbox</b>
                        para testar o fluxo de pagamento sem gastar dinheiro real.</small>
                </div>
            </div>

            <div class="env-sandbox p-3 bg-light rounded border mb-3">
                <h6 class="text-muted mb-3"><i class="fas fa-tools mr-1"></i> Credenciais de Sandbox</h6>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Public Key (Sandbox)</label>
                        <input type="text" name="mercadopago_sandbox_public_key" class="form-control"
                            value="{{ $settings['mercadopago_sandbox_public_key'] ?? '' }}">
                        <small class="text-muted">Encontre em: <a
                                href="https://www.mercadopago.com.br/developers/panel/credentials"
                                target="_blank">Minhas Aplicações > Sua App > Credenciais de Teste</a></small>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Access Token (Sandbox)</label>
                        <input type="text" name="mercadopago_sandbox_access_token" class="form-control"
                            value="{{ $settings['mercadopago_sandbox_access_token'] ?? '' }}">
                        <small class="text-muted">Usado para autorizar transações de teste.</small>
                    </div>
                </div>
            </div>

            <div class="env-production p-3 bg-light rounded border mb-3">
                <h6 class="text-success mb-3"><i class="fas fa-check-circle mr-1"></i> Credenciais de Produção</h6>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Public Key (Produção)</label>
                        <input type="text" name="mercadopago_prod_public_key" class="form-control"
                            value="{{ $settings['mercadopago_prod_public_key'] ?? '' }}">
                        <small class="text-muted">Encontre em: <a
                                href="https://www.mercadopago.com.br/developers/panel/credentials"
                                target="_blank">Credenciais de Produção</a></small>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Access Token (Produção)</label>
                        <input type="text" name="mercadopago_prod_access_token" class="form-control"
                            value="{{ $settings['mercadopago_prod_access_token'] ?? '' }}">
                        <small class="text-muted"><b>Cuidado:</b> Chave de acesso real à sua conta.</small>
                    </div>
                </div>
            </div>

            <div class="env-auth p-3 bg-white rounded border border-primary mb-3">
                <h6 class="text-primary mb-3"><i class="fas fa-key mr-1"></i> Credenciais de Aplicativo (OAuth)</h6>
                <p class="text-sm text-muted mb-3">Necessário para permitir que vendedores conectem suas contas
                    automaticamente.</p>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Client ID (App ID)</label>
                        <input type="text" name="mercadopago_client_id" class="form-control"
                            value="{{ $settings['mercadopago_client_id'] ?? '' }}">
                        <small class="text-muted">Obtenha em: <a
                                href="https://www.mercadopago.com.br/developers/panel/applications" target="_blank">Menu
                                Lateral > Aplicação > Detalhes</a></small>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Client Secret</label>
                        <input type="password" name="mercadopago_client_secret" class="form-control"
                            value="{{ $settings['mercadopago_client_secret'] ?? '' }}">
                        <small class="text-muted">Configuração <b>obrigatória</b> para o Split de Pagamento.</small>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white rounded border mb-3">
                <h6 class="text-dark mb-3"><i class="fas fa-list-check mr-1"></i> Meios de Pagamento Aceitos
                    (Plataforma)</h6>
                <p class="text-sm text-muted mb-3">Selecione quais métodos de pagamento estarão disponíveis no checkout
                    para os cursos e planos da plataforma.</p>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <div class="custom-control custom-checkbox border p-2 rounded">
                            <input type="hidden" name="mercadopago_method_credit_card" value="0">
                            <input type="checkbox" class="custom-control-input" id="method_credit_card"
                                name="mercadopago_method_credit_card" value="1" {{ ($settings['mercadopago_method_credit_card'] ?? 1) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="method_credit_card">Cartão de
                                Crédito</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="custom-control custom-checkbox border p-2 rounded">
                            <input type="hidden" name="mercadopago_method_debit_card" value="0">
                            <input type="checkbox" class="custom-control-input" id="method_debit_card"
                                name="mercadopago_method_debit_card" value="1" {{ ($settings['mercadopago_method_debit_card'] ?? 0) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="method_debit_card">Cartão de
                                Débito</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="custom-control custom-checkbox border p-2 rounded">
                            <input type="hidden" name="mercadopago_method_pix" value="0">
                            <input type="checkbox" class="custom-control-input" id="method_pix"
                                name="mercadopago_method_pix" value="1" {{ ($settings['mercadopago_method_pix'] ?? 1) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="method_pix">Pix</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="custom-control custom-checkbox border p-2 rounded">
                            <input type="hidden" name="mercadopago_method_ticket" value="0">
                            <input type="checkbox" class="custom-control-input" id="method_ticket"
                                name="mercadopago_method_ticket" value="1" {{ ($settings['mercadopago_method_ticket'] ?? 0) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="method_ticket">Boleto
                                (Ticket)</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="custom-control custom-checkbox border p-2 rounded">
                            <input type="hidden" name="mercadopago_method_mercadopago" value="0">
                            <input type="checkbox" class="custom-control-input" id="method_mercadopago"
                                name="mercadopago_method_mercadopago" value="1" {{ ($settings['mercadopago_method_mercadopago'] ?? 0) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="method_mercadopago">Carteira
                                Mercado Pago</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-white rounded border mb-3">
                <h6 class="text-dark mb-3"><i class="fas fa-magic mr-1"></i> Customização do Checkout Transparente</h6>
                <p class="text-sm text-muted mb-3">Personalize a aparência do checkout para combinar com sua marca.</p>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Tema do Checkout</label>
                        <select name="gateway_checkout_theme" class="form-control">
                            <option value="default" {{ ($settings['gateway_checkout_theme'] ?? 'default') == 'default' ? 'selected' : '' }}>Padrão (Mercado Pago)</option>
                            <option value="dark" {{ ($settings['gateway_checkout_theme'] ?? '') == 'dark' ? 'selected' : '' }}>Escuro (Dark)</option>
                            <option value="bootstrap" {{ ($settings['gateway_checkout_theme'] ?? '') == 'bootstrap' ? 'selected' : '' }}>Bootstrap</option>
                            <option value="flat" {{ ($settings['gateway_checkout_theme'] ?? '') == 'flat' ? 'selected' : '' }}>Flat (Moderno)</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Cor Primária (Botões e Destaques)</label>
                        <div class="input-group">
                            <input type="color" id="gateway_color_picker" name="gateway_checkout_primary_color"
                                class="form-control form-control-color"
                                value="{{ $settings['gateway_checkout_primary_color'] ?? '#1F5EDB' }}"
                                style="max-width: 60px; height: 38px;"
                                oninput="document.getElementById('gateway_color_text').value = this.value">
                            <input type="text" id="gateway_color_text" class="form-control"
                                value="{{ $settings['gateway_checkout_primary_color'] ?? '#1F5EDB' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Botão de Teste Mercado Pago -->
    <div class="mt-3">
        <button type="button" class="btn btn-outline-success" id="btn-test-mercadopago">
            <i class="fas fa-plug mr-1"></i> Testar Conexão Mercado Pago
        </button>
        <span id="msg-test-mercadopago" class="ml-2 text-sm font-weight-bold"></span>
    </div>

    <div class="form-group mt-4">
        <label>Webhook URL (Copie e cole no painel do MercadoPago)</label>
        <div class="input-group">
            <input type="text" class="form-control bg-white" readonly value="{{ route('api.webhooks.mercadopago') }}">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button"
                    onclick="navigator.clipboard.writeText(this.parentElement.previousElementSibling.value); toastr.success('Copiado!')"><i
                        class="fas fa-copy"></i> Copiar</button>
            </div>
        </div>
    </div>
</div>
</div>

{{-- PAGSEGURO --}}
<div class="card card-outline card-warning collapsed-card mb-4">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title font-weight-bold text-warning"><i class="fas fa-money-bill-wave mr-2"></i> PagSeguro</h3>

        <div class="ml-auto mr-3">
            @php
                $psEnv = $settings['pagseguro_env'] ?? 'sandbox';
                $hasPS = $psEnv == 'sandbox'
                    ? !empty($settings['pagseguro_sandbox_token'])
                    : !empty($settings['pagseguro_prod_token']);
            @endphp

            @if($psEnv == 'sandbox')
                <span class="badge badge-warning"><i class="fas fa-flask mr-1"></i> Sandbox</span>
            @else
                <span class="badge badge-success"><i class="fas fa-rocket mr-1"></i> Produção</span>
            @endif

            @if($hasPS)
                <span class="badge badge-primary"><i class="fas fa-check mr-1"></i> Configurado</span>
            @else
                <span class="badge badge-secondary"><i class="fas fa-clock mr-1"></i> Pendente</span>
            @endif
        </div>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    <div class="card-body" style="display: none;">
        <div class="row">
            <div class="col-md-6 form-group">
                <label>E-mail da Conta</label>
                <input type="email" name="pagseguro_email" id="pagseguro_email" class="form-control"
                    value="{{ $settings['pagseguro_email'] ?? '' }}">
            </div>
            <div class="col-md-6 form-group">
                <label>Ambiente de Execução</label>
                <select name="pagseguro_env" id="pagseguro_env" class="form-control gateway-env-select"
                    data-gateway="pagseguro">
                    <option value="sandbox" {{ ($settings['pagseguro_env'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Testes)</option>
                    <option value="production" {{ ($settings['pagseguro_env'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>Produção</option>
                </select>
            </div>
        </div>

        <div id="pagseguro-sandbox-container" class="env-sandbox p-3 bg-light rounded border mb-3">
            <h6 class="text-muted mb-3"><i class="fas fa-tools mr-1"></i> Credenciais de Sandbox</h6>
            <div class="row">
                <div class="col-md-12 form-group">
                    <label>Token (Sandbox)</label>
                    <input type="text" name="pagseguro_sandbox_token" id="pagseguro_sandbox_token" class="form-control"
                        value="{{ $settings['pagseguro_sandbox_token'] ?? '' }}">
                    <small class="text-muted">Encontre em: <a
                            href="https://pagseguro.uol.com.br/preferencias/integracoes.jhtml" target="_blank">Venda
                            Online > Integrações</a> no painel Sandbox.</small>
                </div>
            </div>
        </div>

        <div id="pagseguro-production-container" class="env-production p-3 bg-light rounded border mb-3">
            <h6 class="text-warning mb-3"><i class="fas fa-check-circle mr-1"></i> Credenciais de Produção</h6>
            <div class="row">
                <div class="col-md-12 form-group">
                    <label>Token (Produção)</label>
                    <input type="text" name="pagseguro_prod_token" id="pagseguro_prod_token" class="form-control"
                        value="{{ $settings['pagseguro_prod_token'] ?? '' }}">
                    <small class="text-muted">Encontre em: <a
                            href="https://pagseguro.uol.com.br/preferencias/integracoes.jhtml" target="_blank">Perfis de
                            Integração > Token de Integração</a></small>
                </div>
            </div>
        </div>

        <!-- Botão de Teste PagSeguro -->
        <div class="mt-3">
            <button type="button" class="btn btn-outline-warning" id="btn-test-pagseguro">
                <i class="fas fa-plug mr-1"></i> Testar Conexão PagSeguro
            </button>
            <span id="msg-test-pagseguro" class="ml-2 text-sm font-weight-bold"></span>
        </div>

        <div class="form-group mt-4">
            <label>Webhook URL (Notificações)</label>
            <div class="input-group">
                <input type="text" class="form-control bg-white" readonly value="{{ route('api.webhooks.pagseguro') }}">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button"
                        onclick="navigator.clipboard.writeText(this.parentElement.previousElementSibling.value); toastr.success('Copiado!')"><i
                            class="fas fa-copy"></i> Copiar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- MERCADO PAGO ENV TOGGLE ---
        const mpEnvSelect = document.querySelector('select[name="mercadopago_env"]');
        const mpSandboxDiv = document.querySelector('.card-success .env-sandbox');
        const mpProdDiv = document.querySelector('.card-success .env-production');

        function toggleMpEnv() {
            if (mpEnvSelect.value === 'sandbox') {
                mpSandboxDiv.style.display = 'block';
                mpProdDiv.style.display = 'none';
            } else {
                mpSandboxDiv.style.display = 'none';
                mpProdDiv.style.display = 'block';
            }
        }
        if (mpEnvSelect) {
            mpEnvSelect.addEventListener('change', toggleMpEnv);
            toggleMpEnv(); // init
        }

        // --- MERCADO PAGO TEST ---
        document.getElementById('btn-test-mercadopago').addEventListener('click', function () {
            const btn = this;
            const msg = document.getElementById('msg-test-mercadopago');
            const isSandbox = mpEnvSelect.value === 'sandbox';

            // Get visible token
            let token = '';
            if (isSandbox) {
                token = document.querySelector('input[name="mercadopago_sandbox_access_token"]').value;
            } else {
                token = document.querySelector('input[name="mercadopago_prod_access_token"]').value;
            }

            if (!token) {
                toastr.error('Preencha o Access Token antes de testar.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Testando...';
            msg.textContent = '';
            msg.className = 'ml-2 text-sm font-weight-bold';

            fetch('{{ route("admin.settings.test_gateway") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    gateway: 'mercadopago',
                    access_token: token,
                    env: mpEnvSelect.value
                })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        msg.textContent = 'Sucesso: ' + data.message;
                        msg.classList.add('text-success');
                    } else {
                        toastr.error(data.message);
                        msg.textContent = 'Erro: ' + data.message;
                        msg.classList.add('text-danger');
                    }
                })
                .catch(err => {
                    toastr.error('Erro ao testar conexão.');
                    console.error(err);
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-plug mr-1"></i> Testar Conexão Mercado Pago';
                });
        });


        // --- PAGSEGURO ENV TOGGLE ---
        const psEnvSelect = document.getElementById('pagseguro_env');
        const psSandboxContainer = document.getElementById('pagseguro-sandbox-container');
        const psProdContainer = document.getElementById('pagseguro-production-container');

        function togglePsEnv() {
            if (psEnvSelect.value === 'sandbox') {
                psSandboxContainer.style.display = 'block';
                psProdContainer.style.display = 'none';
            } else {
                psSandboxContainer.style.display = 'none';
                psProdContainer.style.display = 'block';
            }
        }
        if (psEnvSelect) {
            psEnvSelect.addEventListener('change', togglePsEnv);
            togglePsEnv();
        }

        // --- PAGSEGURO TEST ---
        document.getElementById('btn-test-pagseguro').addEventListener('click', function () {
            const btn = this;
            const msg = document.getElementById('msg-test-pagseguro');
            const isSandbox = psEnvSelect.value === 'sandbox';
            const email = document.getElementById('pagseguro_email').value;

            let token = '';
            if (isSandbox) {
                token = document.getElementById('pagseguro_sandbox_token').value;
            } else {
                token = document.getElementById('pagseguro_prod_token').value;
            }

            if (!token || !email) {
                toastr.error('Preencha Email e Token antes de testar.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Testando...';
            msg.textContent = '';
            msg.className = 'ml-2 text-sm font-weight-bold';

            fetch('{{ route("admin.settings.test_gateway") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    gateway: 'pagseguro',
                    email: email,
                    token: token,
                    env: psEnvSelect.value
                })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        msg.textContent = 'Sucesso: ' + data.message;
                        msg.classList.add('text-success');
                    } else {
                        toastr.error(data.message);
                        msg.textContent = 'Erro: ' + data.message;
                        msg.classList.add('text-danger');
                    }
                })
                .catch(err => {
                    toastr.error('Erro ao testar conexão.');
                    console.error(err);
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-plug mr-1"></i> Testar Conexão PagSeguro';
                });
        });

    });
</script>

<hr class="my-4">

<h5 class="text-primary mb-3"><i class="fas fa-sliders-h mr-2"></i> Configurações Gerais de Cobrança</h5>

<div class="form-group mb-4">
    <div class="custom-control custom-switch custom-switch-lg custom-switch-off-danger custom-switch-on-success">
        <input type="hidden" name="gateway_transparent_checkout" value="0">
        <input type="checkbox" class="custom-control-input" id="gateway_transparent_checkout"
            name="gateway_transparent_checkout" value="1" {{ ($settings['gateway_transparent_checkout'] ?? 0) ? 'checked' : '' }}>
        <label class="custom-control-label font-weight-bold" for="gateway_transparent_checkout">Habilitar Checkout
            Transparente (Manter usuário no site)</label>
    </div>
    <small class="form-text text-muted ml-5">Se desativado, o usuário será redirecionado para a página de pagamento
        do gateway.</small>
</div>

<div class="row">
    <div class="col-md-4 form-group">
        <label>Juros de Parcelamento (% a.m.)</label>
        <div class="input-group">
            <input type="number" step="0.01" name="gateway_installment_tax" class="form-control"
                value="{{ $settings['gateway_installment_tax'] ?? '0.00' }}">
            <div class="input-group-append">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <small class="text-muted">Deixe 0.00 para usar configuração do gateway.</small>
    </div>
    <div class="col-md-4 form-group">
        <label>Máx. Parcelas sem Juros</label>
        <input type="number" name="gateway_max_installments_no_interest" class="form-control"
            value="{{ $settings['gateway_max_installments_no_interest'] ?? '1' }}">
    </div>
    <div class="col-md-4 form-group">
        <label>Repassar Taxas ao Cliente?</label>
        <select name="gateway_pass_tax_to_client" class="form-control">
            <option value="0" {{ ($settings['gateway_pass_tax_to_client'] ?? 0) == 0 ? 'selected' : '' }}>Não (Empresa
                absorve taxas)</option>
            <option value="1" {{ ($settings['gateway_pass_tax_to_client'] ?? 0) == 1 ? 'selected' : '' }}>Sim (Cliente
                paga taxas)</option>
        </select>
    </div>
</div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gateway Environment Toggle
            $('.gateway-env-select').change(function () {
                var env = $(this).val();
                var cardBody = $(this).closest('.card-body');
                if (env === 'sandbox') {
                    cardBody.find('.env-sandbox').show();
                    cardBody.find('.env-production').hide();
                } else {
                    cardBody.find('.env-sandbox').hide();
                    cardBody.find('.env-production').show();
                }
            }).trigger('change');
        });
    </script>
@endpush