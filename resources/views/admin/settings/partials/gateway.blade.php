<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-credit-card mr-2"></i> Configure os métodos de pagamento aceitos na plataforma. Webhooks são
        essenciais para aprovação automática.
    </div>

    {{-- MERCADO PAGO --}}
    <div class="card card-outline card-success collapsed-card mb-3">
        <div class="card-header">
            <h3 class="card-title font-weight-bold text-success"><i class="fas fa-handshake mr-2"></i> MercadoPago</h3>
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
                </div>
            </div>

            <div class="env-sandbox p-3 bg-light rounded border mb-3">
                <h6 class="text-muted mb-3"><i class="fas fa-tools mr-1"></i> Credenciais de Sandbox</h6>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Public Key (Sandbox)</label>
                        <input type="text" name="mercadopago_sandbox_public_key" class="form-control"
                            value="{{ $settings['mercadopago_sandbox_public_key'] ?? '' }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Access Token (Sandbox)</label>
                        <input type="text" name="mercadopago_sandbox_access_token" class="form-control"
                            value="{{ $settings['mercadopago_sandbox_access_token'] ?? '' }}">
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
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Access Token (Produção)</label>
                        <input type="text" name="mercadopago_prod_access_token" class="form-control"
                            value="{{ $settings['mercadopago_prod_access_token'] ?? '' }}">
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
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Client Secret</label>
                        <input type="password" name="mercadopago_client_secret" class="form-control"
                            value="{{ $settings['mercadopago_client_secret'] ?? '' }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Webhook URL (Copie e cole no painel do MercadoPago)</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-white" readonly
                        value="{{ route('api.webhooks.mercadopago') }}">
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
        <div class="card-header">
            <h3 class="card-title font-weight-bold text-warning"><i class="fas fa-money-bill-wave mr-2"></i> PagSeguro
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>E-mail da Conta</label>
                    <input type="email" name="pagseguro_email" class="form-control"
                        value="{{ $settings['pagseguro_email'] ?? '' }}">
                </div>
                <div class="col-md-6 form-group">
                    <label>Ambiente de Execução</label>
                    <select name="pagseguro_env" class="form-control gateway-env-select" data-gateway="pagseguro">
                        <option value="sandbox" {{ ($settings['pagseguro_env'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Testes)</option>
                        <option value="production" {{ ($settings['pagseguro_env'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>Produção</option>
                    </select>
                </div>
            </div>

            <div class="env-sandbox p-3 bg-light rounded border mb-3">
                <h6 class="text-muted mb-3"><i class="fas fa-tools mr-1"></i> Credenciais de Sandbox</h6>
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label>Token (Sandbox)</label>
                        <input type="text" name="pagseguro_sandbox_token" class="form-control"
                            value="{{ $settings['pagseguro_sandbox_token'] ?? '' }}">
                    </div>
                </div>
            </div>

            <div class="env-production p-3 bg-light rounded border mb-3">
                <h6 class="text-warning mb-3"><i class="fas fa-check-circle mr-1"></i> Credenciais de Produção</h6>
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label>Token (Produção)</label>
                        <input type="text" name="pagseguro_prod_token" class="form-control"
                            value="{{ $settings['pagseguro_prod_token'] ?? '' }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Webhook URL (Notificações)</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-white" readonly
                        value="{{ route('api.webhooks.pagseguro') }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button"
                            onclick="navigator.clipboard.writeText(this.parentElement.previousElementSibling.value); toastr.success('Copiado!')"><i
                                class="fas fa-copy"></i> Copiar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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