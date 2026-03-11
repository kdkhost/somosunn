{{-- ============================================================
     GATEWAY — REDESIGN COMPLETO
     Tabs: Autenticação | Métodos | Cobrança | Checkout | Avançado
     ============================================================ --}}

{{-- HERO HEADER --}}
<div class="relative overflow-hidden rounded-2xl mb-6"
     style="background: linear-gradient(135deg, #1548c0 0%, #2563eb 45%, #3b60d6 100%);">
    {{-- Padrão decorativo --}}
    <div class="absolute inset-0 opacity-[0.07]"
         style="background-image: radial-gradient(circle at 20% 50%, #fff 1px, transparent 1px), radial-gradient(circle at 80% 20%, #fff 1px, transparent 1px); background-size: 40px 40px;"></div>
    {{-- Blob decorativo --}}
    <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full opacity-10"
         style="background: radial-gradient(circle, #93c5fd, transparent 70%);"></div>

    <div class="relative flex items-start justify-between p-6 gap-4 flex-wrap">
        <div class="flex items-center gap-4">
            {{-- Logo MP --}}
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
            {{-- Badge de ambiente ativo --}}
            @php $mpEnv = $settings['mercadopago_env'] ?? 'sandbox'; @endphp
            <span id="hero-env-badge"
                  class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold
                         {{ $mpEnv === 'production' ? 'bg-emerald-400/20 text-emerald-200 border border-emerald-400/30' : 'bg-amber-400/20 text-amber-200 border border-amber-400/30' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $mpEnv === 'production' ? 'bg-emerald-400' : 'bg-amber-400' }} animate-pulse"></span>
                {{ $mpEnv === 'production' ? 'Produção' : 'Sandbox' }}
            </span>
            {{-- Toggle ativo/inativo --}}
            <label class="relative inline-flex items-center cursor-pointer gap-2 mt-1">
                <span class="text-blue-200 text-xs font-semibold">Ativo</span>
                <input type="hidden" name="mercadopago_enabled" value="0">
                <input type="checkbox" class="sr-only peer" name="mercadopago_enabled" value="1"
                    onchange="toggleSetting('mercadopago_enabled', this.checked)" {{ ($settings['mercadopago_enabled'] ?? 1) ? 'checked' : '' }}>
                <div class="w-11 h-6 bg-white/20 peer-focus:outline-none rounded-full peer border border-white/30
                             peer-checked:after:translate-x-full peer-checked:after:border-white
                             after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white
                             after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5
                             after:transition-all peer-checked:bg-emerald-500 relative"></div>
            </label>
        </div>
    </div>
</div>

