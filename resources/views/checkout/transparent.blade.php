@extends('layouts.app')

@section('title', 'Checkout Seguro - ' . config('app.name'))

@section('content')
    @php
        // Suporte a activeGateways (array) — compatibilidade retroativa com $gateway (string)
        $activeGateways = $activeGateways ?? [];
        $gatewayCount = count($activeGateways);

        // Quando a view recebe apenas $gateway (string), manter comportamento legado
        if ($gatewayCount === 0 && !empty($gateway)) {
            $activeGateways = [['provider' => $gateway]];
            $gatewayCount = 1;
        }

        $singleGateway = $gatewayCount === 1 ? ($activeGateways[0]['provider'] ?? ($gateway ?? 'mercadopago')) : null;
        $showSelector = $gatewayCount > 1;

        // Resolver métodos disponíveis para cada gateway (usado no seletor)
        $gatewayMeta = [];
        if ($showSelector) {
            foreach ($activeGateways as $gw) {
                $provider = $gw['provider'];
                $methods = [];
                if ($provider === 'mercadopago') {
                    if ((int) \App\Models\Setting::get('mercadopago_method_credit_card', 1)) $methods[] = 'Cartão de Crédito';
                    if ((int) \App\Models\Setting::get('mercadopago_method_pix', 1)) $methods[] = 'PIX';
                    if ((int) \App\Models\Setting::get('mercadopago_method_debit_card', 0)) $methods[] = 'Débito';
                    if ((int) \App\Models\Setting::get('mercadopago_method_ticket', 0)) $methods[] = 'Boleto';
                    $gatewayMeta[$provider] = [
                        'name' => 'Mercado Pago',
                        'icon' => 'fas fa-handshake',
                        'methods' => $methods,
                    ];
                } elseif ($provider === 'sumup') {
                    $methodCardRaw = \App\Models\Setting::get('sumup_method_card');
                    $methodPixRaw = \App\Models\Setting::get('sumup_method_pix');
                    if ($methodCardRaw !== null ? (bool)(int)$methodCardRaw : true) $methods[] = 'Cartão de Crédito';
                    if ($methodPixRaw !== null ? (bool)(int)$methodPixRaw : true) $methods[] = 'PIX';
                    $gatewayMeta[$provider] = [
                        'name' => 'SumUp',
                        'icon' => 'fas fa-credit-card',
                        'methods' => $methods,
                    ];
                }
            }
        }
    @endphp

    <div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto">
            <!-- Header do Checkout -->
            <div class="text-center mb-10">
                <h1 class="text-3xl font-extrabold text-slate-900 sm:text-4xl">
                    Finalizar seu Pedido
                </h1>
                <p class="mt-3 max-w-2xl mx-auto text-xl text-slate-500 sm:mt-4">
                    @if($showSelector)
                        Escolha o método de pagamento de sua preferência.
                    @elseif($singleGateway === 'sumup')
                        Pagamento seguro e processamento instantâneo via SumUp.
                    @else
                        Pagamento seguro e processamento instantâneo via Mercado Pago.
                    @endif
                </p>
            </div>

            <div class="lg:grid lg:grid-cols-12 lg:gap-x-12 lg:items-start">
                <!-- Esquerda: Formulário de Pagamento -->
                <div class="lg:col-span-7">
                    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-slate-100">
                        <div class="p-1 sm:p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-600 uppercase tracking-wider ml-4">Informações de
                                Pagamento</span>
                            <div class="flex space-x-2 mr-4">
                                <i class="fab fa-cc-visa text-slate-400 text-xl"></i>
                                <i class="fab fa-cc-mastercard text-slate-400 text-xl"></i>
                                <i class="fa-brands fa-pix text-slate-400 text-xl"></i>
                            </div>
                        </div>
                        <div class="p-6 sm:p-8">

                            {{-- ═══════════════════════════════════════════════════════════════
                                 GATEWAY SELECTOR — exibido apenas quando há 2+ gateways ativos
                                 ═══════════════════════════════════════════════════════════════ --}}
                            @if($showSelector)
                            <div id="gateway-selector" class="mb-8">
                                <p class="mb-4 text-xs font-black uppercase tracking-widest text-slate-500">Escolha como pagar</p>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    @foreach($gatewayMeta as $provider => $meta)
                                    <button type="button"
                                        id="btn-gateway-{{ $provider }}"
                                        onclick="selectGateway('{{ $provider }}')"
                                        class="gateway-selector-card group relative flex items-start gap-4 rounded-2xl border-2 border-slate-200 bg-white p-5 text-left transition-all duration-200 hover:border-[#1F5EDB] hover:shadow-lg hover:shadow-blue-100/50 focus:outline-none focus:ring-4 focus:ring-blue-200"
                                        aria-label="Pagar com {{ $meta['name'] }}">
                                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-slate-50 border border-slate-100 group-hover:bg-blue-50 transition-colors">
                                            <i class="{{ $meta['icon'] }} text-xl text-slate-600 group-hover:text-[#1F5EDB]"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-slate-800 text-base">{{ $meta['name'] }}</p>
                                            <p class="text-xs text-slate-500 mt-1">{{ implode(' · ', $meta['methods']) }}</p>
                                        </div>
                                        <div class="absolute top-5 right-5 text-slate-300 group-hover:text-[#1F5EDB] transition-colors">
                                            <i class="fas fa-chevron-right text-sm"></i>
                                        </div>
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Input hidden com o gateway selecionado --}}
                            <input type="hidden" name="selected_gateway" id="selected_gateway" value="">

                            {{-- Botão Trocar gateway (oculto inicialmente) --}}
                            <div id="btn-trocar-gateway" class="hidden mb-6">
                                <button type="button" onclick="showGatewaySelector()"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-[#1F5EDB] hover:bg-slate-50 hover:border-[#1F5EDB] transition-all focus:outline-none focus:ring-2 focus:ring-blue-200">
                                    <i class="fas fa-exchange-alt"></i>
                                    Trocar gateway
                                </button>
                            </div>

                            {{-- Formulário Mercado Pago (oculto) --}}
                            <div id="form-mercadopago" class="hidden">
                                <div id="paymentBrick_container"></div>
                            </div>

                            {{-- Formulário SumUp (oculto) --}}
                            <div id="form-sumup" class="hidden">
                                @include('partials.checkout.sumup-card-form')
                            </div>

                            @else
                            {{-- ═══════════════════════════════════════════════════════════════
                                 SINGLE GATEWAY — comportamento atual mantido
                                 ═══════════════════════════════════════════════════════════════ --}}
                            @if($singleGateway === 'sumup')
                                @include('partials.checkout.sumup-card-form')
                            @else
                                <div id="paymentBrick_container"></div>
                            @endif
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-center space-x-4 text-slate-400 text-sm">
                        <div class="flex items-center">
                            <i class="fas fa-lock mr-2 text-green-500"></i>
                            <span>Ambiente Criptografado</span>
                        </div>
                        <span>&bull;</span>
                        <div class="flex items-center">
                            <i class="fas fa-shield-alt mr-2 text-green-500"></i>
                            <span>Compra Garantida</span>
                        </div>
                    </div>
                </div>

                <!-- Direita: Resumo do Pedido -->
                <div class="mt-10 lg:mt-0 lg:col-span-5">
                    <div class="bg-slate-900 rounded-2xl shadow-2xl p-8 text-white sticky top-24">
                        <h2 class="text-xl font-bold mb-6 flex items-center">
                            <i class="fas fa-shopping-bag mr-3 text-unn-azul-2"></i>
                            Resumo da Compra
                        </h2>

                        <div class="space-y-6">
                            @foreach($order->items as $item)
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <p class="font-medium text-slate-100">{{ $item->title }}</p>
                                        <p class="text-sm text-slate-400">Quantidade: {{ $item->quantity }}</p>
                                    </div>
                                    <span class="font-semibold" id="checkout-item-price">R$ {{ number_format($item->price, 2, ',', '.') }}</span>
                                </div>
                            @endforeach

                            <div class="border-t border-slate-700 pt-6 space-y-4">
                                @if(data_get($order->metadata, 'coupon.code'))
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-400">Cupom
                                            ({{ data_get($order->metadata, 'coupon.code') }})</span>
                                        <span class="text-green-400">- R$
                                            {{ number_format((float) data_get($order->metadata, 'coupon.discount_amount'), 2, ',', '.') }}</span>
                                    </div>
                                @endif

                                @php
                                    $sumupBaseAmount = (float) data_get($order->metadata, 'sumup_base_amount', 0);
                                    $sumupFeeAmount  = (float) data_get($order->metadata, 'sumup_fee_amount', 0);
                                    $sumupPassFeeMeta = (bool) data_get($order->metadata, 'sumup_pass_fee', false);
                                    $showSumupFeeLine = $sumupPassFeeMeta && $sumupBaseAmount > 0 && $sumupFeeAmount > 0.009;
                                @endphp

                                @if($showSumupFeeLine)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-400">Subtotal</span>
                                        <span class="text-slate-200">R$ {{ number_format($sumupBaseAmount, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-400">Taxa de processamento</span>
                                        <span class="text-slate-200">+ R$ {{ number_format($sumupFeeAmount, 2, ',', '.') }}</span>
                                    </div>
                                @endif

                                <div class="flex justify-between text-2xl font-extrabold pt-2">
                                    <span>Total</span>
                                    <span class="text-white" id="checkout-total" data-checkout-total>R$
                                        {{ number_format($order->total_amount, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 bg-slate-800 rounded-xl p-4 flex items-start space-x-3">
                            <i class="fas fa-info-circle text-unn-azul-2 mt-1"></i>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                O acesso ao conteúdo será liberado imediatamente após a confirmação do pagamento.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Garantir container limpo */
        #paymentBrick_container {
            min-height: 500px;
        }

        /* 
           CORREÇÃO URGENTE: Placeholders deslocados em campos seguros (Cartão, Validade, CVV).
           Esses campos usam iframes. Se houver line-height global alto ou padding herdado de frameworks,
           o texto placeholder "cai" para fora do input.
        */
        /* CRÍTICO: Correção de layout para iframes do Mercado Pago (Secure Fields) */
        #paymentBrick_container iframe {
            display: block !important;
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            min-height: 0 !important; /* Remove min-height herança */
        }
        
        /* Força o container do input a ser Flexbox para centralizar o iframe verticalmente */
        #paymentBrick_container [data-testid="input-container"] {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            height: 48px !important; /* Mesma altura configurada no JS v2.visual.style.customVariables.formInputsHeight */
            min-height: 48px !important;
            max-height: 48px !important;
            padding: 0 !important;
            overflow: hidden !important;
        }

        /* Garante que divs intermediárias ocupem todo o espaço */
        #paymentBrick_container [data-testid="input-container"] > div {
            width: 100% !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
        }

        /* Feedback de carregamento */
        .loading-overlay {
            display: none;
            position: fixed;
            top:0; left:0; width:100%; height:100%;
            background: rgba(255,255,255,0.8);
            z-index: 9999;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Gateway Selector — responsivo min 320px */
        .gateway-selector-card {
            min-width: 0;
        }
        @media (max-width: 639px) {
            .gateway-selector-card {
                padding: 1rem;
            }
        }
    </style>

    <div class="loading-overlay" id="checkout-loading">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-unn-azul-2 mb-4"></div>
        <p class="text-slate-900 font-bold">Processando seu pagamento...</p>
    </div>

    <!-- Modal Pix -->
    <div id="pix-modal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full text-center shadow-2xl">
            <h3 class="text-2xl font-bold mb-4">Pagamento via Pix</h3>
            <p id="pix-instruction" class="text-slate-600 mb-6">Escaneie o QR Code abaixo ou copie o código para pagar.</p>
            
            <div id="pix-timer-container" class="hidden mb-4 py-2 px-4 bg-amber-50 rounded-lg border border-amber-200 text-amber-800 font-bold text-xl flex items-center justify-center gap-2">
                <i class="fas fa-clock"></i> <span id="pix-timer">00:00</span>
            </div>

            <div id="pix-qr-container" class="flex justify-center mb-6"></div>
            <div id="pix-code-container" class="bg-slate-100 p-3 rounded-lg flex items-center space-x-2 mb-6">
                <input type="text" id="pix-code" readonly class="bg-transparent border-none text-xs flex-1 outline-none">
                <button onclick="copyPixCode()" class="text-unn-azul-2 font-bold px-2">Copiar</button>
            </div>
            <p class="text-sm text-slate-500 mb-6">O acesso será liberado após a confirmação.</p>
            <a href="{{ route('panel.dashboard') }}" class="block w-full bg-slate-900 text-white font-bold py-3 rounded-xl">Já paguei / Ir para o Painel</a>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════════
         JAVASCRIPT — Gateway Selector + Mercado Pago Payment Brick
         ═══════════════════════════════════════════════════════════════════════════ --}}

    @if($showSelector)
    {{-- Multi-gateway: JS para alternar entre formulários --}}
    <script>
        /**
         * selectGateway(provider) — Oculta o seletor, exibe o formulário do gateway escolhido
         * e mostra o botão "Trocar gateway".
         */
        function selectGateway(provider) {
            // Atualizar input hidden
            var hiddenInput = document.getElementById('selected_gateway');
            if (hiddenInput) hiddenInput.value = provider;

            // Ocultar o seletor
            var selector = document.getElementById('gateway-selector');
            if (selector) selector.classList.add('hidden');

            // Ocultar todos os formulários
            var formMp = document.getElementById('form-mercadopago');
            var formSumup = document.getElementById('form-sumup');
            if (formMp) formMp.classList.add('hidden');
            if (formSumup) formSumup.classList.add('hidden');

            // Exibir o formulário correspondente
            var targetForm = document.getElementById('form-' + provider);
            if (targetForm) targetForm.classList.remove('hidden');

            // Exibir botão "Trocar gateway"
            var btnTrocar = document.getElementById('btn-trocar-gateway');
            if (btnTrocar) btnTrocar.classList.remove('hidden');

            // Inicializar o Mercado Pago Brick se necessário (lazy load)
            if (provider === 'mercadopago' && !window._mpBrickInitialized) {
                initMercadoPagoBrick();
            }
        }

        /**
         * showGatewaySelector() — Retorna ao seletor de gateway, ocultando formulários.
         */
        function showGatewaySelector() {
            // Ocultar formulários
            var formMp = document.getElementById('form-mercadopago');
            var formSumup = document.getElementById('form-sumup');
            if (formMp) formMp.classList.add('hidden');
            if (formSumup) formSumup.classList.add('hidden');

            // Ocultar botão trocar
            var btnTrocar = document.getElementById('btn-trocar-gateway');
            if (btnTrocar) btnTrocar.classList.add('hidden');

            // Exibir seletor
            var selector = document.getElementById('gateway-selector');
            if (selector) selector.classList.remove('hidden');

            // Limpar input hidden
            var hiddenInput = document.getElementById('selected_gateway');
            if (hiddenInput) hiddenInput.value = '';
        }
    </script>
    @endif

    @if(($showSelector) || (!$showSelector && $singleGateway === 'mercadopago'))
    {{-- Mercado Pago SDK e inicialização --}}
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>
        const mpPublicKey = '{{ $publicKey ?? '' }}';

        window._mpBrickInitialized = false;

        const showLoading = (show) => {
            document.getElementById('checkout-loading').style.display = show ? 'flex' : 'none';
        };

        const copyPixCode = () => {
            const el = document.getElementById('pix-code');
            el.select();
            document.execCommand('copy');
            toastr.success('Código Pix copiado!');
        };

        let pixTimerInterval;
        const startPixTimer = (expiresAtIso) => {
            const expiresAt = new Date(expiresAtIso).getTime();
            const timerContainer = document.getElementById('pix-timer-container');
            const timerSpan = document.getElementById('pix-timer');
            const instruction = document.getElementById('pix-instruction');
            const qrContainer = document.getElementById('pix-qr-container');
            const codeContainer = document.getElementById('pix-code-container');

            timerContainer.classList.remove('hidden');

            if (pixTimerInterval) clearInterval(pixTimerInterval);

            const updateTimer = () => {
                const now = new Date().getTime();
                const distance = expiresAt - now;

                if (distance <= 0) {
                    clearInterval(pixTimerInterval);
                    timerContainer.classList.replace('bg-amber-50', 'bg-red-50');
                    timerContainer.classList.replace('border-amber-200', 'border-red-200');
                    timerContainer.classList.replace('text-amber-800', 'text-red-800');
                    timerSpan.textContent = "Expirado";
                    instruction.textContent = "O tempo limite para pagamento deste Pix se esgotou. Feche e tente gerar um novo pedido.";
                    instruction.classList.add('text-red-600');
                    qrContainer.innerHTML = '<div class="w-48 h-48 bg-slate-100 flex items-center justify-center rounded-xl text-slate-400"><i class="fas fa-times-circle text-4xl"></i></div>';
                    if (codeContainer) codeContainer.classList.add('hidden');
                    return;
                }

                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                timerSpan.textContent = minutes.toString().padStart(2, '0') + ":" + seconds.toString().padStart(2, '0');
            };

            updateTimer();
            pixTimerInterval = setInterval(updateTimer, 1000);
        };

        function initMercadoPagoBrick() {
            if (window._mpBrickInitialized) return;
            window._mpBrickInitialized = true;

            if (!mpPublicKey || mpPublicKey.trim() === '') {
                console.error('MercadoPago: publicKey está vazia. Verifique as configurações do gateway.');
                document.getElementById('paymentBrick_container').innerHTML =
                    '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-4"></i><p class="font-semibold">Erro de configuração do gateway</p><p class="text-sm text-slate-500 mt-2">A chave pública do Mercado Pago não está configurada. Entre em contato com o administrador.</p></div>';
                return;
            }

            const mp = new MercadoPago(mpPublicKey, { locale: 'pt-BR' });
            const bricksBuilder = mp.bricks();

            @php
                $theme = \App\Models\Setting::get('gateway_checkout_theme', 'default');
                $primaryColor = \App\Models\Setting::get('gateway_checkout_primary_color', '#1F5EDB');

                $methodCreditCard = (bool) \App\Models\Setting::get('mercadopago_method_credit_card', 1);
                $methodDebitCard = (bool) \App\Models\Setting::get('mercadopago_method_debit_card', 0);
                $methodPix = (bool) \App\Models\Setting::get('mercadopago_method_pix', 1);
                $methodTicket = (bool) \App\Models\Setting::get('mercadopago_method_ticket', 0);
                $methodMercadoPago = (bool) (\App\Models\Setting::get('mercadopago_method_mercadopago') 
                                        ?? \App\Models\Setting::get('mercadopago_method_wallet') 
                                        ?? \App\Models\Setting::get('mercadopago_method_account_money', 0));
            @endphp

            const renderPaymentBrick = async (bricksBuilder) => {
                const paymentMethods = {};
                @if($methodCreditCard) paymentMethods.creditCard = "all"; @endif
                @if($methodDebitCard) paymentMethods.debitCard = "all"; @endif
                @if($methodPix) paymentMethods.bankTransfer = "all"; @endif
                @if($methodTicket) paymentMethods.ticket = "all"; @endif
                @if($methodMercadoPago) paymentMethods.mercadoPago = "all"; @endif

                const settings = {
                    initialization: {
                        amount: {{ $order->total_amount }},
                        preferenceId: '{{ $preferenceId ?? '' }}',
                        payer: {
                            email: '{{ $order->user->email }}',
                        }
                    },
                    customization: {
                        paymentMethods: paymentMethods,
                        visual: {
                            style: {
                                theme: '{{ $theme }}',
                                customVariables: {
                                    baseColor: '{{ $primaryColor }}',
                                    formBackgroundColor: '#ffffff',
                                    formInputsTextSize: '14px',
                                    formInputsHeight: '48px',
                                    buttonHeight: '50px',
                                    borderRadius: '12px',
                                }
                            }
                        }
                    },
                    callbacks: {
                        onReady: () => {
                            console.log('Payment Brick Ready');
                        },
                        onSubmit: ({ selectedPaymentMethod, formData }) => {
                            return new Promise((resolve, reject) => {
                                showLoading(true);
                                
                                fetch('{{ route('checkout.process_payment') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        order_id: '{{ $order->id }}',
                                        formData: formData
                                    })
                                })
                                .then(async response => {
                                    const raw = await response.text();
                                    let data = {};

                                    try {
                                        data = raw ? JSON.parse(raw) : {};
                                    } catch (e) {
                                        throw new Error('Resposta invalida do servidor (HTTP ' + response.status + ').');
                                    }

                                    if (!response.ok) {
                                        throw new Error(data.error || 'Erro HTTP ' + response.status);
                                    }

                                    return data;
                                })
                                .then(data => {
                                    showLoading(false);
                                    if (data.success) {
                                        if (data.qr_code) {
                                            document.getElementById('pix-qr-container').innerHTML = '<img src="data:image/png;base64,' + data.qr_code_base64 + '" alt="Pix QR" class="w-48 h-48">';
                                            document.getElementById('pix-code').value = data.qr_code;
                                            document.getElementById('pix-modal').classList.remove('hidden');
                                            
                                            if (data.expires_at) {
                                                startPixTimer(data.expires_at);
                                            }

                                            resolve();
                                        } else if (data.redirect) {
                                            window.location.href = data.redirect;
                                            resolve();
                                        }
                                    } else {
                                        toastr.error(data.error || 'Erro ao processar pagamento');
                                        reject();
                                    }
                                })
                                .catch(error => {
                                    showLoading(false);
                                    console.error('Checkout Error:', error);
                                    toastr.error(error.message || 'Erro de conexão. Tente novamente.');
                                    reject();
                                });
                            });
                        },
                        onError: (error) => {
                            console.error('Mercado Pago Brick Error:', error);
                            toastr.error('Erro ao carregar o gateway de pagamento.');
                        },
                    },
                };

                window.paymentBrickController = await bricksBuilder.create(
                    'payment',
                    'paymentBrick_container',
                    settings
                );
            };

            renderPaymentBrick(bricksBuilder);
        }

        @if(!$showSelector)
        // Single gateway (MP): inicializar imediatamente
        document.addEventListener('DOMContentLoaded', function() {
            initMercadoPagoBrick();
        });
        @endif
    </script>
    @endif
@endsection
