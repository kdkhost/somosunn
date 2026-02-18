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
        /* Ajustes finos para o Brick não quebrar layout */
        #paymentBrick_container {
            min-height: 400px;
        }

        /* Estilização para o seletor de parcelas e outros inputs do MP */
        .mp-checkout-custom .svelte-payment-brick {
            font-family: 'Inter', sans-serif !important;
        }
    </style>

    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>
        const mp = new MercadoPago('{{ $publicKey }}', {
            locale: 'pt-BR'
        });
        const bricksBuilder = mp.bricks();

        @php
            $theme = \App\Models\Setting::get('gateway_checkout_theme', 'default');
            $primaryColor = \App\Models\Setting::get('gateway_checkout_primary_color', '#1F5EDB');
        @endphp

        const renderPaymentBrick = async (bricksBuilder) => {
            const settings = {
                initialization: {
                    amount: {{ $order->total_amount }},
                    preferenceId: '{{ $preferenceId }}',
                    payer: {
                        email: '{{ $order->user->email }}',
                    }
                },
                customization: {
                    paymentMethods: {
                        ticket: "all",
                        bankTransfer: "all", // PIX
                        creditCard: "all",
                        debitCard: "all",
                        mercadoPago: "all",
                    },
                    visual: {
                        style: {
                            theme: '{{ $theme }}', // 'default', 'dark', 'bootstrap', 'flat'
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
                        // O processamento é feito via preference ID configurado no controller
                    },
                    onError: (error) => {
                        console.error('Payment Brick Error:', error);
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
