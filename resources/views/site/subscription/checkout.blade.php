@extends('layouts.app')

@section('title', 'Assinar Plano ' . $plan->name)

{{-- CSS do Brick no HEAD (antes do SDK renderizar) --}}
@push('styles')
    <style>
        /*
                         * CONTAINER DO BRICK DO MERCADOPAGO
                         * ─────────────────────────────────
                         * NÃO adicionar regras em `iframe` ou `*` dentro deste container:
                         * o Brick gerencia seu CSS via Shadow DOM e JS interno.
                         * Qualquer reset externo (font-family, line-height, height) destrói
                         * o sistema de labels flutuantes e a renderização dos campos PCI.
                         * A única correção necessária foi remover `iframe { height:auto }`
                         * do CSS global do app.blade.php (já feito).
                        */

        #cardPaymentBrick_container {
            min-height: 340px;
            width: 100%;
            margin-bottom: 2rem;
            box-sizing: border-box;
        }

        /* Campos do modo Simulação */
        .sim-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #92400e;
            margin-bottom: 4px;
        }

        .sim-input {
            display: block;
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #fcd34d;
            border-radius: 10px;
            font-size: 0.925rem;
            color: #1f2937;
            background: #fffbeb;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.15s;
        }

        .sim-input:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        }

        .sim-input[disabled] {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
@endpush

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
                                <p class="text-sm text-gray-500">Assinatura {{ ucfirst($period ?? 'mensal') }}</p>
                            </div>
                        </div>

                        {{-- Seletor de período --}}
                        @php
                            $availablePeriods = $availablePeriods ?? ['mensal' => (float)$plan->price];
                            $selectedPeriod   = $period ?? 'mensal';
                            $effectivePrice   = $effectivePrice ?? $plan->price;
                            $prorataAmount    = $prorataAmount ?? null;
                            $isUpgrade        = $isUpgrade ?? false;
                            $isDowngrade      = $isDowngrade ?? false;
                        @endphp

                        @if(count($availablePeriods) > 1)
                        <div class="mb-4">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Período</p>
                            <div class="flex flex-col gap-2" id="period-options">
                                @foreach($availablePeriods as $pk => $pv)
                                @php
                                    $periodNames = ['mensal'=>'Mensal','trimestral'=>'Trimestral (3 meses)','semestral'=>'Semestral (6 meses)','anual'=>'Anual (12 meses)'];
                                    $monthly = $availablePeriods['mensal'] ?? 0;
                                    $months = ['trimestral'=>3,'semestral'=>6,'anual'=>12];
                                    $pct = 0;
                                    if ($pk !== 'mensal' && $monthly > 0 && isset($months[$pk])) {
                                        $full = $monthly * $months[$pk];
                                        $pct = $full > 0 ? round((1 - $pv/$full)*100) : 0;
                                    }
                                @endphp
                                <label class="flex items-center justify-between gap-3 p-3 rounded-xl border-2 cursor-pointer transition
                                    {{ $pk === $selectedPeriod ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}"
                                    data-period-opt="{{ $pk }}" data-price="{{ $pv }}">
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="period" value="{{ $pk }}" form="paymentForm"
                                            class="text-blue-600 period-radio"
                                            {{ $pk === $selectedPeriod ? 'checked' : '' }}>
                                        <span class="font-medium text-sm">{{ $periodNames[$pk] ?? ucfirst($pk) }}</span>
                                        @if($pct > 0)
                                            <span class="text-xs bg-emerald-100 text-emerald-700 font-bold px-2 py-0.5 rounded-full">-{{ $pct }}%</span>
                                        @endif
                                    </div>
                                    <span class="font-bold text-gray-900 text-sm" data-pv="{{ $pk }}">R$ {{ number_format($pv, 2, ',', '.') }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <input type="hidden" name="period" value="{{ $selectedPeriod }}" form="paymentForm">
                        @endif

                        <div class="border-t border-gray-100 py-4 space-y-2">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span id="checkout-subtotal">R$ {{ number_format((float) $effectivePrice, 2, ',', '.') }}</span>
                            </div>
                            @if($isUpgrade && $prorataAmount !== null && $prorataAmount < $plan->getPriceForPeriod($selectedPeriod))
                            <div class="flex justify-between text-emerald-600 font-medium text-sm">
                                <span><i class="fas fa-bolt mr-1"></i> Desconto prorrata (upgrade)</span>
                                <span>- R$ {{ number_format($plan->getPriceForPeriod($selectedPeriod) - $prorataAmount, 2, ',', '.') }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between text-green-600 font-medium">
                                <span>Desconto cupom</span>
                                <span>- R$ 0,00</span>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-900">Total</span>
                                <span class="text-2xl font-black text-blue-600" id="checkout-total">R$
                                    {{ number_format((float) $effectivePrice, 2, ',', '.') }}</span>
                            </div>
                            @if($isDowngrade)
                            <p class="text-xs text-amber-600 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Downgrade: o novo plano será ativado ao término do período atual.
                            </p>
                            @endif
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

                        @if(session('error'))
                            <div
                                class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6 flex items-start gap-3">
                                <i class="fas fa-circle-xmark mt-0.5 text-red-500"></i>
                                <span>{{ session('error') }}</span>
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
                                <!-- CPF preenchido no campo Documento do Brick -->
                                <input type="hidden" name="cpf" id="cpf_hidden" value="{{ Auth::user()?->doc ?? '' }}">
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
                        // Para credit_card o Brick tem seu próprio botão — esconder o externo
                        submitBtn.classList.add('hidden');
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
                                    // Sempre esconde o botão externo ao carregar o Brick de cartão
                                    submitBtn.classList.add('hidden');
                                },
                                onSubmit: ({ selectedPaymentMethod, formData }) => {
                                    // Popula os campos hidden com dados do Brick
                                    document.getElementById('token').value = formData.token ?? '';
                                    document.getElementById('issuer_id').value = formData.issuer_id ?? '';
                                    document.getElementById('payment_method_id').value = formData.payment_method_id ?? '';
                                    document.getElementById('installments').value = formData.installments ?? 1;

                                    // CPF do Brick (campo Documento do Titular)
                                    const brickCpf = formData?.payer?.identification?.number
                                        ?? formData?.identification?.number
                                        ?? '';
                                    if (brickCpf) {
                                        document.getElementById('cpf_hidden') &&
                                            (document.getElementById('cpf_hidden').value = brickCpf);
                                    }

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
                    console.log("Modo Simulação Ativo.");
                    document.getElementById('cardPaymentBrick_container').innerHTML = `
                                                                        <div style="padding:24px;border:2px dashed #fcd34d;border-radius:16px;background:#fffbeb;">
                                                                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                                                                                <i class="fas fa-flask" style="font-size:1.4rem;color:#b45309;"></i>
                                                                                <span style="font-weight:900;color:#92400e;font-size:0.95rem;letter-spacing:.05em;">MODO SIMULAÇÃO — Dados fictícios para teste</span>
                                                                            </div>
                                                                            <div style="display:grid;gap:14px;">
                                                                                <div>
                                                                                    <label class="sim-label">Número do Cartão</label>
                                                                                    <input class="sim-input" type="text" value="5031 4332 1540 6351" disabled>
                                                                                </div>
                                                                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                                                                    <div>
                                                                                        <label class="sim-label">Validade</label>
                                                                                        <input class="sim-input" type="text" value="11/30" disabled>
                                                                                    </div>
                                                                                    <div>
                                                                                        <label class="sim-label">CVV</label>
                                                                                        <input class="sim-input" type="text" value="123" disabled>
                                                                                    </div>
                                                                                </div>
                                                                                <div>
                                                                                    <label class="sim-label">Nome do titular</label>
                                                                                    <input class="sim-input" type="text" value="{{ Auth::check() ? strtoupper(Auth::user()->name) : 'CLIENTE TESTE' }}" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <p style="margin-top:16px;font-size:0.75rem;color:#92400e;line-height:1.5;">
                                                                                <i class="fas fa-info-circle"></i>
                                                                                MercadoPago não configurado. Como o <strong>Debug</strong> está ativo, clique em "Finalizar Pagamento" para simular a aprovação.
                                                                            </p>
                                                                        </div>
                                                                    `;
                }

                // ─── SUBMIT: Pix bypassa o Brick ────────────────────────────────────
                // O Brick DO intercepta o event 'submit' do formulário.
                // Para Pix, precisamos capturar ANTES do Brick, desmontá-lo e submeter.
                const paymentForm = document.getElementById('paymentForm');
                paymentForm.addEventListener('submit', function (e) {
                    const method = document.getElementById('payment_method_hidden').value;
                    if (method === 'pix') {
                        e.preventDefault();
                        e.stopImmediatePropagation(); // Impede que o Brick processe o submit
                        // Desmontar o Brick para liberar o form
                        if (window.cardPaymentBrickController) {
                            try { window.cardPaymentBrickController.unmount(); } catch (_) { }
                        }
                        // Submeter o formulário sem o Brick
                        paymentForm.submit();
                    }
                    // Se for credit_card, o Brick gerencia via onSubmit callback
                }, true); // 'true' = capture phase (antes do Brick que usa bubble phase)

                // Nota: a sincronização do botão é gerenciada integralmente por syncPaymentMethod().

            </script>
        @endif
    @endpush

    @push('scripts')
    <script>
    (function () {
        // Atualiza total na sidebar quando o usuário troca o período no checkout
        var radios = document.querySelectorAll('.period-radio');
        if (!radios.length) return;

        var subtotalEl = document.getElementById('checkout-subtotal');
        var totalEl    = document.getElementById('checkout-total');

        function fmt(val) {
            return 'R$ ' + parseFloat(val).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                var period = this.value;
                var opts   = document.querySelectorAll('[data-period-opt]');

                opts.forEach(function (opt) {
                    var active = opt.dataset.periodOpt === period;
                    opt.classList.toggle('border-blue-600', active);
                    opt.classList.toggle('bg-blue-50', active);
                    opt.classList.toggle('border-gray-200', !active);
                });

                // Pegar preço do label selecionado
                var selected = document.querySelector('[data-period-opt="' + period + '"]');
                if (!selected) return;
                var price = parseFloat(selected.dataset.price);

                if (subtotalEl) subtotalEl.textContent = fmt(price);
                if (totalEl)    totalEl.textContent    = fmt(price);
            });
        });
    })();
    </script>
    @endpush
@endsection
