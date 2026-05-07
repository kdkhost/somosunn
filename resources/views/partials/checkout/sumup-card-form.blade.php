@php
    $methodCard = $sumupMethodCard ?? true;
    $methodPix  = $sumupMethodPix  ?? true;
    $apiKey     = $sumupApiKey     ?? '';
    $orderId    = $order->id       ?? 0;
    $amount     = number_format($order->total_amount ?? 0, 2, '.', '');
    $successUrl = route('events.payment.success', $orderId);
    $pendingUrl = route('events.payment.pending', $orderId);
@endphp

{{-- Seletor de método de pagamento --}}
@if($methodCard && $methodPix)
<div class="mb-6">
    <p class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-3">Forma de Pagamento</p>
    <div class="grid grid-cols-2 gap-3">
        <button type="button" id="btn-method-card"
            onclick="selectSumupMethod('card')"
            class="sumup-method-btn active flex items-center justify-center gap-2 p-3 rounded-xl border-2 border-blue-500 bg-blue-50 text-blue-700 font-semibold transition-all">
            <i class="fas fa-credit-card"></i> Cartão
        </button>
        <button type="button" id="btn-method-pix"
            onclick="selectSumupMethod('pix')"
            class="sumup-method-btn flex items-center justify-center gap-2 p-3 rounded-xl border-2 border-slate-200 bg-white text-slate-600 font-semibold transition-all hover:border-teal-400 hover:bg-teal-50 hover:text-teal-700">
            <i class="fa-brands fa-pix"></i> PIX
        </button>
    </div>
</div>
@endif

{{-- Formulário de Cartão (SumUp Card Widget) --}}
@if($methodCard)
<div id="sumup-card-section">
    <div id="sumup-card"></div>
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
    const CHECKOUT_ID  = '{{ $checkoutId ?? '' }}';
    const API_KEY      = '{{ $apiKey }}';
    const ORDER_ID     = {{ $orderId }};
    const SUCCESS_URL  = '{{ $successUrl }}';
    const PENDING_URL  = '{{ $pendingUrl }}';
    const METHOD_CARD  = {{ $methodCard ? 'true' : 'false' }};
    const METHOD_PIX   = {{ $methodPix  ? 'true' : 'false' }};

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
        if (!CHECKOUT_ID) {
            document.getElementById('sumup-card').innerHTML =
                '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-4"></i><p>Erro: ID do checkout não encontrado.</p></div>';
        } else if (typeof SumUpCard === 'undefined') {
            document.getElementById('sumup-card').innerHTML =
                '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-4"></i><p>Erro: SDK do SumUp não carregou.</p></div>';
        } else {
            try {
                SumUpCard.mount({
                    checkoutId: CHECKOUT_ID,
                    onResponse: function(type, body) {
                        console.log('SumUp Card Response:', type, body);
                        if (type === 'success') {
                            if (typeof toastr !== 'undefined') toastr.success('Pagamento aprovado!');
                            setTimeout(() => window.location.href = SUCCESS_URL, 1500);
                        } else if (type === 'error') {
                            if (typeof toastr !== 'undefined') toastr.error(body.message || 'Erro ao processar pagamento.');
                        } else if (type === 'pending') {
                            if (typeof toastr !== 'undefined') toastr.info('Pagamento pendente de confirmação.');
                            setTimeout(() => window.location.href = PENDING_URL, 1500);
                        }
                    },
                    onLoad: function() { console.log('SumUp Card Widget loaded'); },
                    onError: function(error) {
                        console.error('SumUp Widget Error:', error);
                        document.getElementById('sumup-card').innerHTML =
                            '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-4"></i><p>Erro ao carregar formulário. Tente novamente.</p></div>';
                    }
                });
            } catch (e) {
                console.error('SumUp init error:', e);
                document.getElementById('sumup-card').innerHTML =
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
        .then(r => r.json())
        .then(data => {
            loading && loading.classList.add('hidden');

            if (!data.success) {
                throw new Error(data.error || 'Erro ao gerar PIX');
            }

            pixLoaded = true;

            // QR Code
            const qrContainer = document.getElementById('sumup-pix-qr');
            if (data.qr_code_base64) {
                qrContainer.innerHTML = `<img src="data:image/png;base64,${data.qr_code_base64}" alt="PIX QR Code" class="w-48 h-48 mx-auto rounded-xl border border-slate-200">`;
            } else if (data.qr_code) {
                // Gerar QR Code via API pública se não vier base64
                qrContainer.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=192x192&data=${encodeURIComponent(data.qr_code)}" alt="PIX QR Code" class="w-48 h-48 mx-auto rounded-xl border border-slate-200">`;
            }

            // Código copia e cola
            const codeInput = document.getElementById('sumup-pix-code');
            if (codeInput) codeInput.value = data.copy_paste || data.qr_code || '';

            // Timer
            if (data.expires_at) {
                startPixTimer(data.expires_at);
            }

            content && content.classList.remove('hidden');

            // Polling de status
            startPixPolling(data.checkout_id || CHECKOUT_ID);
        })
        .catch(err => {
            console.error('PIX error:', err);
            loading && loading.classList.add('hidden');
            errDiv  && errDiv.classList.remove('hidden');
            if (errMsg) errMsg.textContent = err.message || 'Erro ao gerar PIX. Tente novamente.';
        });
    };

    window.copySumupPix = function() {
        const el = document.getElementById('sumup-pix-code');
        if (!el) return;
        el.select();
        document.execCommand('copy');
        if (typeof toastr !== 'undefined') toastr.success('Código PIX copiado!');
    };

    function startPixTimer(expiresAtIso) {
        const expiresAt   = new Date(expiresAtIso).getTime();
        const timerDiv    = document.getElementById('sumup-pix-timer');
        const timerValue  = document.getElementById('sumup-pix-timer-value');

        if (!timerDiv || !timerValue) return;
        timerDiv.classList.remove('hidden');

        if (pixTimerInterval) clearInterval(pixTimerInterval);

        const update = () => {
            const diff = expiresAt - Date.now();
            if (diff <= 0) {
                clearInterval(pixTimerInterval);
                timerValue.textContent = 'Expirado';
                timerDiv.classList.replace('bg-amber-50', 'bg-red-50');
                timerDiv.classList.replace('border-amber-200', 'border-red-200');
                timerDiv.classList.replace('text-amber-800', 'text-red-800');
                return;
            }
            const m = Math.floor(diff / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            timerValue.textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        };
        update();
        pixTimerInterval = setInterval(update, 1000);
    }

    function startPixPolling(checkoutId) {
        let attempts = 0;
        const maxAttempts = 60; // 5 minutos (5s * 60)

        const poll = setInterval(() => {
            attempts++;
            if (attempts > maxAttempts) {
                clearInterval(poll);
                return;
            }

            fetch('{{ route("checkout.sumup.status") }}?checkout_id=' + checkoutId + '&order_id=' + ORDER_ID, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'PAID' || data.status === 'SUCCESSFUL') {
                    clearInterval(poll);
                    if (typeof toastr !== 'undefined') toastr.success('Pagamento PIX confirmado!');
                    setTimeout(() => window.location.href = SUCCESS_URL, 1500);
                } else if (data.status === 'FAILED') {
                    clearInterval(poll);
                    if (typeof toastr !== 'undefined') toastr.error('Pagamento PIX falhou. Tente novamente.');
                }
            })
            .catch(() => {}); // Silenciar erros de polling
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
    #sumup-card { min-height: 400px; }
    #sumup-card iframe { width: 100% !important; border: none !important; min-height: 400px !important; }
    #sumup-pix-section { min-height: 200px; }
</style>
