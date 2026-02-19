@extends('layouts.app')

@section('title', 'Checkout Seguro - ' . config('app.name'))

@section('content')
    <div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto">
            <!-- Header do Checkout -->
            <div class="text-center mb-10">
                <h1 class="text-3xl font-extrabold text-slate-900 sm:text-4xl">
                    Finalizar seu Pedido
                </h1>
                <p class="mt-3 max-w-2xl mx-auto text-xl text-slate-500 sm:mt-4">
                    Pagamento seguro e processamento instantâneo via Mercado Pago.
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
                                <i class="fas fa-pix text-slate-400 text-xl"></i>
                            </div>
                        </div>
                        <div class="p-6 sm:p-8">
                            <div id="paymentBrick_container"></div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-center space-x-4 text-slate-400 text-sm">
                        <div class="flex items-center">
                            <i class="fas fa-lock mr-2 text-green-500"></i>
                            <span>Ambiente Criptografado</span>
                        </div>
                        <span>•</span>
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
                                    <span class="font-semibold">R$ {{ number_format($item->price, 2, ',', '.') }}</span>
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

                                <div class="flex justify-between text-2xl font-extrabold pt-2">
                                    <span>Total</span>
                                    <span class="text-white">R$
                                        {{ number_format($order->total_amount, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 bg-slate-800 rounded-xl p-4 flex items-start space-x-3">
                            <i class="fas fa-info-circle text-unn-azul-2 mt-1"></i>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                O acesso ao conteúdo será liberado imediatamente após a confirmação do pagamento pelo
                                Mercado Pago.
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
    </style>

    <div class="loading-overlay" id="checkout-loading">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-unn-azul-2 mb-4"></div>
        <p class="text-slate-900 font-bold">Processando seu pagamento...</p>
    </div>

    <!-- Modal Pix -->
    <div id="pix-modal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full text-center shadow-2xl">
            <h3 class="text-2xl font-bold mb-4">Pagamento via Pix</h3>
            <p class="text-slate-600 mb-6">Escaneie o QR Code abaixo ou copie o código para pagar.</p>
            <div id="pix-qr-container" class="flex justify-center mb-6"></div>
            <div class="bg-slate-100 p-3 rounded-lg flex items-center space-x-2 mb-6">
                <input type="text" id="pix-code" readonly class="bg-transparent border-none text-xs flex-1 outline-none">
                <button onclick="copyPixCode()" class="text-unn-azul-2 font-bold px-2">Copiar</button>
            </div>
            <p class="text-sm text-slate-500 mb-6">O acesso será liberado após a confirmação.</p>
            <a href="{{ route('panel.dashboard') }}" class="block w-full bg-slate-900 text-white font-bold py-3 rounded-xl">Já paguei / Ir para o Painel</a>
        </div>
    </div>

    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>
        const mp = new MercadoPago('{{ $publicKey }}', {
            locale: 'pt-BR'
        });
        const bricksBuilder = mp.bricks();

        @php
            $theme = \App\Models\Setting::get('gateway_checkout_theme', 'default');
            $primaryColor = \App\Models\Setting::get('gateway_checkout_primary_color', '#1F5EDB');

            // Ler configuração de meios de pagamento habilitados pelo admin
            $methodCreditCard = (bool) \App\Models\Setting::get('mercadopago_method_credit_card', 1);
            $methodDebitCard = (bool) \App\Models\Setting::get('mercadopago_method_debit_card', 0);
            $methodPix = (bool) \App\Models\Setting::get('mercadopago_method_pix', 1);
            $methodTicket = (bool) \App\Models\Setting::get('mercadopago_method_ticket', 0);
            $methodMercadoPago = (bool) \App\Models\Setting::get('mercadopago_method_mercadopago', 0);
        @endphp

        const showLoading = (show) => {
            document.getElementById('checkout-loading').style.display = show ? 'flex' : 'none';
        };

        const copyPixCode = () => {
            const el = document.getElementById('pix-code');
            el.select();
            document.execCommand('copy');
            toastr.success('Código Pix copiado!');
        };

        const renderPaymentBrick = async (bricksBuilder) => {
            // Construir paymentMethods dinamicamente — omitir a chave desabilita o método
            const paymentMethods = {};
            @if($methodCreditCard) paymentMethods.creditCard = "all"; @endif
            @if($methodDebitCard) paymentMethods.debitCard = "all"; @endif
            @if($methodPix) paymentMethods.bankTransfer = "all"; @endif
            @if($methodTicket) paymentMethods.ticket = "all"; @endif
            @if($methodMercadoPago) paymentMethods.mercadoPago = "all"; @endif

            console.log('Payment Methods configurados:', paymentMethods);
            console.log('Payment Methods configurados:', paymentMethods);
            console.log('Checkout Theme Raw:', '{{ $theme }}');
            console.log('Primary Color Raw:', '{{ $primaryColor }}');

            const settings = {
                initialization: {
                    amount: {{ $order->total_amount }},
                    preferenceId: '{{ $preferenceId }}',
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
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    order_id: '{{ $order->id }}',
                                    formData: formData
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                showLoading(false);
                                if (data.success) {
                                    if (data.qr_code) {
                                        // Mostrar modal Pix
                                        document.getElementById('pix-qr-container').innerHTML = `<img src="data:image/png;base64,${data.qr_code_base64}" alt="Pix QR" class="w-48 h-48">`;
                                        document.getElementById('pix-code').value = data.qr_code;
                                        document.getElementById('pix-modal').classList.remove('hidden');
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
                                toastr.error('Erro de conexão. Tente novamente.');
                                reject();
                            });
                        });
                    },
                    onError: (error) => {
                        console.error('Payment Brick Error:', error);
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
    </script>
@endsection
