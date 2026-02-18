<div class="space-y-6">
    <div
        class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700/50 rounded-2xl p-4 flex items-start gap-3 shadow-sm transition-all">
        <i class="fas fa-credit-card text-blue-500 dark:text-blue-400 mt-1"></i>
        <div class="text-sm text-blue-800 dark:text-blue-100">
            Configure os métodos de pagamento aceitos na plataforma. Webhooks são essenciais para aprovação automática.
            <br>
            <strong>Nota:</strong> Configure URLs de Webhook no painel do seu gateway.
        </div>
    </div>

    <!-- MercadoPago -->
    <div class="border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden transition-colors">
        <div class="bg-slate-50 dark:bg-slate-950 px-6 py-4 flex items-center justify-between cursor-pointer"
            onclick="toggleCard('mercadopago_card')">
            <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-handshake text-blue-500"></i> MercadoPago
            </h3>
            <i class="fas fa-chevron-down text-slate-400 dark:text-slate-500 transition-transform"
                id="mercadopago_card_icon"></i>
        </div>
        <div id="mercadopago_card" class="hidden p-6 border-t border-slate-200 dark:border-slate-800">
            <div class="mb-6">
                <label
                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1 transition-colors">Ambiente
                    de
                    Execução</label>
                <select name="mercadopago_env"
                    class="gateway-env-select w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-800 dark:text-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium"
                    data-gateway="mercadopago">
                    <option value="sandbox" {{ ($settings['mercadopago_env'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Ambiente de Testes)</option>
                    <option value="production" {{ ($settings['mercadopago_env'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>Produção (Ambiente Real)</option>
                </select>
            </div>

            <!-- Sandbox -->
            <div
                class="env-sandbox mb-4 p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800">
                <h4 class="text-xs font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-4"><i
                        class="fas fa-tools mr-1"></i> Credenciais de Sandbox</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Public
                            Key
                            (Sandbox)</label>
                        <input type="text" name="mercadopago_sandbox_public_key"
                            value="{{ $settings['mercadopago_sandbox_public_key'] ?? '' }}"
                            class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label
                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Access
                            Token
                            (Sandbox)</label>
                        <input type="text" name="mercadopago_sandbox_access_token"
                            value="{{ $settings['mercadopago_sandbox_access_token'] ?? '' }}"
                            class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Production -->
            <div
                class="env-production mb-4 p-4 bg-green-50 dark:bg-green-900/10 rounded-2xl border border-green-200 dark:border-green-800/30 hidden">
                <h4 class="text-xs font-bold text-green-700 dark:text-green-500 uppercase tracking-wider mb-4"><i
                        class="fas fa-check-circle mr-1"></i> Credenciais de Produção</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Public
                            Key
                            (Produção)</label>
                        <input type="text" name="mercadopago_prod_public_key"
                            value="{{ $settings['mercadopago_prod_public_key'] ?? '' }}"
                            class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label
                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Access
                            Token
                            (Produção)</label>
                        <div class="flex gap-2">
                            <input type="text" name="mercadopago_prod_access_token"
                                value="{{ $settings['mercadopago_prod_access_token'] ?? '' }}"
                                class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                            <button type="button" onclick="testGatewayConnection('mercadopago')"
                                class="px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl font-bold hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors whitespace-nowrap">
                                <i class="fas fa-plug mr-2"></i> Testar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Webhook
                    URL</label>
                <div class="flex">
                    <input type="text" readonly value="{{ route('api.webhooks.mercadopago') }}"
                        class="w-full rounded-l-2xl border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-sm focus:outline-none transition-colors">
                    <button type="button" onclick="copyToClipboard('{{ route('api.webhooks.mercadopago') }}')"
                        class="bg-slate-100 dark:bg-slate-800 h-10 hover:bg-slate-200 dark:hover:bg-slate-750 border border-l-0 border-slate-200 dark:border-slate-800 rounded-r-2xl px-4 text-slate-600 dark:text-slate-300 font-medium transition-colors">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>

            {{-- Meios de Pagamento Aceitos --}}
            <div
                class="mt-6 p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800">
                <h4 class="text-xs font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-4">
                    <i class="fas fa-list-check mr-1"></i> Meios de Pagamento Aceitos
                </h4>
                <p class="text-xs text-slate-400 mb-3">Selecione quais métodos de pagamento estarão disponíveis no
                    checkout.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <!-- Cartão de Crédito -->
                    <label
                        class="relative flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-900 border-2 rounded-2xl cursor-pointer transition-all hover:bg-slate-50 dark:hover:bg-slate-800 group h-32 text-center {{ ($settings['mercadopago_method_credit_card'] ?? 1) ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-800' }}"
                        onclick="togglePaymentCard(this)">
                        <input type="hidden" name="mercadopago_method_credit_card" value="0">
                        <input type="checkbox" name="mercadopago_method_credit_card" value="1"
                            class="absolute top-3 right-3 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            {{ ($settings['mercadopago_method_credit_card'] ?? 1) ? 'checked' : '' }}>
                        <i
                            class="fas fa-credit-card text-3xl mb-2 {{ ($settings['mercadopago_method_credit_card'] ?? 1) ? 'text-blue-500' : 'text-slate-400' }}"></i>
                        <span
                            class="text-sm font-bold {{ ($settings['mercadopago_method_credit_card'] ?? 1) ? 'text-blue-700 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400' }}">Crédito</span>
                    </label>

                    <!-- Cartão de Débito -->
                    <label
                        class="relative flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-900 border-2 rounded-2xl cursor-pointer transition-all hover:bg-slate-50 dark:hover:bg-slate-800 group h-32 text-center {{ ($settings['mercadopago_method_debit_card'] ?? 0) ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-800' }}"
                        onclick="togglePaymentCard(this)">
                        <input type="hidden" name="mercadopago_method_debit_card" value="0">
                        <input type="checkbox" name="mercadopago_method_debit_card" value="1"
                            class="absolute top-3 right-3 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            {{ ($settings['mercadopago_method_debit_card'] ?? 0) ? 'checked' : '' }}>
                        <i
                            class="fas fa-credit-card text-3xl mb-2 {{ ($settings['mercadopago_method_debit_card'] ?? 0) ? 'text-blue-500' : 'text-slate-400' }}"></i>
                        <span
                            class="text-sm font-bold {{ ($settings['mercadopago_method_debit_card'] ?? 0) ? 'text-blue-700 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400' }}">Débito</span>
                    </label>

                    <!-- Pix -->
                    <label
                        class="relative flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-900 border-2 rounded-2xl cursor-pointer transition-all hover:bg-slate-50 dark:hover:bg-slate-800 group h-32 text-center {{ ($settings['mercadopago_method_pix'] ?? 1) ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-800' }}"
                        onclick="togglePaymentCard(this)">
                        <input type="hidden" name="mercadopago_method_pix" value="0">
                        <input type="checkbox" name="mercadopago_method_pix" value="1"
                            class="absolute top-3 right-3 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            {{ ($settings['mercadopago_method_pix'] ?? 1) ? 'checked' : '' }}>
                        <i
                            class="brands fa-pix text-3xl mb-2 {{ ($settings['mercadopago_method_pix'] ?? 1) ? 'text-blue-500' : 'text-slate-400' }}"></i>
                        <span
                            class="text-sm font-bold {{ ($settings['mercadopago_method_pix'] ?? 1) ? 'text-blue-700 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400' }}">Pix</span>
                    </label>

                    <!-- Boleto -->
                    <label
                        class="relative flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-900 border-2 rounded-2xl cursor-pointer transition-all hover:bg-slate-50 dark:hover:bg-slate-800 group h-32 text-center {{ ($settings['mercadopago_method_ticket'] ?? 0) ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-800' }}"
                        onclick="togglePaymentCard(this)">
                        <input type="hidden" name="mercadopago_method_ticket" value="0">
                        <input type="checkbox" name="mercadopago_method_ticket" value="1"
                            class="absolute top-3 right-3 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            {{ ($settings['mercadopago_method_ticket'] ?? 0) ? 'checked' : '' }}>
                        <i
                            class="fas fa-barcode text-3xl mb-2 {{ ($settings['mercadopago_method_ticket'] ?? 0) ? 'text-blue-500' : 'text-slate-400' }}"></i>
                        <span
                            class="text-sm font-bold {{ ($settings['mercadopago_method_ticket'] ?? 0) ? 'text-blue-700 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400' }}">Boleto</span>
                    </label>

                    <!-- Carteira MP -->
                    <label
                        class="relative flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-900 border-2 rounded-2xl cursor-pointer transition-all hover:bg-slate-50 dark:hover:bg-slate-800 group h-32 text-center {{ ($settings['mercadopago_method_mercadopago'] ?? 0) ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-800' }}"
                        onclick="togglePaymentCard(this)">
                        <input type="hidden" name="mercadopago_method_mercadopago" value="0">
                        <input type="checkbox" name="mercadopago_method_mercadopago" value="1"
                            class="absolute top-3 right-3 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            {{ ($settings['mercadopago_method_mercadopago'] ?? 0) ? 'checked' : '' }}>
                        <i
                            class="fas fa-wallet text-3xl mb-2 {{ ($settings['mercadopago_method_mercadopago'] ?? 0) ? 'text-blue-500' : 'text-slate-400' }}"></i>
                        <span
                            class="text-sm font-bold {{ ($settings['mercadopago_method_mercadopago'] ?? 0) ? 'text-blue-700 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400' }}">Carteira
                            MP</span>
                    </label>
                </div>
            </div>

            {{-- Customização do Checkout Transparente --}}
            <div
                class="mt-6 p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800">
                <h4 class="text-xs font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-4">
                    <i class="fas fa-palette mr-1"></i> Customização do Checkout Transparente
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Tema do
                            Checkout</label>
                        <select name="gateway_checkout_theme_selected"
                            class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium">
                            <option value="default" {{ ($settings['gateway_checkout_theme_selected'] ?? 'default') == 'default' ? 'selected' : '' }}>Padrão (Mercado Pago)</option>
                            <option value="dark" {{ ($settings['gateway_checkout_theme_selected'] ?? '') == 'dark' ? 'selected' : '' }}>Escuro (Dark)</option>
                            <option value="bootstrap" {{ ($settings['gateway_checkout_theme_selected'] ?? '') == 'bootstrap' ? 'selected' : '' }}>Bootstrap</option>
                            <option value="flat" {{ ($settings['gateway_checkout_theme_selected'] ?? '') == 'flat' ? 'selected' : '' }}>Flat (Moderno)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Cor Primária
                            (Botões e Destaques)</label>
                        <div class="flex gap-2">
                            <input type="color" id="panel_color_picker" name="gateway_checkout_primary_color_hex"
                                value="{{ $settings['gateway_checkout_primary_color_hex'] ?? '#1F5EDB' }}"
                                class="h-10 w-14 rounded-xl border-slate-200 dark:border-slate-800 cursor-pointer"
                                oninput="document.getElementById('panel_color_text').value = this.value">
                            <input type="text" id="panel_color_text"
                                value="{{ $settings['gateway_checkout_primary_color_hex'] ?? '#1F5EDB' }}"
                                class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-white font-medium"
                                readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PagSeguro -->
    <div class="border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden transition-colors">
        <div class="bg-slate-50 dark:bg-slate-950 px-6 py-4 flex items-center justify-between cursor-pointer"
            onclick="toggleCard('pagseguro_card')">
            <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-money-bill-wave text-green-500"></i> PagSeguro
            </h3>
            <i class="fas fa-chevron-down text-slate-400 dark:text-slate-500 transition-transform"
                id="pagseguro_card_icon"></i>
        </div>
        <div id="pagseguro_card" class="hidden p-6 border-t border-slate-200 dark:border-slate-800">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label
                        class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">E-mail
                        da
                        Conta</label>
                    <input type="email" name="pagseguro_email" value="{{ $settings['pagseguro_email'] ?? '' }}"
                        class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                </div>
                <div>
                    <label
                        class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Ambiente
                        de
                        Execução</label>
                    <select name="pagseguro_env"
                        class="gateway-env-select w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-white focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium"
                        data-gateway="pagseguro">
                        <option value="sandbox" {{ ($settings['pagseguro_env'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Testes)</option>
                        <option value="production" {{ ($settings['pagseguro_env'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>Produção</option>
                    </select>
                </div>
            </div>

            <div
                class="env-sandbox mb-4 p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800">
                <h4 class="text-xs font-bold text-slate-500 dark:text-slate-500 uppercase tracking-wider mb-4"><i
                        class="fas fa-tools mr-1"></i> Credenciais de Sandbox</h4>
                <div>
                    <label
                        class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Token
                        (Sandbox)</label>
                    <input type="text" name="pagseguro_sandbox_token"
                        value="{{ $settings['pagseguro_sandbox_token'] ?? '' }}"
                        class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                </div>
            </div>

            <div
                class="env-production mb-4 p-4 bg-green-50 dark:bg-green-900/10 rounded-2xl border border-green-200 dark:border-green-800/30 hidden">
                <h4 class="text-xs font-bold text-green-700 dark:text-green-500 uppercase tracking-wider mb-4"><i
                        class="fas fa-check-circle mr-1"></i> Credenciais de Produção</h4>
                <div>
                    <label
                        class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Token
                        (Produção)</label>
                    <div class="flex gap-2">
                        <input type="text" name="pagseguro_prod_token"
                            value="{{ $settings['pagseguro_prod_token'] ?? '' }}"
                            class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                        <button type="button" onclick="testGatewayConnection('pagseguro')"
                            class="px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl font-bold hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors whitespace-nowrap">
                            <i class="fas fa-plug mr-2"></i> Testar
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Webhook
                    URL</label>
                <div class="flex">
                    <input type="text" readonly value="{{ route('api.webhooks.pagseguro') }}"
                        class="w-full rounded-l-2xl border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-sm focus:outline-none transition-colors">
                    <button type="button" onclick="copyToClipboard('{{ route('api.webhooks.pagseguro') }}')"
                        class="bg-slate-100 dark:bg-slate-800 h-10 hover:bg-slate-200 dark:hover:bg-slate-750 border border-l-0 border-slate-200 dark:border-slate-800 rounded-r-2xl px-4 text-slate-600 dark:text-slate-300 font-medium transition-colors">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- General Billing Settings -->
    <div class="pt-6 border-t border-slate-100 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-sliders-h text-blue-500"></i> Configurações Gerais de Cobrança
        </h3>

        <div class="mb-6 flex items-start gap-3">
            <div class="flex h-5 items-center">
                <input type="hidden" name="gateway_transparent_checkout" value="0">
                <input id="gateway_transparent_checkout" name="gateway_transparent_checkout" type="checkbox" value="1"
                    class="h-4 w-4 rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 dark:bg-slate-900"
                    {{ ($settings['gateway_transparent_checkout'] ?? 0) ? 'checked' : '' }}>
            </div>
            <div class="text-sm">
                <label for="gateway_transparent_checkout"
                    class="font-bold text-slate-700 dark:text-slate-300 transition-colors">Habilitar
                    Checkout Transparente</label>
                <p class="text-slate-500 dark:text-slate-400 transition-colors">Se desativado, o usuário será
                    redirecionado para a página
                    de pagamento do gateway.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Juros
                    de Parcelamento (%
                    a.m.)</label>
                <div class="relative">
                    <input type="number" step="0.01" name="gateway_installment_tax"
                        value="{{ $settings['gateway_installment_tax'] ?? '0.00' }}"
                        class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white pr-8">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-slate-500 dark:text-slate-400">%</span>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Máx.
                    Parcelas sem
                    Juros</label>
                <input type="number" name="gateway_max_installments_no_interest"
                    value="{{ $settings['gateway_max_installments_no_interest'] ?? '1' }}"
                    class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div>
                <label
                    class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Repassar
                    Taxas ao
                    Cliente?</label>
                <select name="gateway_pass_tax_to_client"
                    class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    <option value="0" {{ ($settings['gateway_pass_tax_to_client'] ?? 0) == 0 ? 'selected' : '' }}>Não
                        (Empresa absorve)</option>
                    <option value="1" {{ ($settings['gateway_pass_tax_to_client'] ?? 0) == 1 ? 'selected' : '' }}>Sim
                        (Cliente paga)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Taxa da
                    Plataforma - Marketplace (%)</label>
                <div class="relative">
                    <input type="number" step="0.01" name="marketplace_fee"
                        value="{{ $settings['marketplace_fee'] ?? '10.00' }}"
                        class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white pr-8">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-slate-500 dark:text-slate-400">%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function toggleCard(id) {
            const el = document.getElementById(id);
            const icon = document.getElementById(id + '_icon');
            if (el.classList.contains('hidden')) {
                el.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                el.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        function togglePaymentCard(element) {
            // Evitar loop infinito se o clique foi direto no checkbox
            if (event.target.type === 'checkbox') return;

            const checkbox = element.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            
            updateCardVisual(element, checkbox.checked);
        }

        function updateCardVisual(element, isChecked) {
            const icon = element.querySelector('i');
            const text = element.querySelector('span');

            if (isChecked) {
                element.classList.remove('border-slate-200', 'dark:border-slate-800');
                element.classList.add('border-blue-500', 'bg-blue-50/50', 'dark:bg-blue-900/20');
                
                icon.classList.remove('text-slate-400');
                icon.classList.add('text-blue-500');
                
                text.classList.remove('text-slate-500', 'dark:text-slate-400');
                text.classList.add('text-blue-700', 'dark:text-blue-400');
            } else {
                element.classList.add('border-slate-200', 'dark:border-slate-800');
                element.classList.remove('border-blue-500', 'bg-blue-50/50', 'dark:bg-blue-900/20');
                
                icon.classList.add('text-slate-400');
                icon.classList.remove('text-blue-500');
                
                text.classList.add('text-slate-500', 'dark:text-slate-400');
                text.classList.remove('text-blue-700', 'dark:text-blue-400');
            }
        }
        
        // Inicializar listeners nos checkboxes para garantir sincronia se clicados diretamente
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[type="checkbox"][name^="mercadopago_method_"]').forEach(cb => {
                cb.addEventListener('change', function() {
                    updateCardVisual(this.closest('label'), this.checked);
                });
            });
        });

        function formatCurrency(input) {
            let value = input.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2) + '';
            value = value.replace(".", ",");
            value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
            input.value = value;
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function () {
                if (typeof toastr !== 'undefined') {
                    toastr.success('Copiado para a área de transferência!');
                } else {
                    alert('Copiado!');
                }
            }, function (err) {
                console.error('Could not copy text: ', err);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Init Gateway Environment UI
            document.querySelectorAll('.gateway-env-select').forEach(select => {
                select.addEventListener('change', function () {
                    const parent = this.closest('div[id$="_card"]'); // Find nearest Parent card content
                    const sandbox = parent.querySelector('.env-sandbox');
                    const production = parent.querySelector('.env-production');

                    if (this.value === 'sandbox') {
                        if (sandbox) sandbox.classList.remove('hidden');
                        if (production) production.classList.add('hidden');
                    } else {
                        if (sandbox) sandbox.classList.add('hidden');
                        if (production) production.classList.remove('hidden');
                    }
                });
                // Trigger initial state
                select.dispatchEvent(new Event('change'));
            });

            // Make testGatewayConnection global
            window.testGatewayConnection = function (gateway) {
                const btn = event.currentTarget;
                const originalContent = btn.innerHTML;
                const icon = btn.querySelector('i');

                // Loading state
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Testando...';

                // Get credentials based on gateway
                let data = {
                    gateway: gateway,
                    _token: '{{ csrf_token() }}'
                };

                if (gateway === 'mercadopago') {
                    data.access_token = document.querySelector('input[name="mercadopago_prod_access_token"]').value;
                    data.env = document.querySelector('select[name="mercadopago_env"]').value;
                    if (data.env === 'sandbox') {
                        data.access_token = document.querySelector('input[name="mercadopago_sandbox_access_token"]').value;
                    }
                } else if (gateway === 'pagseguro') {
                    data.token = document.querySelector('input[name="pagseguro_prod_token"]').value;
                    data.email = document.querySelector('input[name="pagseguro_email"]').value;
                    data.env = document.querySelector('select[name="pagseguro_env"]').value;
                    if (data.env === 'sandbox') {
                        data.token = document.querySelector('input[name="pagseguro_sandbox_token"]').value;
                    }
                }

                // Call test route
                fetch('{{ route("panel.admin.settings.test-gateway") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof toastr !== 'undefined') {
                                toastr.success(data.message);
                            } else {
                                alert('Sucesso: ' + data.message);
                            }
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(data.message);
                            } else {
                                alert('Erro: ' + data.message);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erro ao testar conexão. Verifique o console.');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                    });
            };
        });
    </script>
@endpush