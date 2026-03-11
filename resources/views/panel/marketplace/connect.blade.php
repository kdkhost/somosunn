@extends('panel.layouts.app')

@section('title', 'Configurar Pagamentos - UNN')

@section('panel_content')
    @php
        $mpConfigured = $mercadopago->enabled && $mercadopago->access_token;
        $mpOauthConnected = $mpConfigured && $mercadopago->provider == 'mercadopago';
        $oauthPopupUrl = route('gateway.mercadopago.connect', [
            'popup' => 1,
            'return_route' => 'panel.marketplace.payments.edit',
        ]);
        $oauthFallbackUrl = route('gateway.mercadopago.connect', [
            'popup' => 0,
            'return_route' => 'panel.marketplace.payments.edit',
        ]);
    @endphp

    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white transition-colors">
                    Configuracao de Pagamento
                </h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1 transition-colors">
                    Configure seus gateways para receber pagamentos de suas vendas.
                </p>
            </div>
            <a href="{{ route('panel.marketplace.payments') }}"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-800 px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </div>

    <div class="max-w-2xl mt-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300 flex flex-col">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-blue-500/10 rounded-xl flex items-center justify-center p-2">
                    <img src="https://http2.mlstatic.com/frontend-assets/ui-navigation/5.18.9/mercadolibre/logo__small.png"
                        class="w-full h-auto" alt="MP">
                </div>
                <div class="min-w-0">
                    <h3 class="font-extrabold text-lg text-slate-900 dark:text-white">Mercado Pago</h3>
                    <div class="flex items-center gap-2 text-sm mt-0.5 flex-wrap">
                        <span id="mp-status-badge"
                            class="inline-flex items-center font-bold {{ $mpConfigured ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-500' }}">
                            <i id="mp-status-icon" class="fas {{ $mpConfigured ? 'fa-check-circle' : 'fa-circle text-[8px]' }} mr-1"></i>
                            <span id="mp-status-text">{{ $mpConfigured ? 'Ativo' : 'Inativo' }}</span>
                        </span>

                        <button type="button"
                            id="mp-test-connection-btn"
                            data-test-url="{{ route('panel.marketplace.payments.test') }}"
                            class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2.5 py-1 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors font-bold {{ $mpConfigured ? '' : 'hidden' }}">
                            <i class="fas fa-plug mr-1"></i>
                            <span id="mp-test-connection-label">Testar Conexao</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex-1 space-y-6">
                <div class="bg-blue-50 dark:bg-blue-900/10 rounded-2xl p-5 border border-blue-100 dark:border-blue-800/50">
                    <h4 class="font-bold text-blue-900 dark:text-blue-300 mb-1 text-sm">
                        Conexao via OAuth
                        <span class="ml-1 text-xs font-normal text-blue-500">(opcional)</span>
                    </h4>
                    <p class="text-xs text-blue-700 dark:text-blue-400 mb-1 leading-relaxed">
                        Autorize o app da plataforma a processar pagamentos em seu nome com <strong>split automatico</strong>.
                        Necessario apenas se quiser que sua taxa de plataforma seja descontada automaticamente pelo Mercado Pago.
                    </p>
                    <p class="text-xs text-blue-500 dark:text-blue-500 mb-4">
                        Se voce ja configurou seu token manualmente abaixo, os pagamentos ja funcionam sem OAuth.
                    </p>

                    <button type="button"
                        id="mp-oauth-connect-btn"
                        data-oauth-url="{{ $oauthPopupUrl }}"
                        data-oauth-fallback-url="{{ $oauthFallbackUrl }}"
                        class="w-full inline-flex items-center justify-center px-4 py-3 rounded-xl text-sm font-bold transition-all {{ $mpOauthConnected ? 'bg-white dark:bg-slate-800 border border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 shadow-sm hover:bg-blue-50 dark:hover:bg-slate-700' : 'bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-500/20 hover:scale-[1.02]' }}">
                        <i id="mp-oauth-connect-icon" class="fas {{ $mpOauthConnected ? 'fa-sync-alt' : 'fa-link' }} mr-2"></i>
                        <span id="mp-oauth-connect-label">{{ $mpOauthConnected ? 'Atualizar Conexao OAuth' : 'Conectar via OAuth' }}</span>
                    </button>

                    <p id="mp-oauth-feedback" class="mt-3 hidden text-[11px] font-medium text-blue-600 dark:text-blue-300"></p>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <h4 class="font-bold text-slate-900 dark:text-white mb-4 text-sm">
                        <i class="fas fa-cog mr-2 text-slate-400"></i>Configuracoes de Venda
                    </h4>

                    <form action="{{ route('settings.payment.update') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="provider" value="mercadopago">

                        <div class="space-y-3">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Meios de Pagamento Aceitos</label>
                            <div class="grid grid-cols-2 gap-3">
                                @php
                                    $enabledMethods = data_get($mercadopago->extra, 'enabled_methods', ['credit_card', 'debit_card', 'pix']);
                                @endphp
                                <label class="flex items-center gap-3 p-3 rounded-2xl border border-slate-100 dark:border-slate-800 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all">
                                    <input type="checkbox" name="methods[]" value="credit_card" {{ in_array('credit_card', $enabledMethods) ? 'checked' : '' }}
                                        class="rounded text-blue-600 focus:ring-blue-500/20 border-slate-300">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Cartao de Credito</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 rounded-2xl border border-slate-100 dark:border-slate-800 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all">
                                    <input type="checkbox" name="methods[]" value="debit_card" {{ in_array('debit_card', $enabledMethods) ? 'checked' : '' }}
                                        class="rounded text-blue-600 focus:ring-blue-500/20 border-slate-300">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Cartao de Debito</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 rounded-2xl border border-slate-100 dark:border-slate-800 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all">
                                    <input type="checkbox" name="methods[]" value="pix" {{ in_array('pix', $enabledMethods) ? 'checked' : '' }}
                                        class="rounded text-blue-600 focus:ring-blue-500/20 border-slate-300">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">PIX</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Maximo de Parcelas</label>
                            <select name="max_installments"
                                class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-sm px-4 py-2.5">
                                @php $maxInst = (int) data_get($mercadopago->extra, 'max_installments', 12); @endphp
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $maxInst == $i ? 'selected' : '' }}>{{ $i }}x</option>
                                @endfor
                            </select>
                        </div>

                        <div class="p-4 bg-amber-50 dark:bg-amber-900/10 rounded-2xl border border-amber-100 dark:border-amber-800/50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-bold text-amber-900 dark:text-amber-300">Repassar taxas ao cliente?</div>
                                    <p class="text-[10px] text-amber-700 dark:text-amber-400 mt-0.5">O valor da venda sera acrescido da taxa do MP.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="pass_fee" value="0">
                                    <input type="checkbox" name="pass_fee" value="1" {{ data_get($mercadopago->extra, 'pass_fee') ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full py-3.5 rounded-2xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-extrabold text-sm hover:opacity-90 transition-all shadow-xl shadow-slate-900/10 dark:shadow-none">
                            <i class="fas fa-save mr-2"></i> Salvar Configuracoes
                        </button>
                    </form>
                </div>

                <div x-data="{ open: false }" class="mt-4">
                    <button @click="open = !open" type="button"
                        class="flex items-center text-xs font-bold text-slate-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors w-full justify-between group">
                        <span>Chaves API (Manual)</span>
                        <i class="fas fa-chevron-down transition-transform duration-300" :class="{'rotate-180': open}"></i>
                    </button>

                    <div x-show="open" x-collapse style="display: none;" class="mt-4">
                        <form action="{{ route('settings.payment.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="provider" value="mercadopago">

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Public Key</label>
                                    <input type="text" name="public_key" value="{{ $mercadopago->public_key }}"
                                        class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-sm px-4 py-2.5 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                        placeholder="APP_USR-...">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Access Token</label>
                                    <div class="relative">
                                        <input type="password" name="access_token" value="{{ $mercadopago->access_token }}"
                                            class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-sm px-4 py-2.5 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                            placeholder="APP_USR-...">
                                    </div>
                                </div>
                                <button type="submit"
                                    class="w-full py-2.5 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-900 dark:text-white font-bold text-xs hover:opacity-90 transition-all">
                                    Atualizar Chaves
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="//unpkg.com/alpinejs" defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const csrfToken = @json(csrf_token());
                const testButton = document.getElementById('mp-test-connection-btn');
                const testLabel = document.getElementById('mp-test-connection-label');
                const oauthButton = document.getElementById('mp-oauth-connect-btn');
                const oauthLabel = document.getElementById('mp-oauth-connect-label');
                const oauthIcon = document.getElementById('mp-oauth-connect-icon');
                const oauthFeedback = document.getElementById('mp-oauth-feedback');
                const statusBadge = document.getElementById('mp-status-badge');
                const statusIcon = document.getElementById('mp-status-icon');
                const statusText = document.getElementById('mp-status-text');

                function showToast(icon, title, text) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: icon,
                            title: title,
                            text: text,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3500,
                            timerProgressBar: true,
                        });
                        return;
                    }

                    alert(title + '\n\n' + text);
                }

                function markGatewayConnected(message) {
                    statusBadge.className = 'inline-flex items-center font-bold text-emerald-600 dark:text-emerald-400';
                    statusIcon.className = 'fas fa-check-circle mr-1';
                    statusText.textContent = 'Ativo';

                    if (testButton) {
                        testButton.classList.remove('hidden');
                    }

                    oauthButton.className = 'w-full inline-flex items-center justify-center px-4 py-3 rounded-xl text-sm font-bold transition-all bg-white dark:bg-slate-800 border border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 shadow-sm hover:bg-blue-50 dark:hover:bg-slate-700';
                    oauthIcon.className = 'fas fa-sync-alt mr-2';
                    oauthLabel.textContent = 'Atualizar Conexao OAuth';

                    if (oauthFeedback) {
                        oauthFeedback.textContent = message;
                        oauthFeedback.classList.remove('hidden');
                    }
                }

                if (testButton) {
                    testButton.addEventListener('click', function () {
                        const original = testButton.innerHTML;
                        testButton.disabled = true;
                        testButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Testando...';

                        fetch(testButton.dataset.testUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ _token: csrfToken }),
                        })
                            .then(async (response) => {
                                const data = await response.json().catch(() => ({}));
                                if (!response.ok || !data.success) {
                                    throw new Error(data.message || 'Nao foi possivel validar as credenciais.');
                                }

                                showToast('success', 'Conexao validada', data.message);
                                testButton.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Conexao OK';
                                window.setTimeout(function () {
                                    testButton.innerHTML = original;
                                    testButton.disabled = false;
                                }, 1800);
                            })
                            .catch((error) => {
                                showToast('error', 'Falha no teste', error.message);
                                testButton.innerHTML = original;
                                testButton.disabled = false;
                            });
                    });
                }

                if (oauthButton) {
                    oauthButton.addEventListener('click', function () {
                        const popup = window.open(
                            oauthButton.dataset.oauthUrl,
                            'mercadopago-oauth',
                            'width=720,height=820,menubar=no,toolbar=no,status=no,resizable=yes,scrollbars=yes'
                        );

                        if (!popup) {
                            window.location.assign(oauthButton.dataset.oauthFallbackUrl);
                            return;
                        }

                        popup.focus();
                        oauthButton.disabled = true;
                        oauthLabel.textContent = 'Aguardando autorizacao...';
                        oauthIcon.className = 'fas fa-spinner fa-spin mr-2';

                        const popupWatcher = window.setInterval(function () {
                            if (!popup || popup.closed) {
                                window.clearInterval(popupWatcher);
                                oauthButton.disabled = false;
                                if (oauthLabel.textContent === 'Aguardando autorizacao...') {
                                    oauthLabel.textContent = @json($mpOauthConnected ? 'Atualizar Conexao OAuth' : 'Conectar via OAuth');
                                    oauthIcon.className = 'fas {{ $mpOauthConnected ? 'fa-sync-alt' : 'fa-link' }} mr-2';
                                }
                            }
                        }, 600);
                    });
                }

                window.addEventListener('message', function (event) {
                    if (event.origin !== window.location.origin) {
                        return;
                    }

                    if (!event.data || event.data.type !== 'mercadopago-oauth-result') {
                        return;
                    }

                    if (oauthButton) {
                        oauthButton.disabled = false;
                    }

                    if (event.data.success) {
                        markGatewayConnected(event.data.message || 'Conexao OAuth atualizada com sucesso.');
                        showToast('success', 'OAuth concluido', event.data.message || 'Conta conectada com sucesso.');
                    } else {
                        oauthLabel.textContent = @json($mpOauthConnected ? 'Atualizar Conexao OAuth' : 'Conectar via OAuth');
                        oauthIcon.className = 'fas {{ $mpOauthConnected ? 'fa-sync-alt' : 'fa-link' }} mr-2';
                        if (oauthFeedback) {
                            oauthFeedback.textContent = event.data.message || 'Nao foi possivel concluir a conexao OAuth.';
                            oauthFeedback.classList.remove('hidden');
                        }
                        showToast('error', 'Falha no OAuth', event.data.message || 'Nao foi possivel concluir a conexao.');
                    }
                });
            });
        </script>
    @endpush
@endsection
