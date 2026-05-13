{{-- ============================================================
     GATEWAYS DE PAGAMENTO — MercadoPago + SumUp (v3)
     AJAX save, dark mode fix, layout limpo
     ============================================================ --}}

@php
    $mpEnv = $settings['mercadopago_env'] ?? 'sandbox';
    $mpEnabled = (int) ($settings['mercadopago_enabled'] ?? 1) === 1;
    $sumupEnabled = (int) ($settings['sumup_enabled'] ?? 0) === 1;
@endphp

<style>
/* Forçar inputs com cores legíveis em ambos os modos */
.gw-input,
.gw-select {
    color: #0f172a !important; /* slate-900 */
    background-color: #f8fafc !important; /* slate-50 */
    border: 1px solid #e2e8f0 !important; /* slate-200 */
}
.dark .gw-input,
.dark .gw-select {
    color: #f1f5f9 !important; /* slate-100 */
    background-color: #1e293b !important; /* slate-800 */
    border-color: #334155 !important; /* slate-700 */
}
.gw-input:focus,
.gw-select:focus {
    outline: none;
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59,130,246,.15);
}
.gw-input::placeholder { color: #94a3b8; }
.dark .gw-input::placeholder { color: #64748b; }

/* Cards uniformes */
.gw-card {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.25rem;
}
.dark .gw-card {
    background-color: #0f172a;
    border-color: #1e293b;
}

.gw-label {
    display: block;
    font-size: .65rem;
    font-weight: 900;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: .5rem;
}
.dark .gw-label { color: #94a3b8; }
</style>

{{-- HEADER GERAL --}}
<div class="relative overflow-hidden rounded-2xl mb-6"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);">
    <div class="relative p-6 flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center border border-white/10">
            <i class="fas fa-money-check-alt text-white text-2xl"></i>
        </div>
        <div>
            <p class="text-slate-300 text-xs font-bold uppercase tracking-widest mb-0.5">Configurações</p>
            <h2 class="text-white text-2xl font-black leading-tight">Gateways de Pagamento</h2>
            <p class="text-slate-400 text-xs mt-1">Configure MercadoPago e SumUp</p>
        </div>
    </div>
</div>

{{-- TABS PRINCIPAIS --}}
<div class="flex gap-1 bg-slate-100 dark:bg-slate-900 p-1 rounded-2xl mb-6" role="tablist">
    <button type="button" onclick="gwSwitchMain('mercadopago-tab')" id="main-btn-mercadopago-tab"
        class="main-gateway-tab-btn flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-bold transition-all bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm">
        <i class="fas fa-handshake"></i>
        <span>MercadoPago</span>
        @if($mpEnabled)
            <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400" id="mp-status-badge">ATIVO</span>
        @else
            <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-500" id="mp-status-badge">OFF</span>
        @endif
    </button>
    <button type="button" onclick="gwSwitchMain('sumup-tab')" id="main-btn-sumup-tab"
        class="main-gateway-tab-btn flex-1 flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-bold transition-all text-slate-500 dark:text-slate-400">
        <i class="fas fa-credit-card"></i>
        <span>SumUp</span>
        @if($sumupEnabled)
            <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400" id="sumup-status-badge">ATIVO</span>
        @else
            <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-500" id="sumup-status-badge">OFF</span>
        @endif
    </button>
</div>

{{-- ============================================================
     TAB MERCADOPAGO
     ============================================================ --}}
<div id="mercadopago-tab" class="main-gateway-panel">
    {{-- Header MP --}}
    <div class="relative overflow-hidden rounded-2xl mb-6"
         style="background: linear-gradient(135deg, #1548c0 0%, #2563eb 45%, #3b60d6 100%);">
        <div class="relative flex items-start justify-between p-6 gap-4 flex-wrap">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center border border-white/20">
                    <i class="fas fa-handshake text-white text-2xl"></i>
                </div>
                <div>
                    <p class="text-blue-200 text-xs font-bold uppercase tracking-widest mb-0.5">Gateway</p>
                    <h2 class="text-white text-2xl font-black leading-tight">MercadoPago</h2>
                    <p class="text-blue-200 text-xs mt-1">Checkout transparente &bull; Split &bull; OAuth</p>
                </div>
            </div>
            <div class="flex flex-col items-end gap-2 pt-1">
                <span id="mp-env-badge"
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
                    <div class="w-11 h-6 bg-white/20 rounded-full peer border border-white/30
                                peer-checked:after:translate-x-full peer-checked:after:border-white
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white
                                after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5
                                after:transition-all peer-checked:bg-emerald-500 relative"></div>
                </label>
            </div>
        </div>
    </div>

    {{-- Sub-tabs MP --}}
    <div class="flex gap-1 bg-slate-100 dark:bg-slate-900 p-1 rounded-2xl mb-6 overflow-x-auto">
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
            <button type="button" onclick="gwSwitchSub('mp', '{{ $tab['id'] }}')"
                id="mp-btn-{{ $tab['id'] }}"
                class="mp-subtab-btn flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-bold transition-all whitespace-nowrap
                       {{ $i === 0 ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-500 dark:text-slate-400' }}">
                <i class="fas {{ $tab['icon'] }}"></i>
                <span class="hidden sm:inline">{{ $tab['label'] }}</span>
            </button>
        @endforeach
    </div>

    {{-- MP > AUTH --}}
    <div id="mp-auth" class="mp-subpanel space-y-5">
        <div class="gw-card">
            <label class="gw-label">Ambiente</label>
            <div class="flex bg-slate-100 dark:bg-slate-900 p-1 rounded-2xl">
                <button type="button" id="env-btn-sandbox" onclick="gwSwitchEnv('sandbox')"
                    class="flex-1 py-2.5 px-4 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2
                           {{ $mpEnv !== 'production' ? 'bg-white dark:bg-slate-800 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-500' }}">
                    <i class="fas fa-flask text-xs"></i> Sandbox
                </button>
                <button type="button" id="env-btn-production" onclick="gwSwitchEnv('production')"
                    class="flex-1 py-2.5 px-4 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2
                           {{ $mpEnv === 'production' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-500' }}">
                    <i class="fas fa-rocket text-xs"></i> Produção
                </button>
            </div>
            <select name="mercadopago_env" id="mercadopago_env_select" class="hidden">
                <option value="sandbox" {{ $mpEnv !== 'production' ? 'selected' : '' }}>sandbox</option>
                <option value="production" {{ $mpEnv === 'production' ? 'selected' : '' }}>production</option>
            </select>
        </div>

        {{-- Sandbox --}}
        <div id="env-sandbox-fields" class="gw-card {{ $mpEnv === 'production' ? 'hidden' : '' }}" style="border-color: #fbbf24;">
            <div class="flex items-center gap-2 mb-3">
                <i class="fas fa-flask text-amber-500 text-xs"></i>
                <span class="text-xs font-black text-amber-700 dark:text-amber-400 uppercase tracking-wider">Credenciais Sandbox</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="gw-label">Public Key</label>
                    <input type="text" name="mercadopago_sandbox_public_key"
                        value="{{ $settings['mercadopago_sandbox_public_key'] ?? '' }}"
                        placeholder="TEST-xxxxxxxx"
                        class="gw-input w-full px-4 py-3 rounded-xl font-mono text-sm">
                </div>
                <div>
                    <label class="gw-label">Access Token</label>
                    <div class="relative">
                        <input type="password" name="mercadopago_sandbox_access_token" id="sb_token"
                            value="{{ $settings['mercadopago_sandbox_access_token'] ?? '' }}"
                            placeholder="TEST-xxxxxxxx"
                            class="gw-input w-full px-4 py-3 pr-11 rounded-xl font-mono text-sm">
                        <button type="button" onclick="gwReveal('sb_token', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Produção --}}
        <div id="env-production-fields" class="gw-card {{ $mpEnv !== 'production' ? 'hidden' : '' }}" style="border-color: #10b981;">
            <div class="flex items-center gap-2 mb-3">
                <i class="fas fa-rocket text-emerald-500 text-xs"></i>
                <span class="text-xs font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Credenciais Produção</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="gw-label">Public Key</label>
                    <input type="text" name="mercadopago_prod_public_key"
                        value="{{ $settings['mercadopago_prod_public_key'] ?? '' }}"
                        placeholder="APP_USR-xxxxxxxx"
                        class="gw-input w-full px-4 py-3 rounded-xl font-mono text-sm">
                </div>
                <div>
                    <label class="gw-label">Access Token</label>
                    <div class="relative">
                        <input type="password" name="mercadopago_prod_access_token" id="prod_token"
                            value="{{ $settings['mercadopago_prod_access_token'] ?? '' }}"
                            placeholder="APP_USR-xxxxxxxx"
                            class="gw-input w-full px-4 py-3 pr-11 rounded-xl font-mono text-sm">
                        <button type="button" onclick="gwReveal('prod_token', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Webhook MP --}}
        <div class="gw-card">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                    <i class="fas fa-link text-indigo-500 text-xs"></i>
                </div>
                <div>
                    <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">Webhook URL</span>
                    <p class="text-[10px] text-slate-400">Cole no painel MercadoPago &gt; Notificações</p>
                </div>
            </div>
            <div class="flex rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                <input type="text" readonly value="{{ route('api.webhooks.mercadopago') }}"
                    class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-mono border-0">
                <button type="button" onclick="gwCopy('{{ route('api.webhooks.mercadopago') }}')"
                    class="px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold">
                    <i class="fas fa-copy"></i> Copiar
                </button>
            </div>
        </div>

        {{-- OAuth --}}
        <div class="gw-card" style="border-color: #3b82f6;">
            <div class="flex items-center gap-2 mb-3">
                <i class="fas fa-shield-alt text-blue-500 text-xs"></i>
                <span class="text-xs font-black text-blue-700 dark:text-blue-400 uppercase tracking-wider">App OAuth</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="gw-label">Client ID</label>
                    <input type="text" name="mercadopago_client_id"
                        value="{{ $settings['mercadopago_client_id'] ?? '' }}"
                        placeholder="1234567890123456"
                        class="gw-input w-full px-4 py-3 rounded-xl font-mono text-sm">
                </div>
                <div>
                    <label class="gw-label">Client Secret</label>
                    <div class="relative">
                        <input type="password" name="mercadopago_client_secret" id="mp_secret"
                            value="{{ $settings['mercadopago_client_secret'] ?? '' }}"
                            placeholder="••••••••••••••••"
                            class="gw-input w-full px-4 py-3 pr-11 rounded-xl font-mono text-sm">
                        <button type="button" onclick="gwReveal('mp_secret', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" onclick="gwTestMP()" id="btn-test-mp"
            class="w-full flex items-center justify-center gap-2 py-3 px-6 rounded-xl font-bold text-sm transition-all
                   bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg">
            <i class="fas fa-plug"></i> Testar Conexão MercadoPago
        </button>
    </div>

    {{-- MP > METHODS --}}
    <div id="mp-methods" class="mp-subpanel hidden">
        <p class="text-xs text-slate-400 mb-4 font-medium">Ative ou desative os métodos disponíveis no checkout.</p>
        @php
            $mpMethods = [
                ['name' => 'mercadopago_method_credit_card', 'label' => 'Cartão de Crédito', 'desc' => 'Até 12x', 'icon' => 'fa-credit-card', 'color' => 'blue', 'default' => 1],
                ['name' => 'mercadopago_method_debit_card',  'label' => 'Cartão de Débito',  'desc' => 'À vista',    'icon' => 'fa-credit-card', 'color' => 'violet', 'default' => 0],
                ['name' => 'mercadopago_method_pix',         'label' => 'Pix',               'desc' => 'Imediato','icon' => 'fa-pix',         'color' => 'teal', 'default' => 1, 'brand' => true],
                ['name' => 'mercadopago_method_ticket',      'label' => 'Boleto',            'desc' => '1–3 dias', 'icon' => 'fa-barcode',     'color' => 'orange', 'default' => 0],
                ['name' => 'mercadopago_method_mercadopago', 'label' => 'Carteira MP',       'desc' => 'Saldo MP', 'icon' => 'fa-wallet',      'color' => 'sky', 'default' => 0],
            ];
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($mpMethods as $m)
                @php $checked = (int) ($settings[$m['name']] ?? $m['default']) === 1; @endphp
                <label class="gw-card flex items-center gap-4 cursor-pointer transition-all hover:border-blue-400 {{ $checked ? '!border-blue-500' : '' }}">
                    <input type="hidden" name="{{ $m['name'] }}" value="0">
                    <input type="checkbox" class="h-5 w-5 rounded border-slate-300 text-blue-600" name="{{ $m['name'] }}" value="1"
                        {{ $checked ? 'checked' : '' }}>
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-slate-100 dark:bg-slate-800">
                        <i class="{{ !empty($m['brand']) ? 'fa-brands' : 'fas' }} {{ $m['icon'] }} text-xl text-slate-500"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-black text-sm text-slate-700 dark:text-slate-200">{{ $m['label'] }}</p>
                        <p class="text-xs text-slate-500">{{ $m['desc'] }}</p>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    {{-- MP > BILLING --}}
    <div id="mp-billing" class="mp-subpanel hidden space-y-4">
        <label class="gw-card flex items-start gap-4 cursor-pointer">
            <input type="hidden" name="gateway_transparent_checkout" value="0">
            <input type="checkbox" name="gateway_transparent_checkout" value="1"
                class="mt-0.5 h-5 w-5 rounded border-slate-300 text-blue-600"
                {{ ($settings['gateway_transparent_checkout'] ?? 0) ? 'checked' : '' }}>
            <div>
                <p class="font-black text-slate-800 dark:text-white">Checkout Transparente</p>
                <p class="text-xs text-slate-500">Pagar sem sair do site. Se desativado, redireciona para MercadoPago.</p>
            </div>
        </label>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="gw-card">
                <label class="gw-label">Taxa de Juros por Parcela (%)</label>
                <input type="number" min="0" max="99.99" step="0.01" name="mercadopago_installment_tax"
                    value="{{ $settings['mercadopago_installment_tax'] ?? '0.00' }}"
                    class="gw-input w-full px-4 py-3 rounded-xl font-bold text-sm">
            </div>
            <div class="gw-card">
                <label class="gw-label">Máximo de Parcelas</label>
                <input type="number" min="1" max="12" name="mercadopago_max_installments"
                    value="{{ $settings['mercadopago_max_installments'] ?? '12' }}"
                    class="gw-input w-full px-4 py-3 rounded-xl font-bold text-sm">
            </div>
            <div class="gw-card">
                <label class="gw-label">Parcelas sem Juros</label>
                <input type="number" min="1" max="12" name="mercadopago_installments_no_interest"
                    value="{{ $settings['mercadopago_installments_no_interest'] ?? '1' }}"
                    class="gw-input w-full px-4 py-3 rounded-xl font-bold text-sm">
            </div>
            <div class="gw-card">
                <label class="gw-label">Repassar Taxa</label>
                <select name="gateway_pass_tax_to_client" class="gw-select w-full px-4 py-3 rounded-xl text-sm font-semibold">
                    <option value="0" {{ (int) ($settings['gateway_pass_tax_to_client'] ?? 0) === 0 ? 'selected' : '' }}>Empresa absorve</option>
                    <option value="1" {{ (int) ($settings['gateway_pass_tax_to_client'] ?? 0) === 1 ? 'selected' : '' }}>Cliente paga</option>
                </select>
            </div>
        </div>

        <div class="gw-card">
            <label class="gw-label">Expiração do PIX (minutos)</label>
            <input type="number" min="1" max="1440" name="mercadopago_pix_expiration_minutes"
                value="{{ $settings['mercadopago_pix_expiration_minutes'] ?? '10' }}"
                class="gw-input max-w-xs px-4 py-3 rounded-xl font-bold text-sm">
        </div>
    </div>

    {{-- MP > CHECKOUT --}}
    <div id="mp-checkout" class="mp-subpanel hidden space-y-4">
        <div class="gw-card">
            <label class="gw-label">Tema do Checkout</label>
            <select name="gateway_checkout_theme" class="gw-select w-full px-4 py-3 rounded-xl text-sm font-semibold">
                <option value="default" {{ ($settings['gateway_checkout_theme'] ?? 'default') === 'default' ? 'selected' : '' }}>Padrão</option>
                <option value="dark" {{ ($settings['gateway_checkout_theme'] ?? '') === 'dark' ? 'selected' : '' }}>Dark</option>
                <option value="bootstrap" {{ ($settings['gateway_checkout_theme'] ?? '') === 'bootstrap' ? 'selected' : '' }}>Bootstrap</option>
                <option value="flat" {{ ($settings['gateway_checkout_theme'] ?? '') === 'flat' ? 'selected' : '' }}>Flat</option>
            </select>
        </div>
        <div class="gw-card">
            <label class="gw-label">Cor Principal</label>
            <div class="flex items-center gap-3">
                <input type="color" name="gateway_checkout_primary_color" id="gw_color"
                    value="{{ $settings['gateway_checkout_primary_color'] ?? '#1F5EDB' }}"
                    class="h-11 w-14 rounded-xl border border-slate-200 cursor-pointer"
                    oninput="document.getElementById('gw_color_txt').value = this.value">
                <input type="text" id="gw_color_txt"
                    value="{{ $settings['gateway_checkout_primary_color'] ?? '#1F5EDB' }}" readonly
                    class="gw-input flex-1 px-4 py-3 rounded-xl text-sm font-mono">
            </div>
        </div>
    </div>

    {{-- MP > ADVANCED --}}
    <div id="mp-advanced" class="mp-subpanel hidden space-y-4">
        <div class="gw-card grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="gw-label">Integrator ID</label>
                <input type="text" name="mercadopago_integrator_id"
                    value="{{ $settings['mercadopago_integrator_id'] ?? '' }}" placeholder="dev_123"
                    class="gw-input w-full px-4 py-3 rounded-xl font-mono text-sm">
            </div>
            <div>
                <label class="gw-label">Platform ID</label>
                <input type="text" name="mercadopago_platform_id"
                    value="{{ $settings['mercadopago_platform_id'] ?? '' }}" placeholder="plat_123"
                    class="gw-input w-full px-4 py-3 rounded-xl font-mono text-sm">
            </div>
        </div>

        {{-- Permissões por tipo de usuário --}}
        <div>
            <h4 class="text-sm font-black text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                <i class="fas fa-users text-blue-500"></i> Permissões por Tipo de Usuário
            </h4>
            @php
                $mpUserPerms = [
                    ['name' => 'mercadopago_allow_members',     'label' => 'Membros',     'icon' => 'fa-user'],
                    ['name' => 'mercadopago_allow_instructors', 'label' => 'Instrutores', 'icon' => 'fa-chalkboard-teacher'],
                    ['name' => 'mercadopago_allow_sellers',     'label' => 'Vendedores',  'icon' => 'fa-store'],
                    ['name' => 'mercadopago_allow_mentors',     'label' => 'Mentores',    'icon' => 'fa-user-tie'],
                ];
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($mpUserPerms as $perm)
                    @php $checked = (int) ($settings[$perm['name']] ?? 1) === 1; @endphp
                    <label class="gw-card flex flex-col items-center gap-2 cursor-pointer transition-all hover:border-blue-400 {{ $checked ? '!border-blue-500' : '' }}">
                        <input type="hidden" name="{{ $perm['name'] }}" value="0">
                        <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600" name="{{ $perm['name'] }}" value="1"
                            {{ $checked ? 'checked' : '' }}>
                        <i class="fas {{ $perm['icon'] }} text-xl text-slate-500"></i>
                        <p class="text-xs font-black text-slate-700 dark:text-slate-200">{{ $perm['label'] }}</p>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Permissões por tipo de produto --}}
        <div>
            <h4 class="text-sm font-black text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                <i class="fas fa-box text-emerald-500"></i> Permissões por Tipo de Produto
            </h4>
            @php
                $mpProductPerms = [
                    ['name' => 'mercadopago_allow_courses',       'label' => 'Cursos',       'icon' => 'fa-graduation-cap'],
                    ['name' => 'mercadopago_allow_mentorships',   'label' => 'Mentorias',    'icon' => 'fa-user-tie'],
                    ['name' => 'mercadopago_allow_events',        'label' => 'Eventos',      'icon' => 'fa-calendar'],
                    ['name' => 'mercadopago_allow_marketplace',   'label' => 'Marketplace',  'icon' => 'fa-store'],
                    ['name' => 'mercadopago_allow_subscriptions', 'label' => 'Assinaturas',  'icon' => 'fa-crown'],
                    ['name' => 'mercadopago_allow_services',      'label' => 'Serviços',     'icon' => 'fa-concierge-bell'],
                ];
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($mpProductPerms as $perm)
                    @php $checked = (int) ($settings[$perm['name']] ?? 1) === 1; @endphp
                    <label class="gw-card flex flex-col items-center gap-2 cursor-pointer transition-all hover:border-blue-400 {{ $checked ? '!border-blue-500' : '' }}">
                        <input type="hidden" name="{{ $perm['name'] }}" value="0">
                        <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600" name="{{ $perm['name'] }}" value="1"
                            {{ $checked ? 'checked' : '' }}>
                        <i class="fas {{ $perm['icon'] }} text-xl text-slate-500"></i>
                        <p class="text-xs font-black text-slate-700 dark:text-slate-200">{{ $perm['label'] }}</p>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     TAB SUMUP
     ============================================================ --}}
<div id="sumup-tab" class="main-gateway-panel hidden">
    {{-- Header SumUp --}}
    <div class="relative overflow-hidden rounded-2xl mb-6"
         style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);">
        <div class="relative flex items-start justify-between p-6 gap-4 flex-wrap">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center border border-white/20">
                    <i class="fas fa-credit-card text-white text-2xl"></i>
                </div>
                <div>
                    <p class="text-blue-200 text-xs font-bold uppercase tracking-widest mb-0.5">Gateway</p>
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
                    <div class="w-11 h-6 bg-white/20 rounded-full peer border border-white/30
                                peer-checked:after:translate-x-full peer-checked:after:border-white
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white
                                after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5
                                after:transition-all peer-checked:bg-emerald-500 relative"></div>
                </label>
            </div>
        </div>
    </div>

    {{-- Sub-tabs SumUp --}}
    <div class="flex gap-1 bg-slate-100 dark:bg-slate-900 p-1 rounded-2xl mb-6 overflow-x-auto">
        @php
            $sumupTabs = [
                ['id' => 'sumup-auth',        'icon' => 'fa-key',          'label' => 'Autenticação'],
                ['id' => 'sumup-methods',     'icon' => 'fa-credit-card',  'label' => 'Métodos'],
                ['id' => 'sumup-billing',     'icon' => 'fa-sliders-h',    'label' => 'Cobrança'],
                ['id' => 'sumup-permissions', 'icon' => 'fa-shield-alt',   'label' => 'Permissões'],
            ];
        @endphp
        @foreach($sumupTabs as $i => $tab)
            <button type="button" onclick="gwSwitchSub('sumup', '{{ $tab['id'] }}')"
                id="sumup-btn-{{ $tab['id'] }}"
                class="sumup-subtab-btn flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-xl text-xs font-bold transition-all whitespace-nowrap
                       {{ $i === 0 ? 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 shadow-sm' : 'text-slate-500' }}">
                <i class="fas {{ $tab['icon'] }}"></i>
                <span class="hidden sm:inline">{{ $tab['label'] }}</span>
            </button>
        @endforeach
    </div>

    {{-- SUMUP > AUTH --}}
    <div id="sumup-auth" class="sumup-subpanel space-y-5">
        <div class="gw-card" style="border-color: #3b82f6;">
            <div class="flex items-start gap-2 text-xs text-slate-700 dark:text-slate-300">
                <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                <div>
                    <div class="font-black mb-1">Como obter credenciais</div>
                    <ol class="list-decimal ml-5 space-y-0.5 text-[11px]">
                        <li>API Key: <a href="https://me.sumup.com" target="_blank" class="text-blue-500 underline">me.sumup.com</a> &gt; Settings &gt; For Developers &gt; API Keys</li>
                        <li>Merchant Code: código da sua conta lojista (ex: M7MMJMM7)</li>
                        <li>OAuth: opcional (Developers &gt; OAuth Apps)</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="gw-card">
            <label class="gw-label">Ambiente SumUp</label>
            <select name="sumup_env" class="gw-select w-full px-4 py-3 rounded-xl text-sm font-semibold">
                <option value="sandbox" {{ ($settings['sumup_env'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testes)</option>
                <option value="production" {{ ($settings['sumup_env'] ?? '') === 'production' ? 'selected' : '' }}>Produção (Real)</option>
            </select>
        </div>

        <div class="gw-card">
            <div class="flex items-center gap-2 mb-3">
                <i class="fas fa-key text-slate-500 text-xs"></i>
                <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">Credenciais SumUp</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="gw-label">API Key <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" name="sumup_api_key" id="sumup_api_key_field"
                            value="{{ $settings['sumup_api_key'] ?? '' }}"
                            placeholder="sup_sk_xxxxxxxx"
                            class="gw-input w-full px-4 py-3 pr-11 rounded-xl font-mono text-sm">
                        <button type="button" onclick="gwReveal('sumup_api_key_field', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="gw-label">Merchant Code <span class="text-red-500">*</span></label>
                    <input type="text" name="sumup_merchant_code"
                        value="{{ $settings['sumup_merchant_code'] ?? '' }}"
                        placeholder="MXXXXXXX"
                        class="gw-input w-full px-4 py-3 rounded-xl font-mono text-sm">
                </div>
                <div>
                    <label class="gw-label">Client ID (OAuth)</label>
                    <input type="text" name="sumup_client_id"
                        value="{{ $settings['sumup_client_id'] ?? '' }}"
                        placeholder="opcional"
                        class="gw-input w-full px-4 py-3 rounded-xl font-mono text-sm">
                </div>
                <div>
                    <label class="gw-label">Client Secret (OAuth)</label>
                    <div class="relative">
                        <input type="password" name="sumup_client_secret" id="sumup_client_secret_field"
                            value="{{ $settings['sumup_client_secret'] ?? '' }}"
                            placeholder="opcional"
                            class="gw-input w-full px-4 py-3 pr-11 rounded-xl font-mono text-sm">
                        <button type="button" onclick="gwReveal('sumup_client_secret_field', this)"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="gw-card">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                    <i class="fas fa-link text-indigo-500 text-xs"></i>
                </div>
                <div>
                    <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">Webhook SumUp</span>
                    <p class="text-[10px] text-slate-400">Cole no painel SumUp &gt; Developers &gt; Webhooks</p>
                </div>
            </div>
            <div class="flex rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 mb-3">
                <input type="text" readonly value="{{ route('api.webhooks.sumup') }}"
                    class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-mono border-0">
                <button type="button" onclick="gwCopy('{{ route('api.webhooks.sumup') }}')"
                    class="px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold">
                    <i class="fas fa-copy"></i> Copiar
                </button>
            </div>
            <label class="gw-label">Webhook Secret (opcional)</label>
            <div class="relative">
                <input type="password" name="sumup_webhook_secret" id="sumup_webhook_secret_field"
                    value="{{ $settings['sumup_webhook_secret'] ?? '' }}"
                    placeholder="Deixe vazio se não usar HMAC"
                    class="gw-input w-full px-4 py-3 pr-11 rounded-xl font-mono text-sm">
                <button type="button" onclick="gwReveal('sumup_webhook_secret_field', this)"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400">
                    <i class="fas fa-eye text-sm"></i>
                </button>
            </div>
        </div>

        <button type="button" onclick="gwTestSumUp()" id="btn-test-sumup"
            class="w-full flex items-center justify-center gap-2 py-3 px-6 rounded-xl font-bold text-sm transition-all
                   bg-slate-800 hover:bg-slate-900 text-white shadow-lg">
            <i class="fas fa-plug"></i> Testar Conexão SumUp
        </button>
    </div>

    {{-- SUMUP > METHODS --}}
    <div id="sumup-methods" class="sumup-subpanel hidden">
        <p class="text-xs text-slate-400 mb-4 font-medium">Ative ou desative os métodos SumUp.</p>
        @php
            $sumupMethods = [
                ['name' => 'sumup_method_card', 'label' => 'Cartão de Crédito', 'desc' => 'Via SumUp.js', 'icon' => 'fa-credit-card', 'default' => 1],
                ['name' => 'sumup_method_pix',  'label' => 'PIX',               'desc' => 'QR Code',     'icon' => 'fa-pix',         'default' => 1, 'brand' => true],
            ];
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($sumupMethods as $m)
                @php $checked = (int) ($settings[$m['name']] ?? $m['default']) === 1; @endphp
                <label class="gw-card flex items-center gap-4 cursor-pointer transition-all hover:border-blue-400 {{ $checked ? '!border-blue-500' : '' }}">
                    <input type="hidden" name="{{ $m['name'] }}" value="0">
                    <input type="checkbox" class="h-5 w-5 rounded border-slate-300 text-blue-600" name="{{ $m['name'] }}" value="1"
                        {{ $checked ? 'checked' : '' }}>
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-slate-100 dark:bg-slate-800">
                        <i class="{{ !empty($m['brand']) ? 'fa-brands' : 'fas' }} {{ $m['icon'] }} text-xl text-slate-500"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-black text-sm text-slate-700 dark:text-slate-200">{{ $m['label'] }}</p>
                        <p class="text-xs text-slate-500">{{ $m['desc'] }}</p>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    {{-- SUMUP > BILLING --}}
    <div id="sumup-billing" class="sumup-subpanel hidden space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="gw-card">
                <label class="gw-label">Taxa Percentual (%)</label>
                <input type="number" step="0.01" name="sumup_fee_percentage"
                    value="{{ $settings['sumup_fee_percentage'] ?? '2.75' }}"
                    class="gw-input w-full px-4 py-3 rounded-xl font-bold text-sm">
            </div>
            <div class="gw-card">
                <label class="gw-label">Taxa Fixa (R$)</label>
                <input type="number" step="0.01" name="sumup_fee_fixed"
                    value="{{ $settings['sumup_fee_fixed'] ?? '0.00' }}"
                    class="gw-input w-full px-4 py-3 rounded-xl font-bold text-sm">
            </div>
            <div class="gw-card">
                <label class="gw-label">Máx. Parcelas</label>
                <input type="number" min="1" max="12" name="sumup_max_installments"
                    value="{{ $settings['sumup_max_installments'] ?? '12' }}"
                    class="gw-input w-full px-4 py-3 rounded-xl font-bold text-sm">
            </div>
            <div class="gw-card">
                <label class="gw-label">Repassar Taxa</label>
                <select name="sumup_pass_fee" class="gw-select w-full px-4 py-3 rounded-xl text-sm font-semibold">
                    <option value="0" {{ (int) ($settings['sumup_pass_fee'] ?? 0) === 0 ? 'selected' : '' }}>Empresa absorve</option>
                    <option value="1" {{ (int) ($settings['sumup_pass_fee'] ?? 0) === 1 ? 'selected' : '' }}>Cliente paga</option>
                </select>
            </div>
        </div>

        <div class="gw-card grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="gw-label">Parcelas sem Juros</label>
                <input type="number" min="1" max="12" name="sumup_installments_no_interest"
                    value="{{ $settings['sumup_installments_no_interest'] ?? '1' }}"
                    class="gw-input w-full px-4 py-3 rounded-xl font-bold text-sm">
            </div>
            <div>
                <label class="gw-label">Juros por Parcela (%)</label>
                <input type="number" step="0.01" name="sumup_installment_tax"
                    value="{{ $settings['sumup_installment_tax'] ?? '0.00' }}"
                    class="gw-input w-full px-4 py-3 rounded-xl font-bold text-sm">
            </div>
            <div>
                <label class="gw-label">Tipo de Cálculo</label>
                <select name="sumup_interest_type" class="gw-select w-full px-4 py-3 rounded-xl text-sm font-semibold">
                    <option value="per_installment" {{ ($settings['sumup_interest_type'] ?? 'per_installment') === 'per_installment' ? 'selected' : '' }}>Por parcela</option>
                    <option value="on_total" {{ ($settings['sumup_interest_type'] ?? '') === 'on_total' ? 'selected' : '' }}>Sobre o total</option>
                </select>
            </div>
        </div>

        <div class="gw-card">
            <label class="gw-label">Expiração do PIX (minutos)</label>
            <input type="number" min="1" max="1440" name="sumup_pix_expiration_minutes"
                value="{{ $settings['sumup_pix_expiration_minutes'] ?? '10' }}"
                class="gw-input max-w-xs px-4 py-3 rounded-xl font-bold text-sm">
        </div>
    </div>

    {{-- SUMUP > PERMISSIONS --}}
    <div id="sumup-permissions" class="sumup-subpanel hidden space-y-5">
        <div>
            <h4 class="text-sm font-black text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                <i class="fas fa-users text-blue-500"></i> Por Tipo de Usuário
            </h4>
            @php
                $userPerms = [
                    ['name' => 'sumup_allow_members',     'label' => 'Membros',     'icon' => 'fa-user'],
                    ['name' => 'sumup_allow_instructors', 'label' => 'Instrutores', 'icon' => 'fa-chalkboard-teacher'],
                    ['name' => 'sumup_allow_sellers',     'label' => 'Vendedores',  'icon' => 'fa-store'],
                    ['name' => 'sumup_allow_mentors',     'label' => 'Mentores',    'icon' => 'fa-user-tie'],
                ];
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($userPerms as $perm)
                    @php $checked = (int) ($settings[$perm['name']] ?? 1) === 1; @endphp
                    <label class="gw-card flex flex-col items-center gap-2 cursor-pointer transition-all hover:border-blue-400 {{ $checked ? '!border-blue-500' : '' }}">
                        <input type="hidden" name="{{ $perm['name'] }}" value="0">
                        <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600" name="{{ $perm['name'] }}" value="1"
                            {{ $checked ? 'checked' : '' }}>
                        <i class="fas {{ $perm['icon'] }} text-xl text-slate-500"></i>
                        <p class="text-xs font-black text-slate-700 dark:text-slate-200">{{ $perm['label'] }}</p>
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
                    ['name' => 'sumup_allow_courses',       'label' => 'Cursos',       'icon' => 'fa-graduation-cap'],
                    ['name' => 'sumup_allow_mentorships',   'label' => 'Mentorias',    'icon' => 'fa-user-tie'],
                    ['name' => 'sumup_allow_events',        'label' => 'Eventos',      'icon' => 'fa-calendar'],
                    ['name' => 'sumup_allow_marketplace',   'label' => 'Marketplace',  'icon' => 'fa-store'],
                    ['name' => 'sumup_allow_subscriptions', 'label' => 'Assinaturas',  'icon' => 'fa-crown'],
                    ['name' => 'sumup_allow_services',      'label' => 'Serviços',     'icon' => 'fa-concierge-bell'],
                ];
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($productPerms as $perm)
                    @php $checked = (int) ($settings[$perm['name']] ?? 1) === 1; @endphp
                    <label class="gw-card flex flex-col items-center gap-2 cursor-pointer transition-all hover:border-blue-400 {{ $checked ? '!border-blue-500' : '' }}">
                        <input type="hidden" name="{{ $perm['name'] }}" value="0">
                        <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600" name="{{ $perm['name'] }}" value="1"
                            {{ $checked ? 'checked' : '' }}>
                        <i class="fas {{ $perm['icon'] }} text-xl text-slate-500"></i>
                        <p class="text-xs font-black text-slate-700 dark:text-slate-200">{{ $perm['label'] }}</p>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="gw-card grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="gw-label">Valor Mínimo (R$)</label>
                <input type="number" step="0.01" name="sumup_minimum_amount"
                    value="{{ $settings['sumup_minimum_amount'] ?? '0.00' }}"
                    class="gw-input w-full px-4 py-3 rounded-xl font-bold text-sm">
                <p class="text-[10px] text-slate-400 mt-1">0 = sem limite</p>
            </div>
            <div>
                <label class="gw-label">Valor Máximo (R$)</label>
                <input type="number" step="0.01" name="sumup_maximum_amount"
                    value="{{ $settings['sumup_maximum_amount'] ?? '0.00' }}"
                    class="gw-input w-full px-4 py-3 rounded-xl font-bold text-sm">
                <p class="text-[10px] text-slate-400 mt-1">0 = sem limite</p>
            </div>
            <label class="flex items-start gap-3 cursor-pointer pt-6">
                <input type="hidden" name="sumup_fallback_to_mercadopago" value="0">
                <input type="checkbox" name="sumup_fallback_to_mercadopago" value="1"
                    class="h-5 w-5 rounded border-slate-300 text-blue-600"
                    {{ (int) ($settings['sumup_fallback_to_mercadopago'] ?? 1) === 1 ? 'checked' : '' }}>
                <div>
                    <p class="text-xs font-black text-slate-700 dark:text-slate-200">Fallback MP</p>
                    <p class="text-[10px] text-slate-400">Usa MercadoPago se SumUp falhar</p>
                </div>
            </label>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    // TABS PRINCIPAIS
    window.gwSwitchMain = function(tabId) {
        document.querySelectorAll('.main-gateway-panel').forEach(p => p.classList.add('hidden'));
        const panel = document.getElementById(tabId);
        if (panel) panel.classList.remove('hidden');

        document.querySelectorAll('.main-gateway-tab-btn').forEach(btn => {
            btn.classList.remove('bg-white', 'dark:bg-slate-800', 'text-blue-600', 'dark:text-blue-400', 'shadow-sm');
            btn.classList.add('text-slate-500', 'dark:text-slate-400');
        });
        const btn = document.getElementById('main-btn-' + tabId);
        if (btn) {
            btn.classList.remove('text-slate-500', 'dark:text-slate-400');
            btn.classList.add('bg-white', 'dark:bg-slate-800', 'text-blue-600', 'dark:text-blue-400', 'shadow-sm');
        }
    };

    // SUB-TABS (MP ou SumUp)
    window.gwSwitchSub = function(prefix, tabId) {
        document.querySelectorAll('.' + prefix + '-subpanel').forEach(p => p.classList.add('hidden'));
        const panel = document.getElementById(tabId);
        if (panel) panel.classList.remove('hidden');

        document.querySelectorAll('.' + prefix + '-subtab-btn').forEach(btn => {
            btn.classList.remove('bg-white', 'dark:bg-slate-800', 'text-blue-600', 'dark:text-blue-400', 'text-slate-700', 'dark:text-slate-300', 'shadow-sm');
            btn.classList.add('text-slate-500', 'dark:text-slate-400');
        });
        const btn = document.getElementById(prefix + '-btn-' + tabId);
        if (btn) {
            btn.classList.remove('text-slate-500', 'dark:text-slate-400');
            if (prefix === 'mp') {
                btn.classList.add('bg-white', 'dark:bg-slate-800', 'text-blue-600', 'dark:text-blue-400', 'shadow-sm');
            } else {
                btn.classList.add('bg-white', 'dark:bg-slate-800', 'text-slate-700', 'dark:text-slate-300', 'shadow-sm');
            }
        }
    };

    // ENV SWITCH
    window.gwSwitchEnv = function(env) {
        const isSandbox = env === 'sandbox';
        document.getElementById('env-sandbox-fields').classList.toggle('hidden', !isSandbox);
        document.getElementById('env-production-fields').classList.toggle('hidden', isSandbox);
        document.getElementById('mercadopago_env_select').value = env;

        const sb = document.getElementById('env-btn-sandbox');
        const pr = document.getElementById('env-btn-production');
        if (isSandbox) {
            sb.classList.add('bg-white', 'dark:bg-slate-800', 'text-amber-600', 'dark:text-amber-400', 'shadow-sm');
            sb.classList.remove('text-slate-500');
            pr.classList.remove('bg-white', 'dark:bg-slate-800', 'text-emerald-600', 'dark:text-emerald-400', 'shadow-sm');
            pr.classList.add('text-slate-500');
        } else {
            pr.classList.add('bg-white', 'dark:bg-slate-800', 'text-emerald-600', 'dark:text-emerald-400', 'shadow-sm');
            pr.classList.remove('text-slate-500');
            sb.classList.remove('bg-white', 'dark:bg-slate-800', 'text-amber-600', 'dark:text-amber-400', 'shadow-sm');
            sb.classList.add('text-slate-500');
        }
    };

    // REVEAL PASSWORD
    window.gwReveal = function(id, btn) {
        const input = document.getElementById(id);
        const icon = btn.querySelector('i');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    };

    // CLIPBOARD
    window.gwCopy = function(text) {
        navigator.clipboard.writeText(text).then(() => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Copiado!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
            }
        });
    };

    // TEST MP
    window.gwTestMP = function() {
        const btn = document.getElementById('btn-test-mp');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testando...';

        const env = document.getElementById('mercadopago_env_select').value;
        const tokenInput = env === 'sandbox'
            ? document.querySelector('input[name="mercadopago_sandbox_access_token"]')
            : document.querySelector('input[name="mercadopago_prod_access_token"]');
        const token = tokenInput ? tokenInput.value : '';

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
        .catch(() => alert('Erro na requisição'))
        .finally(() => { btn.disabled = false; btn.innerHTML = orig; });
    };

    // TEST SUMUP
    window.gwTestSumUp = function() {
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
        .catch(() => alert('Erro na requisição'))
        .finally(() => { btn.disabled = false; btn.innerHTML = orig; });
    };
})();
</script>
@endpush
