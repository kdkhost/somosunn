{{-- ============================================================
     GATEWAYS DE PAGAMENTO — MercadoPago + SumUp
     Estrutura: Tabs principais (MP | SumUp) com sub-tabs cada
     ============================================================ --}}

@php
    $mpEnv = $settings['mercadopago_env'] ?? 'sandbox';
    $mpEnabled = (int) ($settings['mercadopago_enabled'] ?? 1) === 1;
    $sumupEnabled = (int) ($settings['sumup_enabled'] ?? 0) === 1;
@endphp

{{-- HEADER GERAL --}}
<div class="relative overflow-hidden rounded-2xl mb-6"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);">
    <div class="absolute inset-0 opacity-[0.05]"
         style="background-image: radial-gradient(circle at 20% 50%, #fff 1px, transparent 1px); background-size: 30px 30px;"></div>
    <div class="relative p-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center border border-white/10">
                <i class="fas fa-money-check-alt text-white text-2xl"></i>
            </div>
            <div>
                <p class="text-slate-300 text-xs font-bold uppercase tracking-widest mb-0.5">Configurações</p>
                <h2 class="text-white text-2xl font-black leading-tight">Gateways de Pagamento</h2>
                <p class="text-slate-400 text-xs mt-1">Configure MercadoPago e SumUp para processar pagamentos</p>
            </div>
        </div>
    </div>
</div>

{{-- TABS PRINCIPAIS: MercadoPago | SumUp --}}
<div class="flex gap-1 bg-slate-100 dark:bg-slate-900 p-1 rounded-2xl mb-6">
    <button type="button"
        onclick="switchMainGatewayTab('mercadopago-tab')"
        id="main-tab-btn-mercadopago-tab"
        class="main-gateway-tab-btn flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-bold transition-all bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm">
        <i class="fas fa-handshake"></i>
        <span>MercadoPago</span>
        @if($mpEnabled)
            <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">ATIVO</span>
        @endif
    </button>
    <button type="button"
        onclick="switchMainGatewayTab('sumup-tab')"
        id="main-tab-btn-sumup-tab"
        class="main-gateway-tab-btn flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-bold transition-all text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300">
        <i class="fas fa-credit-card"></i>
        <span>SumUp</span>
        @if($sumupEnabled)
            <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">ATIVO</span>
        @endif
    </button>
</div>

{{-- ============================================================
     TAB MERCADOPAGO
     ============================================================ --}}
