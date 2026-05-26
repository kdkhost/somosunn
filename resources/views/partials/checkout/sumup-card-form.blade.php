@php
    $methodCard          = $sumupMethodCard      ?? true;
    $methodPix           = $sumupMethodPix       ?? true;
    $apiKey              = $sumupApiKey          ?? '';
    $orderId             = $order->id            ?? 0;

    // Valor base do pedido (antes de taxas/juros) = valor original do pedido.
    $baseAmount          = (float) (data_get($order->metadata, 'sumup_base_amount') ?? ($order->total_amount ?? 0));
    // Valor atualmente cobrado do SumUp (reflete o checkout atual - pode ter sido criado como a vista).
    $amount              = (float) ($order->total_amount ?? 0);
    $amountFormatted     = number_format($amount, 2, '.', '');
    $context             = (string) data_get($order->metadata, 'context', '');
    $publicToken         = (string) data_get($order->metadata, 'public_token', '');
    $eventId             = (int) data_get($order->metadata, 'event_id', 0);
    if ($context === 'event_exhibitor' && $eventId > 0) {
        $successUrl      = route('events.exhibitor.success', ['event' => $eventId, 'order' => $orderId, 'token' => $publicToken]);
        $pendingUrl      = route('events.exhibitor.pending', ['event' => $eventId, 'order' => $orderId, 'token' => $publicToken]);
    } elseif ($context === 'event') {
        $successUrl      = route('events.payment.success', ['order' => $orderId, 'token' => $publicToken]);
        $pendingUrl      = route('events.payment.pending', ['order' => $orderId, 'token' => $publicToken]);
    } else {
        $successUrl      = route('checkout.success', $orderId);
        $pendingUrl      = route('checkout.pending', $orderId);
    }
    $checkoutIdValue     = $checkoutId           ?? '';
    $maxInstallments     = (int) ($sumupMaxInstallments  ?? 12);
    $noInterestUpTo      = (int) ($sumupNoInterestUpTo   ?? $sumupInstallmentsNoInterest ?? 1);
    $installmentTax      = (float) ($sumupInstallmentTax ?? 0);
    $passFeeToClient     = (bool) ($sumupPassFeeToClient ?? false);
    $interestType        = $sumupInterestType ?? \App\Models\Setting::get('sumup_interest_type', 'per_installment');
    $pixExpirationMinutes = (int) ($sumupPixExpirationMinutes ?? \App\Models\Setting::get('sumup_pix_expiration_minutes', 10) ?? 10);

    // Taxa do gateway SumUp (percentual + fixa) - SO aplicada quando ha parcelamento acima do limite sem juros
    $sumupFeePercentage  = (float) \App\Models\Setting::get('sumup_fee_percentage', 2.75);
    $sumupFeeFixed       = (float) \App\Models\Setting::get('sumup_fee_fixed', 0);

    // Calcular TODAS as opcoes de parcelas a partir do valor base.
    // Regra: parcelas ate $noInterestUpTo => valor base (sem taxa nem juros).
    //        parcelas acima => base + taxa gateway (se pass_fee=1) + juros (se installment_tax>0).
    $installmentOptions = [];
    for ($i = 1; $i <= $maxInstallments; $i++) {
        $chargeAmount = $baseAmount;
        $gatewayFee   = 0.0;
        $interestFee  = 0.0;
        $hasExtras    = false;

        if ($i > $noInterestUpTo) {
            // Taxa de gateway
            if ($passFeeToClient && ($sumupFeePercentage > 0 || $sumupFeeFixed > 0)) {
                $withFee      = round($baseAmount * (1 + $sumupFeePercentage / 100) + $sumupFeeFixed, 2);
                $gatewayFee   = round($withFee - $baseAmount, 2);
                $chargeAmount = $withFee;
                $hasExtras    = true;
            }
            // Juros de parcelamento - sobre o valor apos gateway
            if ($installmentTax > 0) {
                $parcelsWithInterest = $i - $noInterestUpTo;
                $before = $chargeAmount;
                if ($interestType === 'on_total') {
                    $chargeAmount = round($chargeAmount * (1 + $installmentTax / 100), 2);
                } else {
                    $chargeAmount = round($chargeAmount * (1 + ($installmentTax / 100) * $parcelsWithInterest), 2);
                }
                $interestFee = round($chargeAmount - $before, 2);
                $hasExtras   = true;
            }
        }

        $installmentOptions[] = [
            'n'               => $i,
            'per_installment' => round($chargeAmount / $i, 2),
            'total'           => round($chargeAmount, 2),
            'gateway_fee'     => $gatewayFee,
            'interest_fee'    => $interestFee,
            'has_interest'    => $hasExtras,
        ];
    }
