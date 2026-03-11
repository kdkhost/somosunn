<div class="space-y-6">

    {{-- ===== SEÇÃO 1: MERCADOPAGO — GATEWAY PRINCIPAL ===== --}}
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                <i class="fas fa-handshake text-blue-600 dark:text-blue-400"></i>
            </div>
            MercadoPago
        </h3>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="hidden" name="mercadopago_enabled" value="0">
            <input type="checkbox" class="sr-only peer" name="mercadopago_enabled" value="1"
                onchange="toggleSetting('mercadopago_enabled', this.checked)" {{ ($settings['mercadopago_enabled'] ?? 1) ? 'checked' : '' }}>
            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
        </label>
    </div>

    {{-- Ambiente --}}
    <div>
        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Ambiente de Execução</label>
        <select name="mercadopago_env"
            class="gateway-env-select w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-slate-800 dark:text-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium"
            data-gateway="mercadopago">
            <option value="sandbox" {{ ($settings['mercadopago_env'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Ambiente de Testes)</option>
            <option value="production" {{ ($settings['mercadopago_env'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>Produção (Ambiente Real)</option>
        </select>
    </div>

    {{-- Credenciais Sandbox --}}
    <div class="env-sandbox p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800" id="mercadopago_card">
        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">
            <i class="fas fa-tools mr-1"></i> Credenciais de Sandbox
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Public Key (Sandbox)</label>
                <input type="text" name="mercadopago_sandbox_public_key"
                    value="{{ $settings['mercadopago_sandbox_public_key'] ?? '' }}"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Access Token (Sandbox)</label>
                <input type="text" name="mercadopago_sandbox_access_token"
                    value="{{ $settings['mercadopago_sandbox_access_token'] ?? '' }}"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
        </div>
    </div>

    {{-- Credenciais Produção --}}
    <div class="env-production p-4 bg-green-50 dark:bg-green-900/10 rounded-2xl border border-green-200 dark:border-green-800/30 hidden">
        <h4 class="text-xs font-bold text-green-700 dark:text-green-500 uppercase tracking-wider mb-4">
            <i class="fas fa-check-circle mr-1"></i> Credenciais de Produção
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Public Key (Produção)</label>
                <input type="text" name="mercadopago_prod_public_key"
                    value="{{ $settings['mercadopago_prod_public_key'] ?? '' }}"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Access Token (Produção)</label>
                <div class="flex gap-2">
                    <input type="text" name="mercadopago_prod_access_token"
                        value="{{ $settings['mercadopago_prod_access_token'] ?? '' }}"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    <button type="button" onclick="testGatewayConnection('mercadopago')"
                        class="px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl font-bold hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors whitespace-nowrap">
                        <i class="fas fa-plug mr-2"></i> Testar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Webhook --}}
    <div>
        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Webhook URL</label>
        <div class="flex">
            <input type="text" readonly value="{{ route('api.webhooks.mercadopago') }}"
                class="w-full px-4 py-2 rounded-l-2xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-sm focus:outline-none">
            <button type="button" onclick="copyToClipboard('{{ route('api.webhooks.mercadopago') }}')"
                class="bg-slate-100 dark:bg-slate-800 h-10 hover:bg-slate-200 dark:hover:bg-slate-750 border border-l-0 border-slate-200 dark:border-slate-800 rounded-r-2xl px-4 text-slate-600 dark:text-slate-300 font-medium transition-colors">
                <i class="fas fa-copy"></i>
            </button>
        </div>
        <p class="text-[10px] text-slate-400 mt-1">Copie e cole no painel do MercadoPago.</p>
    </div>

    {{-- ===== SEÇÃO 2: MEIOS DE PAGAMENTO ===== --}}
    <div class="pt-5 border-t border-slate-100 dark:border-slate-800">
        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
            <i class="fas fa-list-check mr-1"></i> Meios de Pagamento Aceitos (Checkout)
        </h4>
        <p class="text-xs text-slate-400 mb-4 font-medium">Ative ou desative os métodos disponíveis no checkout.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @php
                $methods = [
                    ['name' => 'mercadopago_method_credit_card', 'label' => 'Cartão de Crédito', 'icon' => 'fa-credit-card', 'color' => 'blue', 'default' => 1],
                    ['name' => 'mercadopago_method_debit_card', 'label' => 'Cartão de Débito', 'icon' => 'fa-credit-card', 'color' => 'blue', 'default' => 0],
                    ['name' => 'mercadopago_method_pix', 'label' => 'Pix', 'icon' => 'fa-pix', 'color' => 'green', 'default' => 1, 'brand' => true],
                    ['name' => 'mercadopago_method_ticket', 'label' => 'Boleto (Ticket)', 'icon' => 'fa-barcode', 'color' => 'orange', 'default' => 0],
                    ['name' => 'mercadopago_method_mercadopago', 'label' => 'Carteira MP', 'icon' => 'fa-wallet', 'color' => 'blue', 'default' => 0],
                ];
            @endphp
            @foreach($methods as $m)
                <div class="flex items-center justify-between p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-9 h-9 rounded-full bg-{{ $m['color'] }}-50 dark:bg-{{ $m['color'] }}-900/20 flex items-center justify-center text-{{ $m['color'] }}-500">
                            <i class="{{ !empty($m['brand']) ? 'fa-brands' : 'fas' }} {{ $m['icon'] }} text-sm"></i>
                        </div>
                        <span class="font-bold text-slate-800 dark:text-white text-sm">{{ $m['label'] }}</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="{{ $m['name'] }}" value="0">
                        <input type="checkbox" class="sr-only peer" name="{{ $m['name'] }}" value="1"
                            onchange="toggleSetting('{{ $m['name'] }}', this.checked)" {{ ($settings[$m['name']] ?? $m['default']) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-{{ $m['color'] }}-300 dark:peer-focus:ring-{{ $m['color'] }}-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-{{ $m['color'] }}-600"></div>
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ===== SEÇÃO 3: OAUTH / MARKETPLACE ===== --}}
    <div class="pt-5 border-t border-slate-100 dark:border-slate-800">
        <div class="p-4 bg-blue-50 dark:bg-blue-900/10 rounded-2xl border border-blue-200 dark:border-blue-800/30">
            <h4 class="text-xs font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider mb-1 flex items-center gap-2">
                <i class="fas fa-key"></i> Credenciais de Aplicativo (OAuth)
            </h4>
            <p class="text-xs text-slate-400 mb-4">Necessário para vendedores conectarem via OAuth e habilitarem split de pagamento.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Client ID (App ID)</label>
                    <input type="text" name="mercadopago_client_id"
                        value="{{ $settings['mercadopago_client_id'] ?? '' }}"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    <p class="text-[10px] text-slate-400 mt-1">Obtenha em: <a href="https://www.mercadopago.com.br/developers/panel/applications" target="_blank" class="text-blue-500 hover:underline">Painel MP → Aplicação → Detalhes</a></p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Client Secret</label>
                    <input type="password" name="mercadopago_client_secret"
                        value="{{ $settings['mercadopago_client_secret'] ?? '' }}"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    <p class="text-[10px] text-red-500 mt-1 font-bold">Obrigatório para Split de Pagamento (OAuth marketplace).</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SEÇÃO 4: CONFIGURAÇÕES GERAIS DE COBRANÇA ===== --}}
    <div class="pt-5 border-t border-slate-100 dark:border-slate-800">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-sliders-h text-blue-500"></i> Configurações Gerais de Cobrança
        </h3>

        <div class="mb-5 flex items-start gap-3">
            <div class="flex h-5 items-center">
                <input type="hidden" name="gateway_transparent_checkout" value="0">
                <input id="gateway_transparent_checkout" name="gateway_transparent_checkout" type="checkbox" value="1"
                    class="h-4 w-4 rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 dark:bg-slate-900"
                    {{ ($settings['gateway_transparent_checkout'] ?? 0) ? 'checked' : '' }}>
            </div>
            <div class="text-sm">
                <label for="gateway_transparent_checkout" class="font-bold text-slate-700 dark:text-slate-300">Habilitar Checkout Transparente (Manter usuário no site)</label>
                <p class="text-slate-500 dark:text-slate-400">Se desativado, o usuário será redirecionado para a página de pagamento do gateway.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Juros de Parcelamento (% a.m.)</label>
                <div class="relative">
                    <input type="number" step="0.01" name="gateway_installment_tax"
                        value="{{ $settings['gateway_installment_tax'] ?? '0.00' }}"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white pr-8">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-slate-500 dark:text-slate-400">%</span>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Deixe 0.00 para usar configuração do gateway.</p>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Máx. Parcelas sem Juros</label>
                <input type="number" name="gateway_max_installments_no_interest"
                    value="{{ $settings['gateway_max_installments_no_interest'] ?? '1' }}"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Repassar Taxas ao Cliente?</label>
                <select name="gateway_pass_tax_to_client"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    <option value="0" {{ ($settings['gateway_pass_tax_to_client'] ?? 0) == 0 ? 'selected' : '' }}>Não (Empresa absorve)</option>
                    <option value="1" {{ ($settings['gateway_pass_tax_to_client'] ?? 0) == 1 ? 'selected' : '' }}>Sim (Cliente paga)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Taxa Marketplace (%)</label>
                <div class="relative">
                    <input type="number" step="0.01" name="marketplace_fee"
                        value="{{ $settings['marketplace_fee'] ?? '10.00' }}"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white pr-8">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-slate-500 dark:text-slate-400">%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SEÇÃO 5: CUSTOMIZAÇÃO DO CHECKOUT ===== --}}
    <div class="pt-5 border-t border-slate-100 dark:border-slate-800">
        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 flex items-center gap-2">
            <i class="fas fa-magic"></i> Customização do Checkout
        </h4>
        <p class="text-xs text-slate-400 mb-4">Personalize a aparência do checkout para combinar com sua marca.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Tema do Checkout</label>
                <select name="gateway_checkout_theme"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium">
                    <option value="default" {{ ($settings['gateway_checkout_theme'] ?? 'default') == 'default' ? 'selected' : '' }}>Padrão (Mercado Pago)</option>
                    <option value="dark" {{ ($settings['gateway_checkout_theme'] ?? '') == 'dark' ? 'selected' : '' }}>Escuro (Dark)</option>
                    <option value="bootstrap" {{ ($settings['gateway_checkout_theme'] ?? '') == 'bootstrap' ? 'selected' : '' }}>Bootstrap</option>
                    <option value="flat" {{ ($settings['gateway_checkout_theme'] ?? '') == 'flat' ? 'selected' : '' }}>Flat (Moderno)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Cor Primária (Botões)</label>
                <div class="flex gap-2">
                    <input type="color" name="gateway_checkout_primary_color"
                        value="{{ $settings['gateway_checkout_primary_color'] ?? '#1F5EDB' }}"
                        class="h-12 w-14 rounded-xl border border-slate-200 dark:border-slate-800 cursor-pointer"
                        oninput="document.getElementById('gateway_color_text').value = this.value">
                    <input type="text" id="gateway_color_text"
                        value="{{ $settings['gateway_checkout_primary_color'] ?? '#1F5EDB' }}" readonly
                        class="flex-1 px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-sm font-mono outline-none">
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SEÇÃO 6: AVANÇADO (Colapsável) ===== --}}
    <div class="pt-5 border-t border-slate-100 dark:border-slate-800">
        <div class="cursor-pointer flex items-center justify-between mb-3" onclick="toggleCard('advanced_gateway')">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-cog"></i> Configurações Avançadas
            </h4>
            <i class="fas fa-chevron-down text-slate-400 text-xs transition-transform" id="advanced_gateway_icon"></i>
        </div>
        <div id="advanced_gateway" class="hidden space-y-4">
            <div class="p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 flex items-center gap-2">
                    <i class="fas fa-fingerprint"></i> Identificação da Plataforma (Opcional)
                </h4>
                <p class="text-xs text-slate-400 mb-4">IDs de rastreamento de qualidade do MercadoPago.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Integrator ID</label>
                        <input type="text" name="mercadopago_integrator_id"
                            value="{{ $settings['mercadopago_integrator_id'] ?? '' }}" placeholder="ex: dev_1234567890"
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Platform ID</label>
                        <input type="text" name="mercadopago_platform_id"
                            value="{{ $settings['mercadopago_platform_id'] ?? '' }}" placeholder="ex: plat_1234567890"
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
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
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[type="checkbox"][name^="mercadopago_method_"]').forEach(cb => {
                cb.addEventListener('change', function () {
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Copiado!',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            }, function (err) {
                console.error('Could not copy text: ', err);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Init Gateway Environment UI
            document.querySelectorAll('.gateway-env-select').forEach(select => {
                select.addEventListener('change', function () {
                    const container = this.closest('.space-y-6');
                    const sandbox = container.querySelector('.env-sandbox');
                    const production = container.querySelector('.env-production');

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
                }

                // Call test route
                fetch('{{ route("panel.admin.settings.test_gateway") }}', {
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
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sucesso',
                                    text: data.message
                                });
                            }
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(data.message);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro',
                                    text: data.message
                                });
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toastr.error('Erro ao testar conexão. Verifique o console.');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                    });
            };
        });
    </script>
@endpush

@push('scripts')
    <script>
        function toggleSetting(key, checked) {
            const url = "{{ route('admin.settings.toggle') }}";
            const value = checked ? 1 : 0;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ key: key, value: value })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        });
                        Toast.fire({
                            icon: 'success',
                            title: 'Atualizado com sucesso!'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: 'Não foi possível atualizar a configuração.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        // Reverter o toggle visualmente se falhar? 
                        // Para simplificar, deixamos assim, pois o usuário pode tentar novamente.
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Ocorreu um erro na requisição.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                });
        }
    </script>
@endpush