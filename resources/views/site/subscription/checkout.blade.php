@extends('layouts.app')

@section('title', 'Assinar Plano ' . $plan->name)

@section('content')
    <div class="min-h-screen bg-slate-50 pt-32 pb-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Sidebar: Resumo do Pedido -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-32">
                        <h3 class="text-lg font-bold text-gray-900 mb-6">Resumo do Pedido</h3>

                        <div class="flex items-center gap-4 mb-6">
                            @if($plan->image)
                                <img src="{{ asset($plan->image) }}" alt="{{ $plan->name }}"
                                    class="w-16 h-16 rounded-lg object-cover">
                            @else
                                <div class="w-16 h-16 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                                    <i class="fas fa-crown text-2xl"></i>
                                </div>
                            @endif
                            <div>
                                <h4 class="font-bold text-gray-900">{{ $plan->name }}</h4>
                                <p class="text-sm text-gray-500">Assinatura {{ $plan->period }} dias</p>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 py-4 space-y-2">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>R$ {{ number_format((float) $plan->price, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-green-600 font-medium">
                                <span>Desconto</span>
                                <span>- R$ 0,00</span>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-900">Total</span>
                                <span class="text-2xl font-black text-blue-600">R$
                                    {{ number_format((float) $plan->price, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="bg-blue-50 rounded-xl p-4 text-sm text-blue-700">
                            <i class="fas fa-shield-alt mr-2"></i> Pagamento 100% seguro via MercadoPago
                        </div>
                    </div>
                </div>

                <!-- Main: Formulário -->
                <div class="lg:col-span-2">
                    <form action="{{ route('subscription.process', $plan->id) }}" method="POST" id="paymentForm">
                        @csrf
                        {{-- Campo sincronizado com os radios via JS --}}
                        <input type="hidden" name="payment_method" id="payment_method_hidden" value="credit_card">

                        @if ($errors->any())
                            <div class="bg-red-50 border border-red-200 text-red-600 p-4 rounded-xl mb-6">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(($plan->price ?? 0) > 0 && !($paymentConfigured ?? false))
                            @if(config('app.debug'))
                                <div
                                    class="bg-amber-50 border border-amber-200 text-amber-700 p-4 rounded-xl mb-6 flex items-center gap-3">
                                    <i class="fas fa-flask-vial text-xl text-amber-500"></i>
                                    <div>
                                        <p class="font-bold">Modo de Simulação (Debug)</p>
                                        <p class="text-sm">As credenciais do MercadoPago não foram detectadas. Como o site está em
                                            modo de testes, você pode finalizar a compra para validar o fluxo.</p>
                                    </div>
                                </div>
                            @else
                                <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6">
                                    <i class="fas fa-triangle-exclamation mr-2"></i>
                                    Pagamento indisponível no momento. Configure as credenciais do MercadoPago no painel para
                                    habilitar assinaturas.
                                </div>
                            @endif
                        @endif

                        @guest
                            <!-- Passo 1: Identificação -->
                            <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 mb-8">
                                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                    <span
                                        class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm">1</span>
                                    Seus Dados
                                </h2>

                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo</label>
                                        <input type="text" name="name" value="{{ old('name') }}"
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                            required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                                        <input type="email" name="email" value="{{ old('email') }}"
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                            required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">CPF</label>
                                        <input type="text" name="cpf" value="{{ old('cpf') }}"
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="000.000.000-00" required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Celular</label>
                                        <input type="text" name="phone" value="{{ old('phone') }}"
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                            required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Senha de Acesso</label>
                                        <input type="password" name="password"
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                            required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Senha</label>
                                        <input type="password" name="password_confirmation"
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                            required>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div
                                class="bg-blue-50 border border-blue-100 rounded-2xl p-6 mb-8 flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-blue-600 font-bold text-xl shadow-sm">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">Logado como {{ Auth::user()->name }}</p>
                                        <p class="text-sm text-gray-600">{{ Auth::user()->email }}</p>
                                    </div>
                                </div>
                                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                    class="text-sm text-red-500 hover:text-red-700 font-medium">Trocar conta</a>
                            </div>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                        @endguest

                        {{-- CPF para usuários logados que não preencheram ainda --}}
                        @auth
                            @if(!Auth::user()->doc)
                                <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 mb-8">
                                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                                        <span
                                            class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm"><i
                                                class="fas fa-id-card text-xs"></i></span>
                                        Documento (CPF)
                                    </h2>
                                    <p class="text-sm text-gray-500 mb-4">Necessário para processamento do pagamento.</p>
                                    <input type="text" name="cpf" value="{{ old('cpf') }}"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="000.000.000-00" required>
                                </div>
                            @endif
                        @endauth

                        <!-- Passo 2: Pagamento -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
                            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                <span
                                    class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm">2</span>
                                Pagamento
                            </h2>

                            <div class="grid grid-cols-2 gap-4 mb-8">
                                <label class="cursor-pointer" id="label-credit-card">
                                    <input type="radio" name="payment_method_radio" value="credit_card"
                                        id="radio-credit-card" class="peer sr-only" checked>
                                    <div
                                        class="p-4 border-2 border-gray-200 rounded-xl peer-checked:border-blue-600 peer-checked:bg-blue-50 transition text-center hover:border-blue-300">
                                        <i class="fas fa-credit-card text-2xl mb-2 text-gray-600"></i>
                                        <p class="font-bold text-gray-900">Cartão de Crédito</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer" id="label-pix">
                                    <input type="radio" name="payment_method_radio" value="pix" id="radio-pix"
                                        class="peer sr-only">
                                    <div
                                        class="p-4 border-2 border-gray-200 rounded-xl peer-checked:border-blue-600 peer-checked:bg-blue-50 transition text-center hover:border-blue-300">
                                        <i class="fa-brands fa-pix text-2xl mb-2 text-gray-600"></i>
                                        <p class="font-bold text-gray-900">Pix Automático</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Formulário Cartão -->
                            <div id="creditCardForm" class="space-y-6">
                                <!-- SDK MercadoPago será injetado aqui -->
                                <div id="cardPaymentBrick_container"></div>

                                <!-- Hidden inputs populated by JS -->
                                <input type="hidden" name="token" id="token">
                                <input type="hidden" name="issuer_id" id="issuer_id">
                                <input type="hidden" name="payment_method_id" id="payment_method_id">
                                <input type="hidden" name="transaction_amount" id="transaction_amount"
                                    value="{{ $plan->price }}">
                                <input type="hidden" name="installments" id="installments">
                            </div>

                            <!-- Pix Info -->
                            <div id="pixInfo" class="hidden text-center py-8">
                                <div
                                    class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 text-green-600">
                                    <i class="fa-brands fa-pix text-4xl"></i>
                                </div>
                                <h3 class="font-bold text-gray-900 mb-2">Pagamento Instantâneo</h3>
                                <p class="text-gray-600 max-w-sm mx-auto">Ao finalizar, será gerado um QR Code para
                                    pagamento. Seu acesso será liberado imediatamente após a confirmação.</p>
                            </div>

                            <button type="submit"
                                class="w-full btn-primary text-white py-4 rounded-xl font-bold text-lg mt-8 shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
                                {{ (($plan->price ?? 0) > 0 && !($paymentConfigured ?? false) && !config('app.debug')) ? 'disabled' : '' }}>
                                <i class="fas fa-lock"></i> Finalizar Pagamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            /* ─── Isolar Brick do MercadoPago ─── */
            /* O CSS global tem: iframe { height: auto } que destrói o Brick.
               height: unset reverte para o comportamento padrão do iframe. */
            #cardPaymentBrick_container iframe {
                height: unset !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
            #cardPaymentBrick_container {
                min-height: 320px;
                width: 100%;
                margin-bottom: 2rem;
                box-sizing: border-box;
            }

            /* Em modo Debug/Simulação, campos fake bonitões */
            .simulation-field {
                display: block;
                width: 100%;
                border-radius: 0.75rem;
                border: 1px solid #d1d5db;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
                margin-top: 0.25rem;
                color: #4b5563;
                background-color: #f9fafb;
                outline: none;
                transition: border-color 0.15s;
            }

            .simulation-field:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            }
        </style>
        @if((($paymentConfigured ?? false) || config('app.debug')) && ($plan->price ?? 0) > 0)
            @php
                $theme = \App\Models\Setting::get('gateway_checkout_theme', 'default');
                $primaryColor = \App\Models\Setting::get('gateway_checkout_primary_color', '#1F5EDB');
            @endphp

            @if($paymentConfigured ?? false)
                <script src="https://sdk.mercadopago.com/js/v2"></script>
            @endif

            <script>
                // Toggle Payment Methods — sincroniza o hidden que vai no POST
                const radios = document.querySelectorAll('input[name="payment_method_radio"]');
                const hiddenMethod = document.getElementById('payment_method_hidden');
                const cardForm = document.getElementById('creditCardForm');
                const pixInfo = document.getElementById('pixInfo');
                const submitBtn = document.querySelector('button[type="submit"]');

                function syncPaymentMethod(value) {
                    hiddenMethod.value = value;
                    if (value === 'pix') {
                        cardForm.classList.add('hidden');
                        pixInfo.classList.remove('hidden');
                        submitBtn.classList.remove('hidden');
                        submitBtn.innerHTML = '<i class="fas fa-qrcode mr-2"></i> Gerar QR Code Pix';
                    } else {
                        cardForm.classList.remove('hidden');
                        pixInfo.classList.add('hidden');
                        submitBtn.innerHTML = '<i class="fas fa-lock mr-2"></i> Finalizar Pagamento';
                    }
                }

                radios.forEach(radio => {
                    radio.addEventListener('change', (e) => syncPaymentMethod(e.target.value));
                });

                if (typeof MercadoPago !== 'undefined' && "{{ $publicKey ?? '' }}") {
                    const mp = new MercadoPago("{{ $publicKey }}");
                    const bricksBuilder = mp.bricks();

                    const renderCardPaymentBrick = async (bricksBuilder) => {
                        const settings = {
                            initialization: {
                                amount: {{ $plan->price }},
                                payer: {
                                    email: "{{ Auth::check() ? Auth::user()->email : 'test@test.com' }}",
                                },
                            },
                            customization: {
                                visual: {
                                    style: {
                                        theme: '{{ $theme }}',
                                        customVariables: {
                                            baseColor: '{{ $primaryColor }}',
                                            formBackgroundColor: '#ffffff',
                                            borderRadius: '12px',
                                        }
                                    },
                                },
                                paymentMethods: {
                                    maxInstallments: 12,
                                },
                            },
                            callbacks: {
                                onReady: () => {
                                    // Se brick carregou e payment_method é cartão, esconder botão externo
                                    const selected = document.querySelector('input[name="payment_method_radio"]:checked');
                                    if (!selected || selected.value === 'credit_card') {
                                        submitBtn.classList.add('hidden');
                                    }
                                },
                                onSubmit: ({ selectedPaymentMethod, formData }) => {
                                    document.getElementById('token').value = formData.token;
                                    document.getElementById('issuer_id').value = formData.issuer_id;
                                    document.getElementById('payment_method_id').value = formData.payment_method_id;
                                    document.getElementById('installments').value = formData.installments;

                                    return new Promise((resolve) => {
                                        document.getElementById('paymentForm').submit();
                                        resolve();
                                    });
                                },
                                onError: (error) => {
                                    console.error(error);
                                    Swal.fire('Erro', 'Verifique os dados do cartão e tente novamente.', 'error');
                                },
                            },
                        };
                        window.cardPaymentBrickController = await bricksBuilder.create(
                            'cardPayment',
                            'cardPaymentBrick_container',
                            settings
                        );
                    };

                    renderCardPaymentBrick(bricksBuilder);
                } else if ("{{ config('app.debug') }}") {
                    console.log("Modo Simulação Ativo: SDK do MercadoPago suprimido.");
                    document.getElementById('cardPaymentBrick_container').innerHTML = `
                                                <div class="p-8 border border-amber-200 rounded-3xl bg-amber-50/30 space-y-4">
                                                    <div class="flex items-center gap-3 text-amber-700 mb-4">
                                                        <i class="fas fa-flask text-2xl"></i>
                                                        <h4 class="font-black italic">MODO SIMULADOR</h4>
                                                    </div>

                                                    <div class="space-y-3">
                                                        <div>
                                                            <label class="text-[10px] font-black text-amber-900 uppercase tracking-widest">Número do Cartão (Simulado)</label>
                                                            <input type="text" class="simulation-field" value="4444 4444 4444 4444" disabled>
                                                        </div>

                                                        <div class="grid grid-cols-2 gap-4">
                                                            <div>
                                                                <label class="text-[10px] font-black text-amber-900 uppercase tracking-widest">Validade</label>
                                                                <input type="text" class="simulation-field" value="12/28" disabled>
                                                            </div>
                                                            <div>
                                                                <label class="text-[10px] font-black text-amber-900 uppercase tracking-widest">CVV</label>
                                                                <input type="text" class="simulation-field" value="123" disabled>
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <label class="text-[10px] font-black text-amber-900 uppercase tracking-widest">Nome do Titular</label>
                                                            <input type="text" class="simulation-field" value="{{ Auth::check() ? strtoupper(Auth::user()->name) : 'CLIENTE TESTE' }}" disabled>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 bg-white/50 rounded-2xl border border-amber-100 mt-4">
                                                        <p class="text-[11px] text-amber-700 leading-relaxed font-medium">
                                                            <i class="fas fa-info-circle mr-1"></i> As chaves do MercadoPago não foram configuradas. 
                                                            Como o <strong>Debug</strong> está ativo, este formulário fake permite testar o fluxo de adesão.
                                                        </p>
                                                    </div>
                                                </div>
                                            `;
                }

                // Nota: a sincronização do botão é gerenciada integralmente por syncPaymentMethod().
            </script>
        @endif
    @endpush
@endsection