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
                            <img src="{{ asset($plan->image) }}" alt="{{ $plan->name }}" class="w-16 h-16 rounded-lg object-cover">
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
                            <span>R$ {{ number_format($plan->price, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-green-600 font-medium">
                            <span>Desconto</span>
                            <span>- R$ 0,00</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-4 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-900">Total</span>
                            <span class="text-2xl font-black text-blue-600">R$ {{ number_format($plan->price, 2, ',', '.') }}</span>
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
                        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6">
                            <i class="fas fa-triangle-exclamation mr-2"></i>
                            Pagamento indisponível no momento. Configure as credenciais do MercadoPago no painel para habilitar assinaturas.
                        </div>
                    @endif

                    @guest
                    <!-- Passo 1: Identificação -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 mb-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm">1</span>
                            Seus Dados
                        </h2>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo</label>
                                <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">CPF</label>
                                <input type="text" name="cpf" value="{{ old('cpf') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" placeholder="000.000.000-00" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Celular</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Senha de Acesso</label>
                                <input type="password" name="password" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Senha</label>
                                <input type="password" name="password_confirmation" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 mb-8 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-blue-600 font-bold text-xl shadow-sm">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Logado como {{ Auth::user()->name }}</p>
                                <p class="text-sm text-gray-600">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-sm text-red-500 hover:text-red-700 font-medium">Trocar conta</a>
                    </div>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                    @endguest

                    <!-- Passo 2: Pagamento -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm">2</span>
                            Pagamento
                        </h2>

                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="credit_card" class="peer sr-only" checked>
                                <div class="p-4 border-2 border-gray-200 rounded-xl peer-checked:border-blue-600 peer-checked:bg-blue-50 transition text-center hover:border-blue-300">
                                    <i class="fas fa-credit-card text-2xl mb-2 text-gray-600 peer-checked:text-blue-600"></i>
                                    <p class="font-bold text-gray-900">Cartão de Crédito</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="pix" class="peer sr-only">
                                <div class="p-4 border-2 border-gray-200 rounded-xl peer-checked:border-blue-600 peer-checked:bg-blue-50 transition text-center hover:border-blue-300">
                                    <i class="fa-brands fa-pix text-2xl mb-2 text-gray-600 peer-checked:text-blue-600"></i>
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
                            <input type="hidden" name="transaction_amount" id="transaction_amount" value="{{ $plan->price }}">
                            <input type="hidden" name="installments" id="installments">
                        </div>

                        <!-- Pix Info -->
                        <div id="pixInfo" class="hidden text-center py-8">
                            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 text-green-600">
                                <i class="fa-brands fa-pix text-4xl"></i>
                            </div>
                            <h3 class="font-bold text-gray-900 mb-2">Pagamento Instantâneo</h3>
                            <p class="text-gray-600 max-w-sm mx-auto">Ao finalizar, será gerado um QR Code para pagamento. Seu acesso será liberado imediatamente após a confirmação.</p>
                        </div>

                        <button type="submit"
                            class="w-full btn-primary text-white py-4 rounded-xl font-bold text-lg mt-8 shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
                            {{ (($plan->price ?? 0) > 0 && !($paymentConfigured ?? false)) ? 'disabled' : '' }}>
                            <i class="fas fa-lock"></i> Finalizar Pagamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if(($paymentConfigured ?? false) && ($plan->price ?? 0) > 0)
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
    const mp = new MercadoPago("{{ $publicKey }}");
    
    // Toggle Payment Methods
    const radios = document.querySelectorAll('input[name="payment_method"]');
    const cardForm = document.getElementById('creditCardForm');
    const pixInfo = document.getElementById('pixInfo');
    const submitBtn = document.querySelector('button[type="submit"]');

    radios.forEach(radio => {
        radio.addEventListener('change', (e) => {
            if (e.target.value === 'pix') {
                cardForm.classList.add('hidden');
                pixInfo.classList.remove('hidden');
                submitBtn.innerText = 'Gerar QR Code Pix';
            } else {
                cardForm.classList.remove('hidden');
                pixInfo.classList.add('hidden');
                submitBtn.innerText = 'Finalizar Pagamento';
            }
        });
    });

    // Initialize Card Payment Brick
    const bricksBuilder = mp.bricks();
    const renderCardPaymentBrick = async (bricksBuilder) => {
        const settings = {
            initialization: {
                amount: {{ $plan->price }},
                payer: {
                    email: "{{ Auth::check() ? Auth::user()->email : 'test@test.com' }}", // Pre-fill if logged in
                },
            },
            customization: {
                visual: {
                    style: {
                        theme: 'default',
                    },
                },
                paymentMethods: {
                    maxInstallments: 12,
                },
            },
            callbacks: {
                onReady: () => {
                    // Brick is ready
                },
                onSubmit: ({ selectedPaymentMethod, formData }) => {
                    // Populate hidden inputs
                    document.getElementById('token').value = formData.token;
                    document.getElementById('issuer_id').value = formData.issuer_id;
                    document.getElementById('payment_method_id').value = formData.payment_method_id;
                    document.getElementById('installments').value = formData.installments;
                    
                    // Submit form
                    return new Promise((resolve, reject) => {
                        document.getElementById('paymentForm').submit();
                        resolve();
                    });
                },
                onError: (error) => {
                    console.error(error);
                },
            },
        };
        window.cardPaymentBrickController = await bricksBuilder.create(
            'cardPayment',
            'cardPaymentBrick_container',
            settings
        );
    };

    // Only render brick if card is selected (default)
    renderCardPaymentBrick(bricksBuilder);
</script>
@endif
@endpush
@endsection
