<div class="card-body">
    <h5 class="text-primary mb-3"><i class="fas fa-credit-card mr-2"></i>Gateways de Pagamento</h5>
    <p class="text-muted">Configure credenciais e opções dos gateways suportados. <b>Webhooks</b> são
        URLs
        que você deve configurar no painel do gateway para receber atualizações de pagamento.</p>

    {{-- MERCADO PAGO --}}
    <div class="card card-outline card-success collapsed-card">
        <div class="card-header">
            <h3 class="card-title">MercadoPago</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <div class="form-group">
                <label>Ambiente</label>
                <select name="mercadopago_env" class="form-control gateway-env-select" data-gateway="mercadopago">
                    <option value="sandbox" {{ ($settings['mercadopago_env'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Testes)</option>
                    <option value="production" {{ ($settings['mercadopago_env'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>Produção</option>
                </select>
            </div>
            <div class="env-sandbox">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Public Key (Sandbox)</label>
                        <input name="mercadopago_sandbox_public_key" class="form-control"
                            value="{{ $settings['mercadopago_sandbox_public_key'] ?? '' }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Access Token (Sandbox)</label>
                        <input name="mercadopago_sandbox_access_token" class="form-control"
                            value="{{ $settings['mercadopago_sandbox_access_token'] ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="env-production">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Public Key (Produção)</label>
                        <input name="mercadopago_prod_public_key" class="form-control"
                            value="{{ $settings['mercadopago_prod_public_key'] ?? '' }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Access Token (Produção)</label>
                        <input name="mercadopago_prod_access_token" class="form-control"
                            value="{{ $settings['mercadopago_prod_access_token'] ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Webhook URL (Copie e cole no painel do MP)</label>
                <div class="input-group">
                    <input type="text" class="form-control" readonly value="{{ route('api.webhooks.mercadopago') }}">
                    <div class="input-group-append">
                        <button class="btn btn-default" type="button"
                            onclick="navigator.clipboard.writeText(this.parentElement.previousElementSibling.value); toastr.success('Copiado!')"><i
                                class="fas fa-copy"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PAGSEGURO --}}
    <div class="card card-outline card-warning collapsed-card">
        <div class="card-header">
            <h3 class="card-title">PagSeguro</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <div class="form-group">
                <label>Ambiente</label>
                <select name="pagseguro_env" class="form-control gateway-env-select" data-gateway="pagseguro">
                    <option value="sandbox" {{ ($settings['pagseguro_env'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Testes)</option>
                    <option value="production" {{ ($settings['pagseguro_env'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>Produção</option>
                </select>
            </div>
            <div class="env-sandbox">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Email</label>
                        <input name="pagseguro_email" class="form-control"
                            value="{{ $settings['pagseguro_email'] ?? '' }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Token (Sandbox)</label>
                        <input name="pagseguro_sandbox_token" class="form-control"
                            value="{{ $settings['pagseguro_sandbox_token'] ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="env-production">
                <div class="row">
                    <div class="col-md-6 offset-md-6 form-group">
                        <label>Token (Produção)</label>
                        <input name="pagseguro_prod_token" class="form-control"
                            value="{{ $settings['pagseguro_prod_token'] ?? '' }}">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Webhook URL (Notificações)</label>
                <div class="input-group">
                    <input type="text" class="form-control" readonly value="{{ route('api.webhooks.pagseguro') }}">
                    <div class="input-group-append">
                        <button class="btn btn-default" type="button"
                            onclick="navigator.clipboard.writeText(this.parentElement.previousElementSibling.value); toastr.success('Copiado!')"><i
                                class="fas fa-copy"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>
    <h5 class="text-primary mb-3"><i class="fas fa-sliders-h mr-2"></i>Opções Gerais de Pagamento</h5>
    <div class="row">
        <div class="col-md-12">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                <input type="hidden" name="gateway_transparent_checkout" value="0">
                <input type="checkbox" class="custom-control-input" id="gateway_transparent_checkout"
                    name="gateway_transparent_checkout" value="1" {{ ($settings['gateway_transparent_checkout'] ?? 0) ? 'checked' : '' }}>
                <label class="custom-control-label" for="gateway_transparent_checkout">Checkout
                    Transparente
                    (Manter usuário no site)</label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 form-group">
            <label>Juros de Parcelamento (% a.m.)</label>
            <input type="number" step="0.01" name="gateway_installment_tax" class="form-control"
                value="{{ $settings['gateway_installment_tax'] ?? '0.00' }}">
            <small class="text-muted">Se 0, assume sem juros (ou config do gateway).</small>
        </div>
        <div class="col-md-4 form-group">
            <label>Max. Parcelas sem Juros</label>
            <input type="number" name="gateway_max_installments_no_interest" class="form-control"
                value="{{ $settings['gateway_max_installments_no_interest'] ?? '1' }}">
        </div>
        <div class="col-md-4">
            <label>Repassar Taxas ao Cliente?</label>
            <select name="gateway_pass_tax_to_client" class="form-control">
                <option value="0" {{ ($settings['gateway_pass_tax_to_client'] ?? 0) == 0 ? 'selected' : '' }}>
                    Não (Absorver taxas)</option>
                <option value="1" {{ ($settings['gateway_pass_tax_to_client'] ?? 0) == 1 ? 'selected' : '' }}>
                    Sim (Acrescer ao total)</option>
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