@endphp

{{-- Aviso de taxa de parcelamento (apenas informativo - valor real exibido no seletor) --}}
@if($passFeeToClient && $maxInstallments > $noInterestUpTo && ($sumupFeePercentage > 0 || $installmentTax > 0))
<div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 flex items-start gap-2">
    <i class="fas fa-info-circle mt-0.5"></i>
    <div>
        <strong>A vista:</strong> sem taxas adicionais.
        <strong class="ml-2">Parcelado (acima de {{ $noInterestUpTo }}x):</strong>
        @if($sumupFeePercentage > 0)taxa de processamento {{ number_format($sumupFeePercentage, 2, ',', '.') }}%@endif
        @if($sumupFeePercentage > 0 && $installmentTax > 0) + @endif
        @if($installmentTax > 0)juros de {{ number_format($installmentTax, 2, ',', '.') }}%@endif.
    </div>
</div>
@endif

{{-- Debug: Verificar se variáveis estão sendo passadas --}}
@if(config('app.debug'))
<div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-xs">
    <strong>Debug Info:</strong><br>
    Checkout ID: {{ $checkoutIdValue }}<br>
    API Key: {{ !empty($apiKey) ? 'Configurada' : 'NÃO configurada' }}<br>
    Method Card: {{ $methodCard ? 'Sim' : 'Não' }}<br>
    Method PIX: {{ $methodPix ? 'Sim' : 'Não' }}<br>
    Order ID: {{ $orderId }} | Base: R$ {{ number_format($baseAmount, 2, ',', '.') }} | Cobrado: R$ {{ $amountFormatted }}<br>
    Parcelas: até {{ $maxInstallments }}x | Sem juros: até {{ $noInterestUpTo }}x | Juros: {{ $installmentTax }}% | Repasse taxa gateway: {{ $passFeeToClient ? 'Sim (' . number_format($sumupFeePercentage, 2, ',', '.') . '%)' : 'Não' }} | PIX expira: {{ $pixExpirationMinutes }}min
</div>
@endif

{{-- Aviso quando nenhum método está habilitado --}}
@if(!$methodCard && !$methodPix)
<div class="text-center py-10">
    <i class="fas fa-exclamation-circle text-4xl mb-4 text-amber-400"></i>
    <p class="font-semibold text-slate-700 mb-1">Nenhum método de pagamento disponível</p>
    <p class="text-sm text-slate-500">Entre em contato com o organizador do evento.</p>
</div>
@endif

{{-- Seletor de método de pagamento --}}
@if($methodCard && $methodPix)
<div class="mb-6">
    <p class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-3">Forma de Pagamento</p>
    <div class="grid grid-cols-2 gap-3">
        <button type="button" id="btn-method-card"
            onclick="selectSumupMethod('card')"
            class="sumup-method-btn flex items-center justify-center gap-2 p-3 rounded-xl border-2 border-blue-500 bg-blue-50 text-blue-700 font-semibold transition-all">
            <i class="fas fa-credit-card"></i> Cartão
        </button>
        <button type="button" id="btn-method-pix"
            onclick="selectSumupMethod('pix')"
            class="sumup-method-btn flex items-center justify-center gap-2 p-3 rounded-xl border-2 border-slate-200 bg-white text-slate-600 font-semibold transition-all hover:border-teal-400 hover:bg-teal-50 hover:text-teal-700">
            <i class="fa-brands fa-pix"></i> PIX
        </button>
    </div>
