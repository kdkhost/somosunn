@php
    $methodCard          = $sumupMethodCard      ?? true;
    $methodPix           = $sumupMethodPix       ?? true;
    $apiKey              = $sumupApiKey          ?? '';
    $orderId             = $order->id            ?? 0;
    $amount              = (float) ($order->total_amount ?? 0);
    $amountFormatted     = number_format($amount, 2, '.', '');
    $successUrl          = route('events.payment.success', $orderId);
    $pendingUrl          = route('events.payment.pending', $orderId);
    $checkoutIdValue     = $checkoutId           ?? '';
    $maxInstallments     = (int) ($sumupMaxInstallments  ?? 12);
    $noInterestUpTo      = (int) ($sumupNoInterestUpTo   ?? 1);
    $installmentTax      = (float) ($sumupInstallmentTax ?? 0);
    $passFeeToClient     = (bool) ($sumupPassFeeToClient ?? false);
    $interestType        = $sumupInterestType ?? \App\Models\Setting::get('sumup_interest_type', 'per_installment');
    $pixExpirationMinutes = (int) ($sumupPixExpirationMinutes ?? \App\Models\Setting::get('sumup_pix_expiration_minutes', 10) ?? 10);

    // Pré-calcular as opções de parcelas para exibição no debug e no JS
    // Dois tipos de cálculo:
    //   'per_installment' = juros aplicados POR PARCELA (juros compostos simplificados)
    //     total = amount * (1 + taxa/100 * n_parcelas_com_juros)
    //   'on_total' = juros aplicados UMA VEZ sobre o total
    //     total = amount * (1 + taxa/100)
    $installmentOptions = [];
    for ($i = 1; $i <= $maxInstallments; $i++) {
        if ($i <= $noInterestUpTo || $installmentTax <= 0 || !$passFeeToClient) {
            // Sem juros ou juros absorvidos pela plataforma
            $total = $amount;
            $perInstallment = $amount / $i;
            $hasInterest = false;
        } else {
            $hasInterest = true;
            $parcelsWithInterest = $i - $noInterestUpTo;

            if ($interestType === 'on_total') {
                // Juros sobre o total: aplica a taxa uma vez sobre o valor total
                $total = $amount * (1 + $installmentTax / 100);
            } else {
                // Juros por parcela: aplica a taxa multiplicada pelo número de parcelas com juros
                $total = $amount * (1 + ($installmentTax / 100) * $parcelsWithInterest);
            }

            $perInstallment = $total / $i;
        }
        $installmentOptions[] = [
            'n'               => $i,
            'per_installment' => round($perInstallment, 2),
            'total'           => round($total, 2),
            'has_interest'    => $hasInterest,
        ];
    }
@endphp