<div id="mercadopago-tab" class="main-gateway-tab-panel">
    {{-- Header MercadoPago --}}
    <div class="relative overflow-hidden rounded-2xl mb-6"
         style="background: linear-gradient(135deg, #1548c0 0%, #2563eb 45%, #3b60d6 100%);">
        <div class="absolute inset-0 opacity-[0.07]"
             style="background-image: radial-gradient(circle at 20% 50%, #fff 1px, transparent 1px); background-size: 40px 40px;"></div>

        <div class="relative flex items-start justify-between p-6 gap-4 flex-wrap">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center flex-shrink-0 border border-white/20">
                    <i class="fas fa-handshake text-white text-2xl"></i>
                </div>
                <div>
                    <p class="text-blue-200 text-xs font-bold uppercase tracking-widest mb-0.5">Gateway de Pagamento</p>
                    <h2 class="text-white text-2xl font-black leading-tight">MercadoPago</h2>
                    <p class="text-blue-200 text-xs mt-1">Checkout transparente &bull; Split de pagamentos &bull; OAuth marketplace</p>
                </div>
            </div>

            <div class="flex flex-col items-end gap-2 pt-1">
                <span id="hero-env-badge"
                      class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold
                             {{ $mpEnv === 'production' ? 'bg-emerald-400/20 text-emerald-200 border border-emerald-400/30' : 'bg-amber-400/20 text-amber-200 border border-amber-400/30' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $mpEnv === 'production' ? 'bg-emerald-400' : 'bg-amber-400' }} animate-pulse"></span>
                    {{ $mpEnv === 'production' ? 'Produção' : 'Sandbox' }}
                </span>
                <label class="relative inline-flex items-center cursor-pointer gap-2 mt-1">
                    <span class="text-blue-200 text-xs font-semibold">Ativo</span>
                    <input type="hidden" name="mercadopago_enabled" value="0">
                    <input type="checkbox" class="sr-only peer" name="mercadopago_enabled" value="1"
                        {{ $mpEnabled ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-white/20 peer-focus:outline-none rounded-full peer border border-white/30
                                 peer-checked:after:translate-x-full peer-checked:after:border-white
                                 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white
                                 after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5
                                 after:transition-all peer-checked:bg-emerald-500 relative"></div>
                </label>
            </div>
        </div>
    </div>

    {{-- Sub-tabs MercadoPago --}}
    <div class="flex gap-1 bg-slate-100 dark:bg-slate-900 p-1 rounded-2xl mb-6">
        @php
            $mpTabs = [
                ['id' => 'mp-auth',     'icon' => 'fa-key',          'label' => 'Autenticação'],
                ['id' => 'mp-methods',  'icon' => 'fa-credit-card',  'label' => 'Métodos'],
                ['id' => 'mp-billing',  'icon' => 'fa-sliders-h',    'label' => 'Cobrança'],
                ['id' => 'mp-checkout', 'icon' => 'fa-magic',        'label' => 'Checkout'],
                ['id' => 'mp-advanced', 'icon' => 'fa-cog',          'label' => 'Avançado'],
            ];
        @endphp
        @foreach($mpTabs as $i => $tab)
            <button type="button"
                onclick="switchMpSubTab('{{ $tab['id'] }}')"
                id="mp-sub-tab-btn-{{ $tab['id'] }}"
                class="mp-sub-tab-btn flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-bold transition-all
                       {{ $i === 0 ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300' }}">
                <i class="fas {{ $tab['icon'] }}"></i>
                <span class="hidden sm:inline">{{ $tab['label'] }}</span>
            </button>
        @endforeach
    </div>

    {{-- MP SUB-TAB 1: AUTENTICAÇÃO --}}
    <div id="mp-auth" class="mp-sub-tab-panel space-y-5">
        {{-- Segmented control: Ambiente --}}
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Ambiente de Execução</label>
            <div class="flex bg-slate-100 dark:bg-slate-900 p-1 rounded-2xl border border-slate-200 dark:border-slate-800">
                <button type="button" id="env-btn-sandbox"
                    onclick="switchEnv('sandbox')"
                    class="env-seg-btn flex-1 py-2.5 px-4 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2
                           {{ $mpEnv !== 'production' ? 'bg-white dark:bg-slate-800 text-amber-600 dark:text-amber-400 shadow-sm border border-slate-200 dark:border-slate-700' : 'text-slate-500 dark:text-slate-400' }}">
                    <i class="fas fa-flask text-xs"></i> Sandbox
                </button>
                <button type="button" id="env-btn-production"
                    onclick="switchEnv('production')"
                    class="env-seg-btn flex-1 py-2.5 px-4 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2
                           {{ $mpEnv === 'production' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm border border-slate-200 dark:border-slate-700' : 'text-slate-500 dark:text-slate-400' }}">
                    <i class="fas fa-rocket text-xs"></i> Produção
                </button>
            </div>
            <select name="mercadopago_env" id="mercadopago_env_select" class="hidden">
                <option value="sandbox"    {{ $mpEnv !== 'production' ? 'selected' : '' }}>sandbox</option>
                <option value="production" {{ $mpEnv === 'production' ? 'selected' : '' }}>production</option>
            </select>
        </div>

        {{-- Credenciais Sandbox --}}
        <div id="env-sandbox-fields"
             class="{{ $mpEnv === 'production' ? 'hidden' : '' }} rounded-2xl border-2 border-amber-200 dark:border-amber-800/40 overflow-hidden">
            <div class="bg-amber-50 dark:bg-amber-900/10 px-4 py-2.5 flex items-center gap-2 border-b border-amber-200 dark:border-amber-800/40">
                <i class="fas fa-flask text-amber-500 text-xs"></i>
                <span class="text-xs font-black text-amber-700 dark:text-amber-400 uppercase tracking-wider">Credenciais Sandbox</span>
            </div>
            <div class="p-4 bg-white dark:bg-slate-900 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Public Key</label>
                    <input type="text" name="mercadopago_sandbox_public_key"
                        value="{{ $settings['mercadopago_sandbox_public_key'] ?? '' }}"
                        placeholder="TEST-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-amber-400 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Access Token</label>
                    <div class="relative">
                        <input type="password" name="mercadopago_sandbox_access_token" id="sb_token"
                            value="{{ $settings['mercadopago_sandbox_access_token'] ?? '' }}"
                            placeholder="TEST-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                            class="w-full px-4 py-3 pr-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-amber-400 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white">
                        <button type="button" onclick="toggleReveal('sb_token', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Credenciais Produção --}}
        <div id="env-production-fields"
             class="{{ $mpEnv !== 'production' ? 'hidden' : '' }} rounded-2xl border-2 border-emerald-200 dark:border-emerald-800/40 overflow-hidden">
            <div class="bg-emerald-50 dark:bg-emerald-900/10 px-4 py-2.5 flex items-center gap-2 border-b border-emerald-200 dark:border-emerald-800/40">
                <i class="fas fa-rocket text-emerald-500 text-xs"></i>
                <span class="text-xs font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Credenciais Produção</span>
            </div>
            <div class="p-4 bg-white dark:bg-slate-900 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Public Key</label>
                    <input type="text" name="mercadopago_prod_public_key"
                        value="{{ $settings['mercadopago_prod_public_key'] ?? '' }}"
                        placeholder="APP_USR-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-emerald-400 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Access Token</label>
                    <div class="relative">
                        <input type="password" name="mercadopago_prod_access_token" id="prod_token"
                            value="{{ $settings['mercadopago_prod_access_token'] ?? '' }}"
                            placeholder="APP_USR-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                            class="w-full px-4 py-3 pr-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-emerald-400 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white">
                        <button type="button" onclick="toggleReveal('prod_token', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Webhook URL --}}
        <div class="bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                    <i class="fas fa-link text-indigo-500 text-xs"></i>
                </div>
                <div>
                    <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">Webhook URL</span>
                    <p class="text-[10px] text-slate-400">Cole no painel do MercadoPago &rsaquo; Notificações IPN</p>
                </div>
            </div>
            <div class="flex rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                <input type="text" readonly value="{{ route('api.webhooks.mercadopago') }}"
                    class="flex-1 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-500 text-xs font-mono focus:outline-none border-0">
                <button type="button" onclick="copyToClipboard('{{ route('api.webhooks.mercadopago') }}')"
                    class="px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-1.5 transition-colors">
                    <i class="fas fa-copy"></i> Copiar
                </button>
            </div>
        </div>

        {{-- OAuth credentials --}}
        <div class="rounded-2xl border-2 border-blue-100 dark:border-blue-900/30 overflow-hidden">
            <div class="bg-blue-50 dark:bg-blue-900/10 px-4 py-2.5 flex items-center gap-2 border-b border-blue-100 dark:border-blue-900/30">
                <i class="fas fa-shield-alt text-blue-500 text-xs"></i>
                <span class="text-xs font-black text-blue-700 dark:text-blue-400 uppercase tracking-wider">App OAuth (Marketplace / Split)</span>
            </div>
            <div class="p-4 bg-white dark:bg-slate-900 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Client ID (App ID)</label>
                    <input type="text" name="mercadopago_client_id"
                        value="{{ $settings['mercadopago_client_id'] ?? '' }}"
                        placeholder="1234567890123456"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Client Secret</label>
                    <div class="relative">
                        <input type="password" name="mercadopago_client_secret" id="mp_secret"
                            value="{{ $settings['mercadopago_client_secret'] ?? '' }}"
                            placeholder="••••••••••••••••••••••••••••••••"
                            class="w-full px-4 py-3 pr-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white">
                        <button type="button" onclick="toggleReveal('mp_secret', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botão de teste --}}
        <button type="button" onclick="testGatewayConnection('mercadopago')" id="btn-test-mp"
            class="w-full flex items-center justify-center gap-2 py-3 px-6 rounded-xl font-bold text-sm transition-all
                   bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-600/20 active:scale-[0.98]">
            <i class="fas fa-plug"></i>
            Testar Conexão com MercadoPago
        </button>
    </div>

    {{-- MP SUB-TAB 2: MÉTODOS --}}
    <div id="mp-methods" class="mp-sub-tab-panel hidden">
        <p class="text-xs text-slate-400 mb-4 font-medium">Ative ou desative os métodos disponíveis no checkout MercadoPago.</p>
        @php
            $mpMethods = [
                ['name' => 'mercadopago_method_credit_card', 'label' => 'Cartão de Crédito', 'desc' => 'Parcele em até 12x', 'icon' => 'fa-credit-card', 'color' => 'blue', 'default' => 1],
                ['name' => 'mercadopago_method_debit_card',  'label' => 'Cartão de Débito',  'desc' => 'Débito à vista',    'icon' => 'fa-credit-card', 'color' => 'violet', 'default' => 0],
                ['name' => 'mercadopago_method_pix',         'label' => 'Pix',                'desc' => 'Aprovação imediata','icon' => 'fa-pix',         'color' => 'teal', 'default' => 1, 'brand' => true],
                ['name' => 'mercadopago_method_ticket',      'label' => 'Boleto Bancário',    'desc' => 'Prazo de 1–3 dias', 'icon' => 'fa-barcode',     'color' => 'orange', 'default' => 0],
                ['name' => 'mercadopago_method_mercadopago', 'label' => 'Carteira MP',        'desc' => 'Saldo MercadoPago', 'icon' => 'fa-wallet',      'color' => 'sky', 'default' => 0],
            ];
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($mpMethods as $m)
                @php $checked = (int) ($settings[$m['name']] ?? $m['default']) === 1; @endphp
                <label for="method_{{ $m['name'] }}"
                    class="relative flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all select-none
                           {{ $checked ? 'border-' . $m['color'] . '-400 bg-' . $m['color'] . '-50 dark:bg-' . $m['color'] . '-900/15' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-slate-300' }}">
                    <input type="hidden" name="{{ $m['name'] }}" value="0">
                    <input type="checkbox" id="method_{{ $m['name'] }}" class="sr-only method-cb" name="{{ $m['name'] }}" value="1"
                        data-color="{{ $m['color'] }}"
                        {{ $checked ? 'checked' : '' }}>
                    <div class="flex-shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center transition-all
                                {{ $checked ? 'bg-' . $m['color'] . '-100 dark:bg-' . $m['color'] . '-800/30' : 'bg-slate-100 dark:bg-slate-800' }}">
                        <i class="{{ !empty($m['brand']) ? 'fa-brands' : 'fas' }} {{ $m['icon'] }} text-xl
                                  {{ $checked ? 'text-' . $m['color'] . '-600' : 'text-slate-400' }}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-black text-sm {{ $checked ? 'text-' . $m['color'] . '-800 dark:text-' . $m['color'] . '-300' : 'text-slate-700 dark:text-slate-300' }}">
                            {{ $m['label'] }}
                        </p>
                        <p class="text-xs {{ $checked ? 'text-' . $m['color'] . '-500' : 'text-slate-400' }}">{{ $m['desc'] }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                 {{ $checked ? 'bg-' . $m['color'] . '-100 text-' . $m['color'] . '-700' : 'bg-slate-100 text-slate-400' }}">
                        {{ $checked ? 'Ativo' : 'Off' }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- MP SUB-TAB 3: COBRANÇA --}}
    <div id="mp-billing" class="mp-sub-tab-panel hidden space-y-5">
        <label for="gateway_transparent_checkout"
            class="flex items-start gap-4 p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 cursor-pointer hover:border-blue-300 transition-colors">
            <input type="hidden" name="gateway_transparent_checkout" value="0">
            <input id="gateway_transparent_checkout" name="gateway_transparent_checkout" type="checkbox" value="1"
                class="mt-0.5 h-5 w-5 rounded-lg border-slate-300 text-blue-600"
                {{ ($settings['gateway_transparent_checkout'] ?? 0) ? 'checked' : '' }}>
            <div>
                <p class="font-black text-slate-800 dark:text-white">Checkout Transparente</p>
                <p class="text-xs text-slate-500 mt-0.5">O usuário paga sem sair do seu site. Se desativado, será redirecionado para o MercadoPago.</p>
            </div>
        </label>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Juros Parcelas (MP)</label>
                <div class="relative">
                    <input type="number" step="0.01" name="mercadopago_installment_tax"
                        value="{{ $settings['mercadopago_installment_tax'] ?? '0.00' }}"
                        class="w-full px-4 py-3 pr-8 rounded-xl border border-slate-200 bg-slate-50 focus:border-orange-400 outline-none transition-all font-bold text-sm">
                    <span class="absolute inset-y-0 right-3 flex items-center text-slate-400 text-sm">%</span>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Máx. Parcelas</label>
                <input type="number" min="1" max="12" name="mercadopago_max_installments"
                    value="{{ $settings['mercadopago_max_installments'] ?? '12' }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-bold text-sm">
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Sem Juros (até)</label>
                <input type="number" min="1" max="12" name="mercadopago_installments_no_interest"
                    value="{{ $settings['mercadopago_installments_no_interest'] ?? '1' }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-bold text-sm">
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Repassar Taxa</label>
                <select name="gateway_pass_tax_to_client"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all text-sm font-semibold">
                    <option value="0" {{ (int) ($settings['gateway_pass_tax_to_client'] ?? 0) === 0 ? 'selected' : '' }}>Empresa absorve</option>
                    <option value="1" {{ (int) ($settings['gateway_pass_tax_to_client'] ?? 0) === 1 ? 'selected' : '' }}>Cliente paga</option>
                </select>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Expiração PIX MP (minutos)</label>
            <div class="relative max-w-xs">
                <input type="number" min="1" max="1440" name="mercadopago_pix_expiration_minutes"
                    value="{{ $settings['mercadopago_pix_expiration_minutes'] ?? '10' }}"
                    class="w-full px-4 py-3 pr-12 rounded-xl border border-slate-200 bg-slate-50 focus:border-teal-400 outline-none transition-all font-bold text-sm">
                <span class="absolute inset-y-0 right-3 flex items-center text-slate-400 text-xs">min</span>
            </div>
        </div>
    </div>

    {{-- MP SUB-TAB 4: CHECKOUT --}}
    <div id="mp-checkout" class="mp-sub-tab-panel hidden space-y-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Tema do Checkout</label>
            <select name="gateway_checkout_theme"
                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all text-sm font-semibold">
                <option value="default" {{ ($settings['gateway_checkout_theme'] ?? 'default') === 'default' ? 'selected' : '' }}>Padrão do MercadoPago</option>
                <option value="dark" {{ ($settings['gateway_checkout_theme'] ?? '') === 'dark' ? 'selected' : '' }}>Dark</option>
                <option value="bootstrap" {{ ($settings['gateway_checkout_theme'] ?? '') === 'bootstrap' ? 'selected' : '' }}>Bootstrap</option>
                <option value="flat" {{ ($settings['gateway_checkout_theme'] ?? '') === 'flat' ? 'selected' : '' }}>Flat</option>
            </select>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Cor Principal</label>
            <div class="flex items-center gap-3">
                <input type="color" name="gateway_checkout_primary_color" id="gateway_color_picker"
                    value="{{ $settings['gateway_checkout_primary_color'] ?? '#1F5EDB' }}"
                    class="h-11 w-14 rounded-xl border border-slate-200 cursor-pointer flex-shrink-0"
                    oninput="document.getElementById('gateway_color_text').value = this.value">
                <input type="text" id="gateway_color_text"
                    value="{{ $settings['gateway_checkout_primary_color'] ?? '#1F5EDB' }}" readonly
                    class="flex-1 px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-mono outline-none">
            </div>
        </div>
    </div>

    {{-- MP SUB-TAB 5: AVANÇADO --}}
    <div id="mp-advanced" class="mp-sub-tab-panel hidden">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Integrator ID</label>
                <input type="text" name="mercadopago_integrator_id"
                    value="{{ $settings['mercadopago_integrator_id'] ?? '' }}" placeholder="dev_1234567890"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-mono text-sm">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Platform ID</label>
                <input type="text" name="mercadopago_platform_id"
                    value="{{ $settings['mercadopago_platform_id'] ?? '' }}" placeholder="plat_1234567890"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-mono text-sm">
            </div>
        </div>
    </div>
</div>
{{-- /mercadopago-tab --}}

{{-- ============================================================
     TAB SUMUP
     ============================================================ --}}
<div id="sumup-tab" class="main-gateway-tab-panel hidden">
    {{-- Header SumUp --}}
    <div class="relative overflow-hidden rounded-2xl mb-6"
         style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);">
        <div class="absolute inset-0 opacity-[0.07]"
             style="background-image: radial-gradient(circle at 20% 50%, #fff 1px, transparent 1px); background-size: 40px 40px;"></div>
        <div class="relative flex items-start justify-between p-6 gap-4 flex-wrap">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center flex-shrink-0 border border-white/20">
                    <i class="fas fa-credit-card text-white text-2xl"></i>
                </div>
                <div>
                    <p class="text-blue-200 text-xs font-bold uppercase tracking-widest mb-0.5">Gateway de Pagamento</p>
                    <h2 class="text-white text-2xl font-black leading-tight">SumUp</h2>
                    <p class="text-blue-200 text-xs mt-1">Checkout integrado &bull; Cartão &bull; PIX</p>
                </div>
            </div>
            <div class="flex flex-col items-end gap-2 pt-1">
                <label class="relative inline-flex items-center cursor-pointer gap-2 mt-1">
                    <span class="text-blue-200 text-xs font-semibold">Ativo</span>
                    <input type="hidden" name="sumup_enabled" value="0">
                    <input type="checkbox" class="sr-only peer" name="sumup_enabled" value="1"
                        {{ $sumupEnabled ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-white/20 peer-focus:outline-none rounded-full peer border border-white/30
                                 peer-checked:after:translate-x-full peer-checked:after:border-white
                                 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white
                                 after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5
                                 after:transition-all peer-checked:bg-emerald-500 relative"></div>
                </label>
            </div>
        </div>
    </div>

    {{-- Sub-tabs SumUp --}}
    <div class="flex gap-1 bg-slate-100 dark:bg-slate-900 p-1 rounded-2xl mb-6">
        @php
            $sumupTabs = [
                ['id' => 'sumup-auth',        'icon' => 'fa-key',          'label' => 'Autenticação'],
                ['id' => 'sumup-methods',     'icon' => 'fa-credit-card',  'label' => 'Métodos'],
                ['id' => 'sumup-billing',     'icon' => 'fa-sliders-h',    'label' => 'Cobrança'],
                ['id' => 'sumup-permissions', 'icon' => 'fa-shield-alt',   'label' => 'Permissões'],
            ];
        @endphp
        @foreach($sumupTabs as $i => $tab)
            <button type="button"
                onclick="switchSumupSubTab('{{ $tab['id'] }}')"
                id="sumup-sub-tab-btn-{{ $tab['id'] }}"
                class="sumup-sub-tab-btn flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-bold transition-all
                       {{ $i === 0 ? 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700' }}">
                <i class="fas {{ $tab['icon'] }}"></i>
                <span class="hidden sm:inline">{{ $tab['label'] }}</span>
            </button>
        @endforeach
    </div>

    {{-- SUMUP SUB-TAB 1: AUTENTICAÇÃO --}}
    <div id="sumup-auth" class="sumup-sub-tab-panel space-y-5">
        {{-- Instruções --}}
        <div class="rounded-2xl border border-blue-200 dark:border-blue-800/40 bg-blue-50 dark:bg-blue-900/10 p-4 text-xs text-blue-800 dark:text-blue-300">
            <div class="font-black mb-2 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> Como preencher as credenciais SumUp
            </div>
            <ol class="list-decimal ml-5 space-y-1">
                <li><strong>API Key:</strong> <a href="https://me.sumup.com" target="_blank" class="underline">me.sumup.com</a> &gt; Settings &gt; For Developers &gt; API Keys</li>
                <li><strong>Merchant Code:</strong> código da conta lojista (ex: M7MMJMM7)</li>
                <li><strong>Client ID/Secret:</strong> opcionais para OAuth (For Developers &gt; OAuth Apps)</li>
                <li><strong>Webhook Secret:</strong> opcional, para validação HMAC</li>
            </ol>
        </div>

        {{-- Ambiente --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Ambiente SumUp</label>
            <select name="sumup_env"
                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all text-sm font-semibold">
                <option value="sandbox" {{ ($settings['sumup_env'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testes)</option>
                <option value="production" {{ ($settings['sumup_env'] ?? '') === 'production' ? 'selected' : '' }}>Produção (Real)</option>
            </select>
        </div>

        {{-- Credenciais SumUp --}}
        <div class="rounded-2xl border-2 border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="bg-slate-50 dark:bg-slate-900 px-4 py-2.5 flex items-center gap-2 border-b border-slate-200 dark:border-slate-800">
                <i class="fas fa-key text-slate-500 text-xs"></i>
                <span class="text-xs font-black text-slate-700 uppercase tracking-wider">Credenciais SumUp</span>
            </div>
            <div class="p-4 bg-white dark:bg-slate-900 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">API Key <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="sumup_api_key" id="sumup_api_key_field"
                            value="{{ $settings['sumup_api_key'] ?? '' }}"
                            placeholder="sup_sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                            class="w-full px-4 py-3 pr-11 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white">
                        <button type="button" onclick="toggleReveal('sumup_api_key_field', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Merchant Code <span class="text-red-500">*</span></label>
                    <input type="text" name="sumup_merchant_code"
                        value="{{ $settings['sumup_merchant_code'] ?? '' }}"
                        placeholder="MXXXXXXX"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Client ID (OAuth)</label>
                    <input type="text" name="sumup_client_id"
                        value="{{ $settings['sumup_client_id'] ?? '' }}"
                        placeholder="opcional"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-mono text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Client Secret (OAuth)</label>
                    <div class="relative">
                        <input type="password" name="sumup_client_secret" id="sumup_client_secret_field"
                            value="{{ $settings['sumup_client_secret'] ?? '' }}"
                            placeholder="opcional"
                            class="w-full px-4 py-3 pr-11 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-mono text-sm">
                        <button type="button" onclick="toggleReveal('sumup_client_secret_field', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Webhook SumUp --}}
        <div class="bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <i class="fas fa-link text-indigo-500 text-xs"></i>
                </div>
                <div>
                    <span class="text-xs font-black text-slate-700 uppercase tracking-wider">Webhook SumUp</span>
                    <p class="text-[10px] text-slate-400">Cole no painel SumUp &gt; Developers &gt; Webhooks</p>
                </div>
            </div>
            <div class="flex rounded-xl overflow-hidden border border-slate-200 mb-3">
                <input type="text" readonly value="{{ route('api.webhooks.sumup') }}"
                    class="flex-1 px-4 py-2.5 bg-white text-slate-500 text-xs font-mono focus:outline-none border-0">
                <button type="button" onclick="copyToClipboard('{{ route('api.webhooks.sumup') }}')"
                    class="px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold flex items-center gap-1.5 transition-colors">
                    <i class="fas fa-copy"></i> Copiar
                </button>
            </div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Webhook Secret (opcional)</label>
            <div class="relative">
                <input type="password" name="sumup_webhook_secret" id="sumup_webhook_secret_field"
                    value="{{ $settings['sumup_webhook_secret'] ?? '' }}"
                    placeholder="Deixe vazio se não usar HMAC"
                    class="w-full px-4 py-3 pr-11 rounded-xl border border-slate-200 bg-white focus:border-blue-500 outline-none transition-all font-mono text-sm">
                <button type="button" onclick="toggleReveal('sumup_webhook_secret_field', this)"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                    <i class="fas fa-eye text-sm"></i>
                </button>
            </div>
        </div>

        {{-- Botão de teste --}}
        <button type="button" onclick="testSumUpConnection()" id="btn-test-sumup"
            class="w-full flex items-center justify-center gap-2 py-3 px-6 rounded-xl font-bold text-sm transition-all
                   bg-slate-800 hover:bg-slate-900 text-white shadow-lg active:scale-[0.98]">
            <i class="fas fa-plug"></i>
            Testar Conexão com SumUp
        </button>
    </div>

    {{-- SUMUP SUB-TAB 2: MÉTODOS --}}
    <div id="sumup-methods" class="sumup-sub-tab-panel hidden">
        <p class="text-xs text-slate-400 mb-4 font-medium">Ative ou desative os métodos disponíveis no checkout SumUp.</p>
        @php
            $sumupMethods = [
                ['name' => 'sumup_method_card', 'label' => 'Cartão de Crédito', 'desc' => 'Via SumUp.js (tokenizado)', 'icon' => 'fa-credit-card', 'color' => 'blue', 'default' => 1],
                ['name' => 'sumup_method_pix',  'label' => 'PIX',               'desc' => 'QR Code inline',           'icon' => 'fa-pix',         'color' => 'teal', 'default' => 1, 'brand' => true],
            ];
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($sumupMethods as $m)
                @php $checked = (int) ($settings[$m['name']] ?? $m['default']) === 1; @endphp
                <label for="sumup_method_{{ $m['name'] }}"
                    class="flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all select-none
                           {{ $checked ? 'border-' . $m['color'] . '-400 bg-' . $m['color'] . '-50 dark:bg-' . $m['color'] . '-900/15' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900' }}">
                    <input type="hidden" name="{{ $m['name'] }}" value="0">
                    <input type="checkbox" id="sumup_method_{{ $m['name'] }}" class="sr-only" name="{{ $m['name'] }}" value="1"
                        {{ $checked ? 'checked' : '' }}>
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $checked ? 'bg-' . $m['color'] . '-100' : 'bg-slate-100' }}">
                        <i class="{{ !empty($m['brand']) ? 'fa-brands' : 'fas' }} {{ $m['icon'] }} text-lg {{ $checked ? 'text-' . $m['color'] . '-600' : 'text-slate-400' }}"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-black text-sm {{ $checked ? 'text-' . $m['color'] . '-800' : 'text-slate-700' }}">{{ $m['label'] }}</p>
                        <p class="text-xs {{ $checked ? 'text-' . $m['color'] . '-500' : 'text-slate-400' }}">{{ $m['desc'] }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                 {{ $checked ? 'bg-' . $m['color'] . '-100 text-' . $m['color'] . '-700' : 'bg-slate-100 text-slate-400' }}">
                        {{ $checked ? 'Ativo' : 'Off' }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- SUMUP SUB-TAB 3: COBRANÇA --}}
    <div id="sumup-billing" class="sumup-sub-tab-panel hidden space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 p-4">
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Taxa Percentual</label>
                <div class="relative">
                    <input type="number" step="0.01" name="sumup_fee_percentage"
                        value="{{ $settings['sumup_fee_percentage'] ?? '2.75' }}"
                        class="w-full px-4 py-3 pr-8 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-bold text-sm">
                    <span class="absolute inset-y-0 right-3 flex items-center text-slate-400 text-sm">%</span>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 p-4">
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Taxa Fixa</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm">R$</span>
                    <input type="number" step="0.01" name="sumup_fee_fixed"
                        value="{{ $settings['sumup_fee_fixed'] ?? '0.00' }}"
                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-bold text-sm">
                </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 p-4">
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Máx. Parcelas</label>
                <input type="number" min="1" max="12" name="sumup_max_installments"
                    value="{{ $settings['sumup_max_installments'] ?? '12' }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-bold text-sm">
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 p-4">
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Repassar Taxa</label>
                <select name="sumup_pass_fee"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all text-sm font-semibold">
                    <option value="0" {{ (int) ($settings['sumup_pass_fee'] ?? 0) === 0 ? 'selected' : '' }}>Empresa absorve</option>
                    <option value="1" {{ (int) ($settings['sumup_pass_fee'] ?? 0) === 1 ? 'selected' : '' }}>Cliente paga</option>
                </select>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Parcelas sem Juros</label>
                <input type="number" min="1" max="12" name="sumup_installments_no_interest"
                    value="{{ $settings['sumup_installments_no_interest'] ?? '1' }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-bold text-sm">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Juros por Parcela (%)</label>
                <div class="relative">
                    <input type="number" step="0.01" name="sumup_installment_tax"
                        value="{{ $settings['sumup_installment_tax'] ?? '0.00' }}"
                        class="w-full px-4 py-3 pr-8 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-bold text-sm">
                    <span class="absolute inset-y-0 right-3 flex items-center text-slate-400 text-sm">%</span>
                </div>
            </div>
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Tipo Cálculo</label>
                <select name="sumup_interest_type"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all text-sm font-semibold">
                    <option value="per_installment" {{ ($settings['sumup_interest_type'] ?? 'per_installment') === 'per_installment' ? 'selected' : '' }}>Por parcela</option>
                    <option value="on_total" {{ ($settings['sumup_interest_type'] ?? '') === 'on_total' ? 'selected' : '' }}>Sobre o total</option>
                </select>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 p-4">
            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Expiração PIX SumUp (minutos)</label>
            <div class="relative max-w-xs">
                <input type="number" min="1" max="1440" name="sumup_pix_expiration_minutes"
                    value="{{ $settings['sumup_pix_expiration_minutes'] ?? '10' }}"
                    class="w-full px-4 py-3 pr-12 rounded-xl border border-slate-200 bg-slate-50 focus:border-teal-400 outline-none transition-all font-bold text-sm">
                <span class="absolute inset-y-0 right-3 flex items-center text-slate-400 text-xs">min</span>
            </div>
        </div>
    </div>

    {{-- SUMUP SUB-TAB 4: PERMISSÕES --}}
    <div id="sumup-permissions" class="sumup-sub-tab-panel hidden space-y-5">
        <div>
            <h4 class="text-sm font-black text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                <i class="fas fa-users text-blue-500"></i> Por Tipo de Usuário
            </h4>
            @php
                $userPerms = [
                    ['name' => 'sumup_allow_members',     'label' => 'Membros',     'icon' => 'fa-user',          'color' => 'blue'],
                    ['name' => 'sumup_allow_instructors', 'label' => 'Instrutores', 'icon' => 'fa-chalkboard-teacher', 'color' => 'violet'],
                    ['name' => 'sumup_allow_sellers',     'label' => 'Vendedores',  'icon' => 'fa-store',         'color' => 'orange'],
                    ['name' => 'sumup_allow_mentors',     'label' => 'Mentores',    'icon' => 'fa-user-tie',      'color' => 'teal'],
                ];
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($userPerms as $perm)
                    @php $checked = (int) ($settings[$perm['name']] ?? 1) === 1; @endphp
                    <label for="perm_{{ $perm['name'] }}"
                        class="flex flex-col items-center gap-2 p-4 rounded-2xl border-2 cursor-pointer transition-all
                               {{ $checked ? 'border-' . $perm['color'] . '-400 bg-' . $perm['color'] . '-50' : 'border-slate-200 bg-white' }}">
                        <input type="hidden" name="{{ $perm['name'] }}" value="0">
                        <input type="checkbox" id="perm_{{ $perm['name'] }}" class="sr-only" name="{{ $perm['name'] }}" value="1"
                            {{ $checked ? 'checked' : '' }}>
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $checked ? 'bg-' . $perm['color'] . '-100' : 'bg-slate-100' }}">
                            <i class="fas {{ $perm['icon'] }} text-lg {{ $checked ? 'text-' . $perm['color'] . '-600' : 'text-slate-400' }}"></i>
                        </div>
                        <p class="text-xs font-black {{ $checked ? 'text-' . $perm['color'] . '-800' : 'text-slate-500' }}">{{ $perm['label'] }}</p>
                        <span class="text-[10px] font-bold {{ $checked ? 'text-' . $perm['color'] . '-600' : 'text-slate-400' }}">{{ $checked ? 'Permitido' : 'Bloqueado' }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <h4 class="text-sm font-black text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                <i class="fas fa-box text-emerald-500"></i> Por Tipo de Produto
            </h4>
            @php
                $productPerms = [
                    ['name' => 'sumup_allow_courses',       'label' => 'Cursos',       'icon' => 'fa-graduation-cap', 'color' => 'blue'],
                    ['name' => 'sumup_allow_mentorships',   'label' => 'Mentorias',    'icon' => 'fa-user-tie',       'color' => 'violet'],
                    ['name' => 'sumup_allow_events',        'label' => 'Eventos',      'icon' => 'fa-calendar',       'color' => 'pink'],
                    ['name' => 'sumup_allow_marketplace',   'label' => 'Marketplace',  'icon' => 'fa-store',          'color' => 'orange'],
                    ['name' => 'sumup_allow_subscriptions', 'label' => 'Assinaturas',  'icon' => 'fa-crown',          'color' => 'amber'],
                    ['name' => 'sumup_allow_services',      'label' => 'Serviços',     'icon' => 'fa-concierge-bell', 'color' => 'teal'],
                ];
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($productPerms as $perm)
                    @php $checked = (int) ($settings[$perm['name']] ?? 1) === 1; @endphp
                    <label for="perm_{{ $perm['name'] }}"
                        class="flex flex-col items-center gap-2 p-4 rounded-2xl border-2 cursor-pointer transition-all
                               {{ $checked ? 'border-' . $perm['color'] . '-400 bg-' . $perm['color'] . '-50' : 'border-slate-200 bg-white' }}">
                        <input type="hidden" name="{{ $perm['name'] }}" value="0">
                        <input type="checkbox" id="perm_{{ $perm['name'] }}" class="sr-only" name="{{ $perm['name'] }}" value="1"
                            {{ $checked ? 'checked' : '' }}>
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $checked ? 'bg-' . $perm['color'] . '-100' : 'bg-slate-100' }}">
                            <i class="fas {{ $perm['icon'] }} text-lg {{ $checked ? 'text-' . $perm['color'] . '-600' : 'text-slate-400' }}"></i>
                        </div>
                        <p class="text-xs font-black {{ $checked ? 'text-' . $perm['color'] . '-800' : 'text-slate-500' }}">{{ $perm['label'] }}</p>
                        <span class="text-[10px] font-bold {{ $checked ? 'text-' . $perm['color'] . '-600' : 'text-slate-400' }}">{{ $checked ? 'Permitido' : 'Bloqueado' }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 p-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Valor Mínimo (R$)</label>
                <input type="number" step="0.01" name="sumup_minimum_amount"
                    value="{{ $settings['sumup_minimum_amount'] ?? '0.00' }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-bold text-sm">
                <p class="text-[10px] text-slate-400 mt-1">0 = sem limite</p>
            </div>
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Valor Máximo (R$)</label>
                <input type="number" step="0.01" name="sumup_maximum_amount"
                    value="{{ $settings['sumup_maximum_amount'] ?? '0.00' }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:border-blue-500 outline-none transition-all font-bold text-sm">
                <p class="text-[10px] text-slate-400 mt-1">0 = sem limite</p>
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="sumup_fallback_to_mercadopago" value="0">
                    <input type="checkbox" name="sumup_fallback_to_mercadopago" value="1"
                        class="h-5 w-5 rounded border-slate-300 text-blue-600"
                        {{ (int) ($settings['sumup_fallback_to_mercadopago'] ?? 1) === 1 ? 'checked' : '' }}>
                    <div>
                        <p class="text-xs font-black text-slate-700">Fallback MercadoPago</p>
                        <p class="text-[10px] text-slate-400">Se SumUp falhar, usar MP</p>
                    </div>
                </label>
            </div>
        </div>
    </div>
</div>
{{-- /sumup-tab --}}

@push('scripts')
<script>
/* ───────── TABS PRINCIPAIS (MP | SumUp) ───────── */
function switchMainGatewayTab(tabId) {
    document.querySelectorAll('.main-gateway-tab-panel').forEach(p => p.classList.add('hidden'));
    document.getElementById(tabId).classList.remove('hidden');

    document.querySelectorAll('.main-gateway-tab-btn').forEach(btn => {
        btn.classList.remove('bg-white', 'dark:bg-slate-800', 'text-blue-600', 'dark:text-blue-400', 'shadow-sm');
        btn.classList.add('text-slate-500', 'dark:text-slate-400');
    });
    const active = document.getElementById('main-tab-btn-' + tabId);
    if (active) {
        active.classList.remove('text-slate-500', 'dark:text-slate-400');
        active.classList.add('bg-white', 'dark:bg-slate-800', 'text-blue-600', 'dark:text-blue-400', 'shadow-sm');
    }
}

/* ───────── SUB-TABS MercadoPago ───────── */
function switchMpSubTab(tabId) {
    document.querySelectorAll('.mp-sub-tab-panel').forEach(p => p.classList.add('hidden'));
    document.getElementById(tabId).classList.remove('hidden');

    document.querySelectorAll('.mp-sub-tab-btn').forEach(btn => {
        btn.classList.remove('bg-white', 'dark:bg-slate-800', 'text-blue-600', 'dark:text-blue-400', 'shadow-sm');
        btn.classList.add('text-slate-500', 'dark:text-slate-400');
    });
    const active = document.getElementById('mp-sub-tab-btn-' + tabId);
    if (active) {
        active.classList.remove('text-slate-500', 'dark:text-slate-400');
        active.classList.add('bg-white', 'dark:bg-slate-800', 'text-blue-600', 'dark:text-blue-400', 'shadow-sm');
    }
}

/* ───────── SUB-TABS SumUp ───────── */
function switchSumupSubTab(tabId) {
    document.querySelectorAll('.sumup-sub-tab-panel').forEach(p => p.classList.add('hidden'));
    document.getElementById(tabId).classList.remove('hidden');

    document.querySelectorAll('.sumup-sub-tab-btn').forEach(btn => {
        btn.classList.remove('bg-white', 'dark:bg-slate-800', 'text-slate-700', 'dark:text-slate-300', 'shadow-sm');
        btn.classList.add('text-slate-500', 'dark:text-slate-400');
    });
    const active = document.getElementById('sumup-sub-tab-btn-' + tabId);
    if (active) {
        active.classList.remove('text-slate-500', 'dark:text-slate-400');
        active.classList.add('bg-white', 'dark:bg-slate-800', 'text-slate-700', 'dark:text-slate-300', 'shadow-sm');
    }
}

/* ───────── ENV SWITCHER MP ───────── */
function switchEnv(env) {
    const isSandbox = env === 'sandbox';
    document.getElementById('env-sandbox-fields').classList.toggle('hidden', !isSandbox);
    document.getElementById('env-production-fields').classList.toggle('hidden', isSandbox);
    document.getElementById('mercadopago_env_select').value = env;

    const sbBtn = document.getElementById('env-btn-sandbox');
    const prBtn = document.getElementById('env-btn-production');
    if (isSandbox) {
        sbBtn.classList.add('bg-white', 'dark:bg-slate-800', 'text-amber-600', 'shadow-sm', 'border', 'border-slate-200');
        prBtn.classList.remove('bg-white', 'dark:bg-slate-800', 'text-emerald-600', 'shadow-sm', 'border', 'border-slate-200');
        prBtn.classList.add('text-slate-500');
    } else {
        prBtn.classList.add('bg-white', 'dark:bg-slate-800', 'text-emerald-600', 'shadow-sm', 'border', 'border-slate-200');
        sbBtn.classList.remove('bg-white', 'dark:bg-slate-800', 'text-amber-600', 'shadow-sm', 'border', 'border-slate-200');
        sbBtn.classList.add('text-slate-500');
    }
}

/* ───────── REVEAL PASSWORD ───────── */
function toggleReveal(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

/* ───────── CLIPBOARD ───────── */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'success', title: 'Copiado!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
        } else {
            alert('Copiado!');
        }
    });
}

/* ───────── TEST MP CONNECTION ───────── */
function testGatewayConnection(gateway) {
    const btn = document.getElementById('btn-test-mp');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testando...';

    const env = document.getElementById('mercadopago_env_select').value;
    const token = env === 'sandbox'
        ? document.querySelector('input[name="mercadopago_sandbox_access_token"]').value
        : document.querySelector('input[name="mercadopago_prod_access_token"]').value;

    fetch('{{ route("panel.admin.settings.test_gateway") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ gateway: 'mercadopago', access_token: token, env: env })
    })
    .then(r => r.json())
    .then(data => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: data.success ? 'success' : 'error',
                title: data.success ? 'Conexão OK!' : 'Falhou',
                text: data.message || '',
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000
            });
        }
    })
    .catch(() => { alert('Erro na requisição'); })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = orig;
    });
}

/* ───────── TEST SUMUP CONNECTION ───────── */
function testSumUpConnection() {
    const btn = document.getElementById('btn-test-sumup');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testando...';

    const apiKey = document.getElementById('sumup_api_key_field').value;

    fetch('{{ route("panel.admin.settings.test_gateway") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ gateway: 'sumup', access_token: apiKey, env: 'production' })
    })
    .then(r => r.json())
    .then(data => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: data.success ? 'success' : 'error',
                title: data.success ? 'Conexão OK!' : 'Falhou',
                text: data.message || '',
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000
            });
        }
    })
    .catch(() => { alert('Erro na requisição'); })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = orig;
    });
}

/* ───────── INIT ───────── */
document.addEventListener('DOMContentLoaded', function () {
    @php $initEnv = $settings['mercadopago_env'] ?? 'sandbox'; @endphp
    switchEnv('{{ $initEnv }}');
});
</script>
@endpush