</div>
@elseif($methodPix && !$methodCard)
{{-- Só PIX: mostrar título --}}
<div class="mb-4">
    <p class="text-sm font-semibold text-slate-600 uppercase tracking-wider flex items-center gap-2">
        <i class="fa-brands fa-pix text-teal-500"></i> Pagamento via PIX
    </p>
</div>
@elseif($methodCard && !$methodPix)
{{-- Só Cartão: mostrar título --}}
<div class="mb-4">
    <p class="text-sm font-semibold text-slate-600 uppercase tracking-wider flex items-center gap-2">
        <i class="fas fa-credit-card text-blue-500"></i> Pagamento com Cartão
    </p>
</div>
@endif

{{-- Formulário de Cartão (SumUp Card Widget) --}}
@if($methodCard)
<div id="sumup-card-section">
    {{-- Seletor de parcelas customizado (sempre visível quando maxInstallments > 1) --}}
    @if($maxInstallments > 1)
    <div class="mb-4">
        <label class="block text-sm font-semibold text-slate-600 mb-2">Parcelas</label>
        <select id="sumup-custom-installments"
            onchange="onCustomInstallmentChange(this.value)"
            class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-slate-800 font-medium text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all appearance-none cursor-pointer"
            style="background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2364748b%22 stroke-width=%222%22%3E%3Cpath d=%22M6 9l6 6 6-6%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 20px;">
            @foreach($installmentOptions as $opt)
                <option value="{{ $opt['n'] }}">
                    {{ $opt['n'] }}x de R$ {{ number_format($opt['per_installment'], 2, ',', '.') }} = R$ {{ number_format($opt['total'], 2, ',', '.') }} {{ $opt['has_interest'] ? '(com taxas)' : 'sem juros' }}
                </option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Widget SumUp (seletor de parcelas nativo sempre oculto — usamos o nosso) --}}
    <div id="sumup-card"></div>

    {{-- Feedback de recriacao de checkout quando o usuario muda parcelas --}}
    <div id="sumup-recreating" class="hidden mt-3 py-2 px-3 bg-slate-100 rounded-lg text-xs text-slate-600 flex items-center justify-center gap-2">
        <div class="animate-spin rounded-full h-3 w-3 border-t-2 border-b-2 border-slate-500"></div>
        <span>Atualizando valor...</span>
    </div>

    {{-- Botão customizado de pagamento com valor atualizado --}}
    @if($maxInstallments > 1 && ($installmentTax > 0 || ($passFeeToClient && $sumupFeePercentage > 0)))
    <button type="button" id="sumup-custom-pay-btn" onclick="submitSumupCard()"
        class="w-full mt-4 py-4 px-6 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-base transition-all active:scale-[0.98] flex items-center justify-center gap-2">
        <i class="fas fa-lock text-sm"></i>
        <span id="sumup-pay-btn-text">Pagar R$ {{ number_format($installmentOptions[0]['total'] ?? $amount, 2, ',', '.') }}</span>
    </button>
    @endif
</div>
@endif

