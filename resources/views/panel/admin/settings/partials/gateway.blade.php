<div class="space-y-6">
    <div
        class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-2xl p-4 flex items-start gap-3">
        <i class="fas fa-credit-card text-blue-500 mt-1"></i>
        <div class="text-sm text-blue-800 dark:text-blue-300">
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
                        <input type="text" name="mercadopago_prod_access_token"
                            value="{{ $settings['mercadopago_prod_access_token'] ?? '' }}"
                            class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
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
                    <input type="text" name="pagseguro_prod_token" value="{{ $settings['pagseguro_prod_token'] ?? '' }}"
                        class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
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

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function () {
                // Check if toastr is defined, otherwise use alert or custom toast
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
        });
    </script>
@endpush