{{-- Debug: Verificar se variáveis estão sendo passadas --}}
@if(config('app.debug'))
<div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-xs">
    <strong>Debug Info:</strong><br>
    Checkout ID: {{ $checkoutIdValue }}<br>
    API Key: {{ !empty($apiKey) ? 'Configurada' : 'NÃO configurada' }}<br>
    Method Card: {{ $methodCard ? 'Sim' : 'Não' }}<br>
    Method PIX: {{ $methodPix ? 'Sim' : 'Não' }}<br>
    Order ID: {{ $orderId }} | Amount: R$ {{ $amountFormatted }}<br>
    Parcelas: até {{ $maxInstallments }}x | Sem juros: até {{ $noInterestUpTo }}x | Juros: {{ $installmentTax }}% | Repasse: {{ $passFeeToClient ? 'Sim' : 'Não' }} | PIX expira: {{ $pixExpirationMinutes }}min
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
    {{-- Widget SumUp --}}
    <div id="sumup-card"></div>

    {{-- Resumo de juros por parcela — exibido abaixo do widget após seleção --}}
    @if($maxInstallments > 1 && $installmentTax > 0)
    <div id="sumup-installment-summary" class="hidden mt-3 px-4 py-3 rounded-xl border text-sm font-medium text-center"></div>
    <div class="mt-2 px-3 py-2 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-500">
        <i class="fas fa-info-circle mr-1 text-blue-400"></i>
        @if($passFeeToClient)
            Parcelas acima de {{ $noInterestUpTo }}x incluem {{ number_format($installmentTax, 2, ',', '.') }}% de juros repassados ao valor total.
        @else
            Parcelas acima de {{ $noInterestUpTo }}x têm {{ number_format($installmentTax, 2, ',', '.') }}% de juros absorvidos pela plataforma.
        @endif
    </div>
    @elseif($maxInstallments > 1)
    <div id="sumup-installment-summary" class="hidden mt-3 px-4 py-3 rounded-xl border text-sm font-medium text-center"></div>
    <div class="mt-2 px-3 py-2 bg-teal-50 rounded-xl border border-teal-200 text-xs text-teal-700">
        <i class="fas fa-check-circle mr-1"></i>
        Parcelamento em até {{ $maxInstallments }}x sem juros.
    </div>
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
                SumUpCard.mount({
                    id: 'sumup-card',
                    checkoutId: CHECKOUT_ID,
                    locale: 'pt-BR',
                    country: 'BR',
                    currency: 'BRL',
                    showInstallments: MAX_INSTALLMENTS > 1,
                    maxInstallments: MAX_INSTALLMENTS,
                    onChangeInstallments: function(installments) {
                        var option = INSTALLMENT_OPTIONS.find(function(o) { return o.n === installments; });
                        if (!option) return;

                        var summaryEl = document.getElementById('sumup-installment-summary');
                        if (!summaryEl) return;

                        var fmt = function(v) {
                            return 'R$ ' + v.toFixed(2).replace('.', ',');
                        };

                        if (option.has_interest && PASS_FEE_TO_CLIENT) {
                            // Juros repassados ao cliente
                            summaryEl.className = 'mt-3 px-4 py-3 rounded-xl border border-amber-200 bg-amber-50 text-sm font-medium text-center text-amber-800';
                            summaryEl.innerHTML =
                                '<i class="fas fa-exclamation-circle mr-1"></i>' +
                                installments + 'x de <strong>' + fmt(option.per_installment) + '</strong>' +
                                ' &nbsp;|&nbsp; Total: <strong>' + fmt(option.total) + '</strong>' +
                                ' <span class="text-amber-600 text-xs">(+' + INSTALLMENT_TAX + '% juros)</span>';
                        } else if (option.has_interest) {
                            // Juros absorvidos pela plataforma
                            summaryEl.className = 'mt-3 px-4 py-3 rounded-xl border border-blue-200 bg-blue-50 text-sm font-medium text-center text-blue-800';
                            summaryEl.innerHTML =
                                '<i class="fas fa-info-circle mr-1"></i>' +
                                installments + 'x de <strong>' + fmt(option.per_installment) + '</strong>' +
                                ' &nbsp;|&nbsp; Total: <strong>' + fmt(option.total) + '</strong>' +
                                ' <span class="text-blue-500 text-xs">(juros absorvidos)</span>';
                        } else {
                            // Sem juros
                            summaryEl.className = 'mt-3 px-4 py-3 rounded-xl border border-teal-200 bg-teal-50 text-sm font-medium text-center text-teal-800';
                            summaryEl.innerHTML =
                                '<i class="fas fa-check-circle mr-1"></i>' +
                                installments + 'x de <strong>' + fmt(option.per_installment) + '</strong>' +
                                ' &nbsp;|&nbsp; Total: <strong>' + fmt(option.total) + '</strong>' +
                                ' <span class="text-teal-600 text-xs">(sem juros)</span>';
                        }
                        summaryEl.classList.remove('hidden');
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
        var maxAttempts = 60; // 5 minutos

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