{{-- Formulário de PIX --}}
@if($methodPix)
<div id="sumup-pix-section" class="{{ $methodCard ? 'hidden' : '' }}">
    <div id="sumup-pix-loading" class="text-center py-8">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-teal-500 mx-auto mb-4"></div>
        <p class="text-slate-600">Gerando QR Code PIX...</p>
    </div>
    <div id="sumup-pix-content" class="hidden text-center">
        <div class="bg-teal-50 border border-teal-200 rounded-xl p-4 mb-4">
            <p class="text-teal-800 font-semibold text-sm">Escaneie o QR Code ou copie o código PIX</p>
        </div>
        <div id="sumup-pix-qr" class="flex justify-center mb-4"></div>
        <div class="bg-slate-100 rounded-xl p-3 flex items-center gap-2 mb-4">
            <input type="text" id="sumup-pix-code" readonly
                class="bg-transparent border-none text-xs flex-1 outline-none text-slate-700 font-mono">
            <button type="button" onclick="copySumupPix()"
                class="text-teal-600 font-bold text-sm px-2 hover:text-teal-800 transition-colors">
                <i class="fas fa-copy mr-1"></i>Copiar
            </button>
        </div>
        <div id="sumup-pix-timer" class="hidden mb-4 py-2 px-4 bg-amber-50 rounded-lg border border-amber-200 text-amber-800 font-bold text-lg flex items-center justify-center gap-2">
            <i class="fas fa-clock"></i> <span id="sumup-pix-timer-value">00:00</span>
        </div>
        <p class="text-xs text-slate-500">O acesso será liberado automaticamente após a confirmação do pagamento.</p>
    </div>
    <div id="sumup-pix-error" class="hidden text-center py-6 text-red-600">
        <i class="fas fa-exclamation-triangle text-3xl mb-3"></i>
        <p id="sumup-pix-error-msg">Erro ao gerar PIX. Tente novamente.</p>
        <button type="button" onclick="loadSumupPix()"
            class="mt-3 px-4 py-2 bg-teal-600 text-white rounded-lg text-sm font-semibold hover:bg-teal-700 transition-colors">
            Tentar novamente
        </button>
    </div>
</div>
@endif