{{-- TABS --}}
<div class="flex gap-1 bg-slate-100 dark:bg-slate-900 p-1 rounded-2xl mb-6" id="gw-tab-nav">
    @php
        $tabs = [
            ['id' => 'gw-auth',     'icon' => 'fa-key',          'label' => 'Autenticação'],
            ['id' => 'gw-methods',  'icon' => 'fa-credit-card',  'label' => 'Métodos'],
            ['id' => 'gw-billing',  'icon' => 'fa-sliders-h',    'label' => 'Cobrança'],
            ['id' => 'gw-checkout', 'icon' => 'fa-magic',        'label' => 'Checkout'],
            ['id' => 'gw-advanced', 'icon' => 'fa-cog',          'label' => 'Avançado'],
        ];
    @endphp
    @foreach($tabs as $i => $tab)
        <button type="button"
            onclick="switchGwTab('{{ $tab['id'] }}')"
            id="tab-btn-{{ $tab['id'] }}"
            class="gw-tab-btn flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-bold transition-all
                   {{ $i === 0 ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300' }}">
            <i class="fas {{ $tab['icon'] }}"></i>
            <span class="hidden sm:inline">{{ $tab['label'] }}</span>
        </button>
    @endforeach
</div>

<div class="space-y-6">

    {{-- ============================================================
         TAB 1: AUTENTICAÇÃO
         ============================================================ --}}
    <div id="gw-auth" class="gw-tab-panel space-y-5">

        {{-- Segmented control: Ambiente --}}
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Ambiente de Execução</label>
            <div class="flex bg-slate-100 dark:bg-slate-900 p-1 rounded-2xl border border-slate-200 dark:border-slate-800">
                <button type="button" id="env-btn-sandbox"
                    onclick="switchEnv('sandbox')"
                    class="env-seg-btn flex-1 py-2.5 px-4 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2
                           {{ $mpEnv !== 'production' ? 'bg-white dark:bg-slate-800 text-amber-600 dark:text-amber-400 shadow-sm border border-slate-200 dark:border-slate-700' : 'text-slate-500 dark:text-slate-400' }}">
                    <i class="fas fa-flask text-xs"></i> Sandbox
                    <span class="text-[10px] font-normal opacity-70 hidden sm:inline">(testes)</span>
                </button>
                <button type="button" id="env-btn-production"
                    onclick="switchEnv('production')"
                    class="env-seg-btn flex-1 py-2.5 px-4 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2
                           {{ $mpEnv === 'production' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm border border-slate-200 dark:border-slate-700' : 'text-slate-500 dark:text-slate-400' }}">
                    <i class="fas fa-rocket text-xs"></i> Produção
                    <span class="text-[10px] font-normal opacity-70 hidden sm:inline">(real)</span>
                </button>
            </div>
            {{-- Hidden real select para envio do form --}}
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
                <span class="text-xs font-black text-amber-700 dark:text-amber-400 uppercase tracking-wider">Credenciais de Sandbox</span>
                <span class="ml-auto text-[10px] text-amber-500 font-semibold">Ambiente de testes — não realiza cobranças reais</span>
            </div>
            <div class="p-4 bg-white dark:bg-slate-900 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Public Key</label>
                    <input type="text" name="mercadopago_sandbox_public_key"
                        value="{{ $settings['mercadopago_sandbox_public_key'] ?? '' }}"
                        placeholder="TEST-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-amber-400 focus:ring-4 focus:ring-amber-400/10 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white placeholder:text-slate-300 dark:placeholder:text-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Access Token</label>
                    <div class="relative">
                        <input type="password" name="mercadopago_sandbox_access_token" id="sb_token"
                            value="{{ $settings['mercadopago_sandbox_access_token'] ?? '' }}"
                            placeholder="TEST-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                            class="w-full px-4 py-3 pr-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-amber-400 focus:ring-4 focus:ring-amber-400/10 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white placeholder:text-slate-300 dark:placeholder:text-slate-700">
                        <button type="button" onclick="toggleReveal('sb_token', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
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
                <span class="text-xs font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Credenciais de Produção</span>
                <span class="ml-auto text-[10px] text-emerald-600 font-semibold hidden sm:inline">Ambiente real — cobranças reais serão processadas</span>
            </div>
            <div class="p-4 bg-white dark:bg-slate-900 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Public Key</label>
                    <input type="text" name="mercadopago_prod_public_key"
                        value="{{ $settings['mercadopago_prod_public_key'] ?? '' }}"
                        placeholder="APP_USR-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white placeholder:text-slate-300 dark:placeholder:text-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Access Token</label>
                    <div class="relative">
                        <input type="password" name="mercadopago_prod_access_token" id="prod_token"
                            value="{{ $settings['mercadopago_prod_access_token'] ?? '' }}"
                            placeholder="APP_USR-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                            class="w-full px-4 py-3 pr-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white placeholder:text-slate-300 dark:placeholder:text-slate-700">
                        <button type="button" onclick="toggleReveal('prod_token', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
            {{-- Botão de teste de conexão --}}
            <div class="px-4 pb-4 bg-white dark:bg-slate-900">
                <button type="button" onclick="testGatewayConnection('mercadopago')" id="btn-test-mp"
                    class="w-full flex items-center justify-center gap-2 py-3 px-6 rounded-xl font-bold text-sm transition-all
                           bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-600/20 active:scale-[0.98]">
                    <i class="fas fa-plug"></i>
                    Testar Conexão com MercadoPago
                </button>
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
                    <p class="text-[10px] text-slate-400">Cole este endereço no painel do MercadoPago &rsaquo; Notificações IPN</p>
                </div>
            </div>
            <div class="flex rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                <input type="text" readonly value="{{ route('api.webhooks.mercadopago') }}"
                    class="flex-1 px-4 py-2.5 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-mono focus:outline-none border-0">
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
                <span class="ml-auto text-[10px] text-blue-500 font-semibold hidden sm:inline">Obtenha em: Painel MP → Aplicações</span>
            </div>
            <div class="p-4 bg-white dark:bg-slate-900 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Client ID (App ID)</label>
                    <input type="text" name="mercadopago_client_id"
                        value="{{ $settings['mercadopago_client_id'] ?? '' }}"
                        placeholder="1234567890123456"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white placeholder:text-slate-300 dark:placeholder:text-slate-700">
                    <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                        <i class="fas fa-external-link-alt text-[8px]"></i>
                        <a href="https://www.mercadopago.com.br/developers/panel/applications" target="_blank"
                           class="text-blue-500 hover:underline">Painel MP → Aplicação → Detalhes</a>
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Client Secret</label>
                    <div class="relative">
                        <input type="password" name="mercadopago_client_secret" id="mp_secret"
                            value="{{ $settings['mercadopago_client_secret'] ?? '' }}"
                            placeholder="••••••••••••••••••••••••••••••••"
                            class="w-full px-4 py-3 pr-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white placeholder:text-slate-300 dark:placeholder:text-slate-700">
                        <button type="button" onclick="toggleReveal('mp_secret', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                    <p class="text-[10px] text-red-500 mt-1.5 font-bold flex items-center gap-1">
                        <i class="fas fa-exclamation-circle"></i> Obrigatório para split de pagamento
                    </p>
                </div>
            </div>
        </div>

    </div>{{-- /gw-auth --}}

    {{-- ============================================================
         TAB 2: MÉTODOS DE PAGAMENTO
         ============================================================ --}}
    <div id="gw-methods" class="gw-tab-panel hidden">
        <p class="text-xs text-slate-400 mb-4 font-medium">Ative ou desative os métodos disponíveis no checkout para seus clientes.</p>
        @php
            $methods = [
                ['name' => 'mercadopago_method_credit_card', 'label' => 'Cartão de Crédito', 'desc' => 'Parcele em até 12x', 'icon' => 'fa-credit-card', 'color' => 'blue', 'default' => 1],
                ['name' => 'mercadopago_method_debit_card',  'label' => 'Cartão de Débito',  'desc' => 'Débito à vista',    'icon' => 'fa-credit-card', 'color' => 'violet', 'default' => 0],
                ['name' => 'mercadopago_method_pix',         'label' => 'Pix',                'desc' => 'Aprovação imediata','icon' => 'fa-pix',         'color' => 'teal', 'default' => 1, 'brand' => true],
                ['name' => 'mercadopago_method_ticket',      'label' => 'Boleto Bancário',    'desc' => 'Prazo de 1–3 dias', 'icon' => 'fa-barcode',     'color' => 'orange', 'default' => 0],
                ['name' => 'mercadopago_method_mercadopago', 'label' => 'Carteira MP',        'desc' => 'Saldo MercadoPago', 'icon' => 'fa-wallet',      'color' => 'sky', 'default' => 0],
            ];
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($methods as $m)
                @php $checked = ($settings[$m['name']] ?? $m['default']); @endphp
                <label for="method_{{ $m['name'] }}"
                    class="method-card relative flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all select-none
                           {{ $checked ? 'border-' . $m['color'] . '-400 bg-' . $m['color'] . '-50 dark:bg-' . $m['color'] . '-900/15 dark:border-' . $m['color'] . '-600/50' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-slate-300 dark:hover:border-slate-700' }}">
                    <input type="hidden" name="{{ $m['name'] }}" value="0">
                    <input type="checkbox" id="method_{{ $m['name'] }}" class="sr-only method-cb" name="{{ $m['name'] }}" value="1"
                        data-color="{{ $m['color'] }}"
                        onchange="onMethodToggle(this)"
                        {{ $checked ? 'checked' : '' }}>

                    {{-- Icon --}}
                    <div class="flex-shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center transition-all
                                {{ $checked ? 'bg-' . $m['color'] . '-100 dark:bg-' . $m['color'] . '-800/30' : 'bg-slate-100 dark:bg-slate-800' }}">
                        <i class="{{ !empty($m['brand']) ? 'fa-brands' : 'fas' }} {{ $m['icon'] }} text-xl
                                  {{ $checked ? 'text-' . $m['color'] . '-600 dark:text-' . $m['color'] . '-400' : 'text-slate-400' }}"></i>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-black text-sm {{ $checked ? 'text-' . $m['color'] . '-800 dark:text-' . $m['color'] . '-300' : 'text-slate-700 dark:text-slate-300' }}">
                            {{ $m['label'] }}
                        </p>
                        <p class="text-xs {{ $checked ? 'text-' . $m['color'] . '-500' : 'text-slate-400' }}">{{ $m['desc'] }}</p>
                    </div>

                    {{-- Status badge --}}
                    <div class="flex-shrink-0">
                        <span class="method-status-badge px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                     {{ $checked ? 'bg-' . $m['color'] . '-100 dark:bg-' . $m['color'] . '-900/30 text-' . $m['color'] . '-700 dark:text-' . $m['color'] . '-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }}">
                            {{ $checked ? 'Ativo' : 'Off' }}
                        </span>
                    </div>
                </label>
            @endforeach
        </div>
    </div>{{-- /gw-methods --}}

    {{-- ============================================================
         TAB 3: COBRANÇA
         ============================================================ --}}
    <div id="gw-billing" class="gw-tab-panel hidden space-y-5">

        {{-- Checkout transparente --}}
        <label for="gateway_transparent_checkout"
            class="flex items-start gap-4 p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 cursor-pointer hover:border-blue-300 dark:hover:border-blue-700 transition-colors group">
            <input type="hidden" name="gateway_transparent_checkout" value="0">
            <input id="gateway_transparent_checkout" name="gateway_transparent_checkout" type="checkbox" value="1"
                class="mt-0.5 h-5 w-5 rounded-lg border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 dark:bg-slate-900 cursor-pointer"
                {{ ($settings['gateway_transparent_checkout'] ?? 0) ? 'checked' : '' }}>
            <div>
                <p class="font-black text-slate-800 dark:text-white group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
                    Checkout Transparente
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    O usuário paga sem sair do seu site. Se desativado, será redirecionado para a página do MercadoPago.
                </p>
            </div>
        </label>

        {{-- Grid de 4 campos --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-xl bg-orange-100 dark:bg-orange-900/20 flex items-center justify-center">
                        <i class="fas fa-percent text-orange-500 text-xs"></i>
                    </div>
                    <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">Juros de Parcelas</label>
                </div>
                <div class="relative">
                    <input type="number" step="0.01" name="gateway_installment_tax"
                        value="{{ $settings['gateway_installment_tax'] ?? '0.00' }}"
                        class="w-full px-4 py-3 pr-8 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-orange-400 focus:ring-4 focus:ring-orange-400/10 outline-none transition-all font-bold text-sm text-slate-800 dark:text-white">
                    <span class="absolute inset-y-0 right-3 flex items-center text-slate-400 text-sm pointer-events-none">%</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1.5">0.00 = usa config do gateway</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center">
                        <i class="fas fa-layer-group text-purple-500 text-xs"></i>
                    </div>
                    <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">Parcelas sem Juros</label>
                </div>
                <input type="number" name="gateway_max_installments_no_interest"
                    value="{{ $settings['gateway_max_installments_no_interest'] ?? '1' }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-purple-400 focus:ring-4 focus:ring-purple-400/10 outline-none transition-all font-bold text-sm text-slate-800 dark:text-white">
                <p class="text-[10px] text-slate-400 mt-1.5">Máx. parcelas sem acréscimo</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                        <i class="fas fa-exchange-alt text-blue-500 text-xs"></i>
                    </div>
                    <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">Repassar Taxas</label>
                </div>
                <select name="gateway_pass_tax_to_client"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-sm text-slate-800 dark:text-white">
                    <option value="0" {{ ($settings['gateway_pass_tax_to_client'] ?? 0) == 0 ? 'selected' : '' }}>Não — empresa absorve</option>
                    <option value="1" {{ ($settings['gateway_pass_tax_to_client'] ?? 0) == 1 ? 'selected' : '' }}>Sim — cliente paga</option>
                </select>
                <p class="text-[10px] text-slate-400 mt-1.5">Taxas de parcelamento</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/20 flex items-center justify-center">
                        <i class="fas fa-store text-emerald-500 text-xs"></i>
                    </div>
                    <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">Taxa Marketplace</label>
                </div>
                <div class="relative">
                    <input type="number" step="0.01" name="marketplace_fee"
                        value="{{ $settings['marketplace_fee'] ?? '10.00' }}"
                        class="w-full px-4 py-3 pr-8 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-400/10 outline-none transition-all font-bold text-sm text-slate-800 dark:text-white">
                    <span class="absolute inset-y-0 right-3 flex items-center text-slate-400 text-sm pointer-events-none">%</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-1.5">Comissão sobre cada venda</p>
            </div>
        </div>

    </div>{{-- /gw-billing --}}

    {{-- ============================================================
         TAB 4: CHECKOUT
         ============================================================ --}}
    <div id="gw-checkout" class="gw-tab-panel hidden">
        <p class="text-xs text-slate-400 mb-5 font-medium">Personalize a aparência do checkout para combinar com sua marca.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-900/20 flex items-center justify-center">
                        <i class="fas fa-palette text-indigo-500 text-xs"></i>
                    </div>
                    <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">Tema do Checkout</label>
                </div>
                <select name="gateway_checkout_theme"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all font-bold text-sm text-slate-800 dark:text-white">
                    <option value="default"   {{ ($settings['gateway_checkout_theme'] ?? 'default') == 'default'   ? 'selected' : '' }}>Padrão (MercadoPago)</option>
                    <option value="dark"      {{ ($settings['gateway_checkout_theme'] ?? '') == 'dark'      ? 'selected' : '' }}>Escuro (Dark)</option>
                    <option value="bootstrap" {{ ($settings['gateway_checkout_theme'] ?? '') == 'bootstrap' ? 'selected' : '' }}>Bootstrap</option>
                    <option value="flat"      {{ ($settings['gateway_checkout_theme'] ?? '') == 'flat'      ? 'selected' : '' }}>Flat (Moderno)</option>
                </select>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-xl bg-pink-100 dark:bg-pink-900/20 flex items-center justify-center">
                        <i class="fas fa-brush text-pink-500 text-xs"></i>
                    </div>
                    <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">Cor Primária (Botões)</label>
                </div>
                <div class="flex gap-3 items-center">
                    <input type="color" name="gateway_checkout_primary_color"
                        value="{{ $settings['gateway_checkout_primary_color'] ?? '#1F5EDB' }}"
                        class="h-11 w-14 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer flex-shrink-0"
                        oninput="document.getElementById('gateway_color_text').value = this.value">
                    <div class="flex-1">
                        <input type="text" id="gateway_color_text"
                            value="{{ $settings['gateway_checkout_primary_color'] ?? '#1F5EDB' }}" readonly
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-white text-sm font-mono outline-none">
                        <p class="text-[10px] text-slate-400 mt-1">HEX da cor principal do checkout</p>
                    </div>
                </div>
            </div>
        </div>
    </div>{{-- /gw-checkout --}}

    {{-- ============================================================
         TAB 5: AVANÇADO
         ============================================================ --}}
    <div id="gw-advanced" class="gw-tab-panel hidden">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="bg-slate-50 dark:bg-slate-900 px-4 py-3 border-b border-slate-200 dark:border-slate-800 flex items-center gap-2">
                <i class="fas fa-fingerprint text-slate-400 text-sm"></i>
                <div>
                    <span class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-wider">Identificação da Plataforma</span>
                    <p class="text-[10px] text-slate-400">IDs de rastreamento de qualidade do MercadoPago (opcional)</p>
                </div>
            </div>
            <div class="p-4 bg-white dark:bg-slate-900 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Integrator ID</label>
                    <input type="text" name="mercadopago_integrator_id"
                        value="{{ $settings['mercadopago_integrator_id'] ?? '' }}" placeholder="dev_1234567890"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white placeholder:text-slate-300 dark:placeholder:text-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Platform ID</label>
                    <input type="text" name="mercadopago_platform_id"
                        value="{{ $settings['mercadopago_platform_id'] ?? '' }}" placeholder="plat_1234567890"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-mono text-sm text-slate-800 dark:text-white placeholder:text-slate-300 dark:placeholder:text-slate-700">
                </div>
            </div>
        </div>
    </div>{{-- /gw-advanced --}}

</div>{{-- /space-y-6 wrapper --}}

@push('scripts')
    <script>
        /* ───────── TABS ───────── */
        function switchGwTab(activeId) {
            document.querySelectorAll('.gw-tab-panel').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.gw-tab-btn').forEach(b => {
                b.classList.remove('bg-white', 'dark:bg-slate-800', 'text-blue-600', 'dark:text-blue-400', 'shadow-sm');
                b.classList.add('text-slate-500', 'dark:text-slate-400');
            });
            document.getElementById(activeId).classList.remove('hidden');
            const btn = document.getElementById('tab-btn-' + activeId);
            btn.classList.remove('text-slate-500', 'dark:text-slate-400');
            btn.classList.add('bg-white', 'dark:bg-slate-800', 'text-blue-600', 'dark:text-blue-400', 'shadow-sm');
        }

        /* ───────── ENV SWITCHER ───────── */
        @php $mpEnvJs = ($settings['mercadopago_env'] ?? 'sandbox'); @endphp
        function switchEnv(env) {
            const isSandbox = env === 'sandbox';
            document.getElementById('env-sandbox-fields').classList.toggle('hidden', !isSandbox);
            document.getElementById('env-production-fields').classList.toggle('hidden', isSandbox);
            document.getElementById('mercadopago_env_select').value = env;

            // Segmented control visual
            const sbBtn = document.getElementById('env-btn-sandbox');
            const prBtn = document.getElementById('env-btn-production');
            if (isSandbox) {
                sbBtn.classList.add('bg-white', 'dark:bg-slate-800', 'text-amber-600', 'dark:text-amber-400', 'shadow-sm', 'border', 'border-slate-200', 'dark:border-slate-700');
                sbBtn.classList.remove('text-slate-500', 'dark:text-slate-400');
                prBtn.classList.remove('bg-white', 'dark:bg-slate-800', 'text-emerald-600', 'dark:text-emerald-400', 'shadow-sm', 'border', 'border-slate-200', 'dark:border-slate-700');
                prBtn.classList.add('text-slate-500', 'dark:text-slate-400');
            } else {
                prBtn.classList.add('bg-white', 'dark:bg-slate-800', 'text-emerald-600', 'dark:text-emerald-400', 'shadow-sm', 'border', 'border-slate-200', 'dark:border-slate-700');
                prBtn.classList.remove('text-slate-500', 'dark:text-slate-400');
                sbBtn.classList.remove('bg-white', 'dark:bg-slate-800', 'text-amber-600', 'dark:text-amber-400', 'shadow-sm', 'border', 'border-slate-200', 'dark:border-slate-700');
                sbBtn.classList.add('text-slate-500', 'dark:text-slate-400');
            }

            // Hero badge
            const badge = document.getElementById('hero-env-badge');
            if (badge) {
                badge.innerHTML = isSandbox
                    ? '<span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span> Sandbox'
                    : '<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Produção';
                badge.className = badge.className.replace(/bg-\w+-400\/20|text-\w+-200|border-\w+-400\/30/g, '');
                if (isSandbox) {
                    badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-400/20 text-amber-200 border border-amber-400/30';
                } else {
                    badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-400/20 text-emerald-200 border border-emerald-400/30';
                }
            }
        }
        // Init env on page load
        document.addEventListener('DOMContentLoaded', function () {
            switchEnv('{{ $mpEnvJs }}');
        });

        /* ───────── REVEAL/HIDE PASSWORD ───────── */
        function toggleReveal(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon  = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        /* ───────── METHOD CARD TOGGLE ───────── */
        function onMethodToggle(cb) {
            const card  = cb.closest('label');
            const color = cb.dataset.color;
            const badge = card.querySelector('.method-status-badge');
            const icon  = card.querySelector('i.fa-brands, i.fas');
            const iconBox = icon.closest('div');
            const title = card.querySelector('p.font-black');
            const desc  = card.querySelectorAll('p')[1];

            if (cb.checked) {
                card.className = card.className
                    .replace(/border-slate-200 dark:border-slate-800/g, '')
                    .replace(/bg-white dark:bg-slate-900 hover:border-slate-300 dark:hover:border-slate-700/g, '');
                card.classList.add(`border-${color}-400`, `bg-${color}-50`, `dark:bg-${color}-900/15`, `dark:border-${color}-600/50`);
                iconBox.className = iconBox.className.replace('bg-slate-100 dark:bg-slate-800', `bg-${color}-100 dark:bg-${color}-800/30`);
                icon.classList.remove('text-slate-400');
                icon.classList.add(`text-${color}-600`, `dark:text-${color}-400`);
                title.className = title.className.replace('text-slate-700 dark:text-slate-300', `text-${color}-800 dark:text-${color}-300`);
                badge.className = badge.className.replace(/bg-slate-100 dark:bg-slate-800 text-slate-400/g, '');
                badge.classList.add(`bg-${color}-100`, `dark:bg-${color}-900/30`, `text-${color}-700`, `dark:text-${color}-400`);
                badge.textContent = 'Ativo';
            } else {
                card.classList.remove(`border-${color}-400`, `bg-${color}-50`, `dark:bg-${color}-900/15`, `dark:border-${color}-600/50`);
                card.classList.add('border-slate-200', 'dark:border-slate-800', 'bg-white', 'dark:bg-slate-900', 'hover:border-slate-300', 'dark:hover:border-slate-700');
                iconBox.className = iconBox.className.replace(`bg-${color}-100 dark:bg-${color}-800/30`, 'bg-slate-100 dark:bg-slate-800');
                icon.classList.add('text-slate-400');
                icon.classList.remove(`text-${color}-600`, `dark:text-${color}-400`);
                title.className = title.className.replace(`text-${color}-800 dark:text-${color}-300`, 'text-slate-700 dark:text-slate-300');
                badge.classList.remove(`bg-${color}-100`, `dark:bg-${color}-900/30`, `text-${color}-700`, `dark:text-${color}-400`);
                badge.classList.add('bg-slate-100', 'dark:bg-slate-800', 'text-slate-400');
                badge.textContent = 'Off';
            }
            toggleSetting(cb.name, cb.checked);
        }

        /* ───────── CLIPBOARD ───────── */
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function () {
                const Toast = Swal.mixin({
                    toast: true, position: 'top-end',
                    showConfirmButton: false, timer: 3000, timerProgressBar: true
                });
                Toast.fire({ icon: 'success', title: 'Copiado para a área de transferência!' });
            }, function (err) { console.error('Could not copy text: ', err); });
        }

        /* ───────── TEST CONNECTION ───────── */
        document.addEventListener('DOMContentLoaded', function () {
            window.testGatewayConnection = function (gateway) {
                const btn = document.getElementById('btn-test-mp');
                const orig = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Testando conexão...';

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
                    body: JSON.stringify({ gateway: 'mercadopago', access_token: token, env: env, _token: '{{ csrf_token() }}' })
                })
                .then(r => r.json())
                .then(data => {
                    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true });
                    if (data.success) {
                        Toast.fire({ icon: 'success', title: data.message });
                        btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Conexão OK';
                        btn.classList.replace('bg-emerald-600', 'bg-emerald-700');
                        setTimeout(() => { btn.innerHTML = orig; btn.classList.replace('bg-emerald-700', 'bg-emerald-600'); }, 3000);
                    } else {
                        Toast.fire({ icon: 'error', title: data.message });
                        btn.innerHTML = '<i class="fas fa-times-circle mr-2"></i> Falhou — verifique o token';
                        btn.classList.replace('bg-emerald-600', 'bg-red-600');
                        setTimeout(() => { btn.innerHTML = orig; btn.classList.replace('bg-red-600', 'bg-emerald-600'); }, 4000);
                    }
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: 'Erro', text: 'Falha na requisição. Verifique o console.' });
                    btn.innerHTML = orig;
                })
                .finally(() => { btn.disabled = false; });
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
                            text: 'Nao foi possivel atualizar a configuracao.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Ocorreu um erro na requisicao.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                });
        }
    </script>
@endpush