<script src="https://gateway.sumup.com/gateway/ecom/card/v2/sdk.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
(function() {
    const CHECKOUT_ID = '{{ $checkoutIdValue }}';
    const API_KEY     = '{{ $apiKey }}';
    const ORDER_ID    = {{ $orderId }};
    const SUCCESS_URL = '{{ $successUrl }}';
    const PENDING_URL = '{{ $pendingUrl }}';
    const METHOD_CARD = {{ $methodCard ? 'true' : 'false' }};
    const METHOD_PIX  = {{ $methodPix  ? 'true' : 'false' }};
    const AMOUNT      = {{ $amount }};
    const MAX_INSTALLMENTS   = {{ $maxInstallments }};
    const NO_INTEREST_UP_TO  = {{ $noInterestUpTo }};
    const INSTALLMENT_TAX    = {{ $installmentTax }};
    const PASS_FEE_TO_CLIENT = {{ $passFeeToClient ? 'true' : 'false' }};
    const PIX_EXPIRATION_MINUTES = {{ $pixExpirationMinutes }};

    // Opções de parcelas pré-calculadas pelo PHP
    const INSTALLMENT_OPTIONS = @json($installmentOptions);

    console.log('=== SumUp Form Initialization ===');
    console.log('Checkout ID:', CHECKOUT_ID);
    console.log('API Key configured:', API_KEY ? 'Yes' : 'No');
    console.log('Method Card:', METHOD_CARD);
    console.log('Method PIX:', METHOD_PIX);
    console.log('Order ID:', ORDER_ID);

    let pixTimerInterval = null;
    let pixLoaded = false;

    // ── Seletor customizado de parcelas ──────────────────────────────────────
    var sumupCardInstance = null;
    var currentCheckoutId = CHECKOUT_ID;
    var recreatingCheckout = false;
    var installmentDebounceTimer = null;

    function remountSumupCard(newCheckoutId, installments) {
        currentCheckoutId = newCheckoutId;
        var cardContainer = document.getElementById('sumup-card');
        if (!cardContainer || typeof SumUpCard === 'undefined') return;

        // Destruir widget anterior
        try {
            if (sumupCardInstance && typeof sumupCardInstance.unmount === 'function') {
                sumupCardInstance.unmount();
            }
        } catch (e) { /* ignore */ }
        cardContainer.innerHTML = '';

        try {
            sumupCardInstance = SumUpCard.mount({
                id: 'sumup-card',
                checkoutId: newCheckoutId,
                locale: 'pt-BR',
                country: 'BR',
                currency: 'BRL',
                showInstallments: false,
                installments: installments,
                showSubmitButton: !(MAX_INSTALLMENTS > 1 && (INSTALLMENT_TAX > 0 || (PASS_FEE_TO_CLIENT))),
                maxInstallments: MAX_INSTALLMENTS,
                onResponse: function(type, body) {
                    if (type === 'success') {
                        if (typeof toastr !== 'undefined') toastr.success('Pagamento aprovado!');
                        setTimeout(function(){ window.location.href = SUCCESS_URL; }, 1500);
                    } else if (type === 'error') {
                        if (typeof toastr !== 'undefined') toastr.error((body && body.message) || 'Erro ao processar pagamento.');
                    } else if (type === 'pending') {
                        if (typeof toastr !== 'undefined') toastr.info('Pagamento pendente de confirmacao.');
                        setTimeout(function(){ window.location.href = PENDING_URL; }, 1500);
                    }
                }
            });
        } catch (e) {
            console.error('Falha ao remontar widget SumUp:', e);
        }
    }

    function recreateSumupCheckout(installments) {
        var recreatingEl = document.getElementById('sumup-recreating');
        var payBtn = document.getElementById('sumup-custom-pay-btn');
        if (recreatingEl) recreatingEl.classList.remove('hidden');
        if (payBtn) payBtn.setAttribute('disabled', 'disabled');
        recreatingCheckout = true;

        return fetch('{{ route("checkout.sumup.recreate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ order_id: ORDER_ID, installments: installments })
        })
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (recreatingEl) recreatingEl.classList.add('hidden');
            if (payBtn) payBtn.removeAttribute('disabled');
            recreatingCheckout = false;

            if (!data.success) {
                if (typeof toastr !== 'undefined') toastr.error(data.error || 'Falha ao atualizar valor.');
                return;
            }

            // Atualizar botao
            var btnText = document.getElementById('sumup-pay-btn-text');
            if (btnText) {
                btnText.textContent = 'Pagar R$ ' + Number(data.charge_amount).toFixed(2).replace('.', ',');
            }

            // Remontar o widget SumUp com o novo checkout_id (valor atualizado)
            if (data.checkout_id && data.checkout_id !== currentCheckoutId) {
                remountSumupCard(data.checkout_id, installments);
            }
        })
        .catch(function(err){
            if (recreatingEl) recreatingEl.classList.add('hidden');
            if (payBtn) payBtn.removeAttribute('disabled');
            recreatingCheckout = false;
            console.error('Recreate checkout error:', err);
            if (typeof toastr !== 'undefined') toastr.error('Erro ao atualizar valor.');
        });
    }

    window.onCustomInstallmentChange = function(value) {
        var n = parseInt(value, 10);
        console.log('Custom installment selected:', n);

        // Encontrar o valor total da parcela selecionada
        var option = INSTALLMENT_OPTIONS.find(function(o) { return o.n === n; });
        var totalWithInterest = option ? option.total : AMOUNT;

        // Atualizar imediatamente o texto do botao
        var btnText = document.getElementById('sumup-pay-btn-text');
        if (btnText) {
            btnText.textContent = 'Pagar R$ ' + totalWithInterest.toFixed(2).replace('.', ',');
        }

        // Atualizar o resumo lateral (coluna ao lado) com o valor atualizado
        var formattedTotal = 'R$ ' + totalWithInterest.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        var sidebarTotal = document.getElementById('checkout-total') || document.querySelector('[data-checkout-total]') || document.querySelector('.checkout-total-value');
        if (sidebarTotal) {
            sidebarTotal.textContent = formattedTotal;
        }
        // Tambem tenta atualizar qualquer elemento com classe ou data-attribute de total
        document.querySelectorAll('[data-dynamic-total], .dynamic-total, .order-total-value').forEach(function(el) {
            el.textContent = formattedTotal;
        });
        // Atualiza o preco do item no resumo (card escuro)
        var itemPriceEl = document.getElementById('checkout-item-price');
        if (itemPriceEl) {
            itemPriceEl.textContent = formattedTotal;
        }
        // Atualiza subtotal se existir
        var subtotalEl = document.getElementById('checkout-subtotal');
        if (subtotalEl && n > 1 && option && option.has_interest) {
            subtotalEl.textContent = formattedTotal;
        }

        // Debounce para recriar o checkout no backend (so se o valor mudar)
        if (installmentDebounceTimer) clearTimeout(installmentDebounceTimer);
        installmentDebounceTimer = setTimeout(function(){
            recreateSumupCheckout(n);
        }, 400);
    };

    // Submeter o formulário SumUp via botão customizado
    window.submitSumupCard = function() {
        if (recreatingCheckout) {
            if (typeof toastr !== 'undefined') toastr.info('Aguarde - atualizando valor...');
            return;
        }
        if (sumupCardInstance && typeof sumupCardInstance.submit === 'function') {
            sumupCardInstance.submit();
        }
    };

    // ── Seletor de método ────────────────────────────────────────────────────
    window.selectSumupMethod = function(method) {
        const cardSection = document.getElementById('sumup-card-section');
        const pixSection  = document.getElementById('sumup-pix-section');
        const btnCard     = document.getElementById('btn-method-card');
        const btnPix      = document.getElementById('btn-method-pix');

        if (method === 'card') {
            cardSection && cardSection.classList.remove('hidden');
            pixSection  && pixSection.classList.add('hidden');
            btnCard && btnCard.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');
            btnCard && btnCard.classList.remove('border-slate-200', 'bg-white', 'text-slate-600');
            btnPix  && btnPix.classList.remove('border-teal-400', 'bg-teal-50', 'text-teal-700');
            btnPix  && btnPix.classList.add('border-slate-200', 'bg-white', 'text-slate-600');
        } else {
            cardSection && cardSection.classList.add('hidden');
            pixSection  && pixSection.classList.remove('hidden');
            btnPix  && btnPix.classList.add('border-teal-400', 'bg-teal-50', 'text-teal-700');
            btnPix  && btnPix.classList.remove('border-slate-200', 'bg-white', 'text-slate-600');
            btnCard && btnCard.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
            btnCard && btnCard.classList.add('border-slate-200', 'bg-white', 'text-slate-600');

            if (!pixLoaded) {
                loadSumupPix();
            }
        }
    };

    // ── Cartão ───────────────────────────────────────────────────────────────
    if (METHOD_CARD) {
        console.log('Initializing SumUp Card Widget...');
        const cardContainer = document.getElementById('sumup-card');

        if (!cardContainer) {
            console.error('Container #sumup-card not found in DOM');
        } else if (!CHECKOUT_ID) {
            console.error('Checkout ID is empty');
            cardContainer.innerHTML =
                '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-4"></i><p>Erro: ID do checkout não encontrado.</p></div>';
        } else if (typeof SumUpCard === 'undefined') {
            console.error('SumUpCard SDK not loaded');
            cardContainer.innerHTML =
                '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-4"></i><p>Erro: SDK do SumUp não carregou.</p></div>';
        } else {
            console.log('SumUpCard SDK loaded, mounting widget...');
            try {
                sumupCardInstance = SumUpCard.mount({
                    id: 'sumup-card',
                    checkoutId: CHECKOUT_ID,
                    locale: 'pt-BR',
                    country: 'BR',
                    currency: 'BRL',
                    amount: AMOUNT.toFixed(2),
                    showInstallments: false,
                    installments: 1,
                    showSubmitButton: !(MAX_INSTALLMENTS > 1 && (INSTALLMENT_TAX > 0 || PASS_FEE_TO_CLIENT)),
                    maxInstallments: MAX_INSTALLMENTS,
                    onChangeInstallments: function(installments) {
                        console.log('SumUp native installment changed:', installments);
                    },
                    onResponse: function(type, body) {
                        console.log('SumUp Card Response:', type, body);
                        if (type === 'success') {
                            if (typeof toastr !== 'undefined') toastr.success('Pagamento aprovado!');
                            setTimeout(() => window.location.href = SUCCESS_URL, 1500);
                        } else if (type === 'error') {
                            if (typeof toastr !== 'undefined') toastr.error((body && body.message) || 'Erro ao processar pagamento.');
                        } else if (type === 'pending') {
                            if (typeof toastr !== 'undefined') toastr.info('Pagamento pendente de confirmação.');
                            setTimeout(() => window.location.href = PENDING_URL, 1500);
                        }
                    },
                    onLoad: function() {
                        console.log('SumUp Card Widget loaded successfully');
                    },
                    onError: function(error) {
                        console.error('SumUp Widget Error:', error);
                        cardContainer.innerHTML =
                            '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-4"></i><p>Erro ao carregar formulário. Tente novamente.</p></div>';
                    }
                });
                console.log('SumUpCard.mount() called successfully');
            } catch (e) {
                console.error('SumUp init error:', e);
                cardContainer.innerHTML =
                    '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-4"></i><p>Erro ao inicializar SumUp: ' + e.message + '</p></div>';
            }
        }
    }

    // ── PIX ──────────────────────────────────────────────────────────────────
    window.loadSumupPix = function() {
        const loading = document.getElementById('sumup-pix-loading');
        const content = document.getElementById('sumup-pix-content');
        const errDiv  = document.getElementById('sumup-pix-error');
        const errMsg  = document.getElementById('sumup-pix-error-msg');

        loading && loading.classList.remove('hidden');
        content && content.classList.add('hidden');
        errDiv  && errDiv.classList.add('hidden');

        fetch('{{ route("checkout.sumup.pix") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ order_id: ORDER_ID })
        })
        .then(function(r) {
            return r.json().then(function(data) {
                return { ok: r.ok, status: r.status, data: data };
            });
        })
        .then(function(res) {
            loading && loading.classList.add('hidden');

            if (!res.ok || !res.data.success) {
                throw new Error(res.data.error || 'Erro ao gerar PIX (HTTP ' + res.status + ')');
            }

            const data = res.data;
            if (data.status === 'PAID' || data.status === 'SUCCESSFUL') {
                if (typeof toastr !== 'undefined') toastr.success('Pagamento confirmado!');
                setTimeout(function() { window.location.href = SUCCESS_URL; }, 800);
                return;
            }

            console.log('PIX data received:', {
                checkout_id: data.checkout_id,
                has_qr_base64: !!data.qr_code_base64,
                has_qr_code: !!data.qr_code,
                has_copy_paste: !!data.copy_paste,
            });

            pixLoaded = true;

            // QR Code — preferir base64, depois gerar via qrserver
            const qrContainer = document.getElementById('sumup-pix-qr');
            if (data.qr_code_base64) {
                qrContainer.innerHTML = '<img src="data:image/png;base64,' + data.qr_code_base64 + '" alt="PIX QR Code" class="w-48 h-48 mx-auto rounded-xl border border-slate-200">';
            } else if (data.copy_paste || data.qr_code) {
                const pixData = encodeURIComponent(data.copy_paste || data.qr_code);
                qrContainer.innerHTML = '<img src="https://api.qrserver.com/v1/create-qr-code/?size=192x192&data=' + pixData + '" alt="PIX QR Code" class="w-48 h-48 mx-auto rounded-xl border border-slate-200">';
            } else {
                qrContainer.innerHTML = '<div class="w-48 h-48 mx-auto rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center text-slate-400 text-xs">QR Code indisponível</div>';
            }

            // Código copia e cola
            const codeInput = document.getElementById('sumup-pix-code');
            if (codeInput) {
                codeInput.value = data.copy_paste || data.qr_code || '';
            }

            // Timer
            if (data.expires_at) {
                startPixTimer(data.expires_at);
            } else {
                // Fallback: usar o valor configurado de expiração em minutos
                var fallbackExpiry = new Date(Date.now() + PIX_EXPIRATION_MINUTES * 60 * 1000).toISOString();
                startPixTimer(fallbackExpiry);
            }

            content && content.classList.remove('hidden');

            // Polling de status
            startPixPolling(data.checkout_id || CHECKOUT_ID);
        })
        .catch(function(err) {
            console.error('PIX error:', err);
            loading && loading.classList.add('hidden');
            errDiv  && errDiv.classList.remove('hidden');
            if (errMsg) errMsg.textContent = err.message || 'Erro ao gerar PIX. Tente novamente.';
        });
    };

    window.copySumupPix = function() {
        const el = document.getElementById('sumup-pix-code');
        if (!el || !el.value) return;
        el.select();
        try {
            document.execCommand('copy');
        } catch (e) {
            navigator.clipboard && navigator.clipboard.writeText(el.value);
        }
        if (typeof toastr !== 'undefined') toastr.success('Código PIX copiado!');
    };

    function startPixTimer(expiresAtIso) {
        const expiresAt  = new Date(expiresAtIso).getTime();
        const timerDiv   = document.getElementById('sumup-pix-timer');
        const timerValue = document.getElementById('sumup-pix-timer-value');

        if (!timerDiv || !timerValue) return;
        timerDiv.classList.remove('hidden');

        if (pixTimerInterval) clearInterval(pixTimerInterval);

        var update = function() {
            var diff = expiresAt - Date.now();
            if (diff <= 0) {
                clearInterval(pixTimerInterval);
                timerValue.textContent = 'Expirado';
                timerDiv.classList.replace('bg-amber-50', 'bg-red-50');
                timerDiv.classList.replace('border-amber-200', 'border-red-200');
                timerDiv.classList.replace('text-amber-800', 'text-red-800');
                return;
            }
            var m = Math.floor(diff / 60000);
            var s = Math.floor((diff % 60000) / 1000);
            timerValue.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        };
        update();
        pixTimerInterval = setInterval(update, 1000);
    }

    function startPixPolling(checkoutId) {
        var attempts    = 0;
        var maxAttempts = Math.ceil(PIX_EXPIRATION_MINUTES * 60 / 5); // poll every 5s until expiration

        var poll = setInterval(function() {
            attempts++;
            if (attempts > maxAttempts) {
                clearInterval(poll);
                return;
            }

            fetch('{{ route("checkout.sumup.status") }}?checkout_id=' + checkoutId + '&order_id=' + ORDER_ID, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status === 'PAID' || data.status === 'SUCCESSFUL') {
                    clearInterval(poll);
                    if (typeof toastr !== 'undefined') toastr.success('Pagamento PIX confirmado!');
                    setTimeout(function() { window.location.href = SUCCESS_URL; }, 1500);
                } else if (data.status === 'FAILED') {
                    clearInterval(poll);
                    if (typeof toastr !== 'undefined') toastr.error('Pagamento PIX falhou. Tente novamente.');
                }
            })
            .catch(function() {}); // silenciar erros de polling
        }, 5000);
    }

    // Se só tem PIX, carregar automaticamente
    if (METHOD_PIX && !METHOD_CARD) {
        loadSumupPix();
    }
})();
});
</script>

<style>
    /*
     * O widget SumUp Card renderiza dentro de um iframe.
     * Elementos com data-sumup-id ficam no DOM do host e podem ser estilizados.
     * O iframe interno não pode ser estilizado por CSS externo.
     */

    /* Container externo: altura automática */
    #sumup-card {
        min-height: 0 !important;
        height: auto !important;
    }

    /* O iframe gerado pelo SDK deve ocupar 100% da largura */
    #sumup-card > iframe,
    #sumup-card iframe {
        width: 100% !important;
        min-width: 100% !important;
        border: none !important;
        display: block !important;
    }

    /* Estilizar o container principal do widget via data-sumup-id */
    [data-sumup-id="widget__container"] {
        font-family: inherit !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
    }

    /* Seção PIX */
    #sumup-pix-section { min-height: 200px; }
</style>
