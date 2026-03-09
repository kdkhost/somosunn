@extends('panel.layouts.app')

@section('title', 'Scanner Universal de Ingressos')

@section('panel_content')
    <div class="max-w-xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="text-center space-y-2">
            <h1 class="text-2xl font-black text-slate-900 dark:text-white transition-colors">Scanner Universal</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Valide qualquer ingresso apontando a câmera.</p>
        </div>

        {{-- Scanner Section --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-[1.5rem] sm:rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-4 sm:p-6 relative">
            <div id="reader-wrapper"
                class="relative bg-slate-100 dark:bg-slate-950 rounded-2xl sm:rounded-3xl overflow-hidden border-4 border-slate-50 dark:border-slate-800 shadow-inner flex items-center justify-center">

                {{-- Start Button (Required for Audio/GPS/Camera permissions in some browsers) --}}
                <div id="start-screen"
                    class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-slate-50 dark:bg-slate-950 p-8 text-center">
                    <div
                        class="w-20 h-20 rounded-full bg-blue-600 text-white flex items-center justify-center text-3xl mb-6 shadow-xl shadow-blue-600/20 animate-pulse">
                        <i class="fas fa-camera"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white mb-2">Permissões Necessárias</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-8">
                        Para validar ingressos, precisamos acessar sua **Câmera**, **Localização** e habilitar o **Sinal
                        Sonoro**.
                    </p>
                    <button onclick="initializeScanner()"
                        class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl shadow-xl transition-all active:scale-95 flex items-center justify-center gap-3">
                        <i class="fas fa-play"></i> ATIVAR E INICIAR
                    </button>

                    @if(!request()->secure() && config('app.env') !== 'local')
                        <div
                            class="mt-6 p-4 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-bold border border-rose-100 dark:border-rose-800/50">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Acesso via HTTP detectado. Câmera e GPS exigem
                            HTTPS para funcionar.
                        </div>
                    @endif
                </div>

                <div id="reader" class="w-full h-full"></div>

                {{-- Overlay Feedback --}}
                <div id="scanner-overlay"
                    class="absolute inset-0 z-50 hidden flex-col items-center justify-center bg-white/95 dark:bg-slate-900/95 transition-all overflow-hidden border-4 border-slate-50 dark:border-slate-800 rounded-2xl sm:rounded-3xl">

                    {{-- Faixa "VALIDADO" --}}
                    <div id="validation-stripe"
                        class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex items-center justify-center bg-emerald-600 py-6 shadow-2xl -rotate-12 scale-150 opacity-0 transition-all duration-500 z-20 pointer-events-none">
                        <span
                            class="text-white text-4xl sm:text-5xl font-black tracking-[0.2em] whitespace-nowrap">VALIDADO</span>
                    </div>

                    {{-- Faixa "INVÁLIDO" --}}
                    <div id="error-stripe"
                        class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex items-center justify-center bg-rose-600 py-6 shadow-2xl rotate-12 scale-150 opacity-0 transition-all duration-500 z-20 pointer-events-none">
                        <span id="error-stripe-text"
                            class="text-white text-xl sm:text-2xl font-black text-center px-4 uppercase tracking-tighter whitespace-nowrap">INVÁLIDO</span>
                    </div>

                    <div id="overlay-content" class="relative z-10 flex flex-col items-center justify-center">
                        <div id="overlay-icon-wrapper"
                            class="w-24 h-24 rounded-full flex items-center justify-center mb-6 transition-transform scale-0 duration-300">
                            <i id="overlay-icon" class="fas fa-check text-4xl"></i>
                        </div>
                        <h2 id="overlay-status" class="text-xl sm:text-2xl font-black text-center px-6">Processando...</h2>
                        <p id="overlay-detail" class="text-sm sm:text-base text-slate-500 mt-2 text-center px-8 font-bold">
                        </p>
                    </div>

                    <button id="resume-btn" onclick="resumeScanning()"
                        class="mt-10 px-10 py-4 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 font-extrabold rounded-2xl shadow-xl transition-all active:scale-95 z-30 hidden opacity-0">
                        CONTINUAR ESCANEANDO
                    </button>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3">
                <div
                    class="flex items-center gap-2 p-4 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-2xl text-sm font-semibold border border-blue-100 dark:border-blue-800/50">
                    <i class="fas fa-info-circle"></i>
                    <p>Posicione o QR Code no centro da moldura.</p>
                </div>
            </div>
        </div>

        {{-- Event List --}}
        @if($todayEvents->count() > 0)
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest px-2">Eventos de Hoje / Pendentes</h3>
                <div class="grid gap-3">
                    @foreach($todayEvents as $event)
                        <div
                            class="flex items-center justify-between p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                            <div class="min-w-0">
                                <h4 class="text-sm font-black text-slate-900 dark:text-white truncate">{{ $event->title }}</h4>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">
                                    {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <span
                                class="px-3 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-black rounded-lg border border-emerald-100 dark:border-emerald-800/50">
                                Ativo
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        let html5QrCode = null;
        let isProcessing = false;
        let userCoords = { lat: null, lng: null };

        let audioCtx = null;

        document.addEventListener('DOMContentLoaded', function () {
            // Não inicia mais sozinho para respeitar políticas de Audio/Câmera
        });

        async function initializeScanner() {
            // 1. Solicita Notificações (para áudio/texto)
            if ("Notification" in window) {
                const permission = await Notification.requestPermission();
                if (permission !== "granted") {
                    console.warn("Notificações não permitidas.");
                    // Não bloqueia totalmente por causa das notificações de áudio serem via AudioContext,
                    // mas avisa.
                }
            }

            // 2. Inicializa AudioContext (Gesto do Usuário necessário)
            try {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                if (audioCtx.state === 'suspended') {
                    await audioCtx.resume();
                }
            } catch (e) {
                console.error("Erro ao iniciar Áudio:", e);
            }

            // 3. Solicita GPS e só inicia scanner se tiver retorno
            const startBtn = document.querySelector('#start-screen button');
            const originalText = startBtn.innerHTML;
            startBtn.disabled = true;
            startBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> AUTORIZANDO...';

            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        userCoords.lat = position.coords.latitude;
                        userCoords.lng = position.coords.longitude;
                        console.log("GPS capturado:", userCoords);

                        // Esconde tela inicial e inicia scanner
                        document.getElementById('start-screen').classList.add('hidden');
                        startScanner();
                    },
                    (error) => {
                        console.error("Erro ao obter GPS:", error);
                        startBtn.disabled = false;
                        startBtn.innerHTML = originalText;

                        let msg = 'Para validar os ingressos, você PRECISA permitir o acesso à localização.';
                        if (error.code === error.PERMISSION_DENIED) {
                            msg = 'Você negou o acesso ao GPS. Por favor, habilite nas configurações do seu navegador/celular para continuar.';
                        }

                        Swal.fire({
                            title: 'GPS OBRIGATÓRIO',
                            text: msg,
                            icon: 'error',
                            confirmButtonText: 'Tentar Novamente',
                            confirmButtonColor: '#1F5EDB'
                        });
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else {
                Swal.fire({
                    title: 'Dispositivo Incompatível',
                    text: 'Seu dispositivo não suporta GPS, que é obrigatório para este scanner.',
                    icon: 'error'
                });
                startBtn.disabled = false;
                startBtn.innerHTML = originalText;
            }
        }

        function requestGPS() {
            // Função legada removida, agora tratada no fluxo de inicialização obrigatória
        }

        async function startScanner() {
            if (html5QrCode) {
                try {
                    await html5QrCode.stop();
                } catch (e) { }
            }

            html5QrCode = new Html5Qrcode("reader");

            const config = {
                fps: 15,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            try {
                await html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    onScanSuccess
                );
                playBeep('scan');
            } catch (err) {
                console.error("Erro ao iniciar câmera:", err);
                Swal.fire({
                    title: 'Erro na Câmera',
                    text: 'Não conseguimos acessar sua câmera. Verifique se você deu permissão e se não há outro app usando a câmera.',
                    icon: 'error',
                    confirmButtonText: 'Tentar Novamente',
                    confirmButtonColor: '#1F5EDB'
                }).then(() => {
                    location.reload();
                });
            }
        }

        function onScanSuccess(decodedText) {
            if (isProcessing) return;
            isProcessing = true;

            showOverlay('loading', 'Validando ingressos...');
            playBeep('scan');

            // Tenta pegar o GPS mais atualizado antes de enviar
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition((position) => {
                    userCoords.lat = position.coords.latitude;
                    userCoords.lng = position.coords.longitude;
                    sendValidation(decodedText);
                }, () => {
                    sendValidation(decodedText); // Envia mesmo se falhar (o backend vai barrar se for obrigatório)
                }, { timeout: 3000 });
            } else {
                sendValidation(decodedText);
            }
        }

        function sendValidation(decodedText) {
            fetch('{{ route('panel.admin.quick-scanner.validate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ticket_code: decodedText,
                    latitude: userCoords.lat,
                    longitude: userCoords.lng
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showOverlay('success', 'Acesso Liberado!', (data.participant_name || 'Participante') + '<br><span class="text-[10px]">' + (data.event_title || '') + '</span>');
                        playBeep('success');
                        if (navigator.vibrate) navigator.vibrate([100, 50, 100]);

                        // Fecha automaticamente em 1.8s
                        setTimeout(() => {
                            resumeScanning();
                        }, 1800);
                    } else {
                        let errorMsg = data.message;
                        // Personaliza msg de fraude/outro evento se necessário
                        if (errorMsg.includes('não encontrado')) {
                            errorMsg = 'Ingresso inválido para este evento.<br><small>Possível fraude ou ingresso de outro evento.</small>';
                        }

                        showOverlay('error', 'Acesso Negado', errorMsg);
                        playBeep('error');
                        if (navigator.vibrate) navigator.vibrate(500);
                    }
                })
                .catch(error => {
                    showOverlay('error', 'Erro de Conexão', 'Não foi possível validar o código.');
                    playBeep('error');
                    if (navigator.vibrate) navigator.vibrate(500);
                });
        }

        function showOverlay(type, status, detail = '') {
            const overlay = document.getElementById('scanner-overlay');
            const iconWrapper = document.getElementById('overlay-icon-wrapper');
            const icon = document.getElementById('overlay-icon');
            const statusEl = document.getElementById('overlay-status');
            const detailEl = document.getElementById('overlay-detail');
            const validationStripe = document.getElementById('validation-stripe');
            const errorStripe = document.getElementById('error-stripe');
            const resumeBtn = document.getElementById('resume-btn');

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            statusEl.innerHTML = status;
            detailEl.innerHTML = detail;
            iconWrapper.classList.remove('scale-0');
            iconWrapper.classList.add('scale-100');

            // Reset effects
            validationStripe.classList.add('opacity-0');
            validationStripe.classList.add('scale-150');
            errorStripe.classList.add('opacity-0');
            errorStripe.classList.add('scale-150');
            resumeBtn.classList.add('hidden');
            resumeBtn.classList.add('opacity-0');

            if (type === 'loading') {
                iconWrapper.className = 'w-24 h-24 rounded-full flex items-center justify-center mb-6 bg-blue-100 dark:bg-blue-900/30 text-blue-600';
                icon.className = 'fas fa-spinner fa-spin text-4xl';
            } else if (type === 'success') {
                iconWrapper.className = 'w-24 h-24 rounded-full flex items-center justify-center mb-6 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600';
                icon.className = 'fas fa-check text-4xl';

                // Animate Stripe
                setTimeout(() => {
                    validationStripe.classList.remove('opacity-0', 'scale-150');
                    validationStripe.classList.add('opacity-100', 'scale-100', 'rotate-[-12deg]');
                }, 50);

            } else {
                iconWrapper.className = 'w-24 h-24 rounded-full flex items-center justify-center mb-6 bg-rose-100 dark:bg-rose-900/30 text-rose-600';
                icon.className = 'fas fa-times text-4xl';
                resumeBtn.classList.remove('hidden');
                setTimeout(() => {
                    resumeBtn.classList.remove('opacity-0');
                    resumeBtn.classList.add('opacity-100');
                }, 100);

                // Animate Error Stripe
                setTimeout(() => {
                    const errorTextEl = document.getElementById('error-stripe-text');
                    if (detail.toLowerCase().includes('evento') || detail.toLowerCase().includes('encontrado') || detail.toLowerCase().includes('inválido')) {
                        errorTextEl.innerText = 'INGRESSO INVÁLIDO';
                    } else {
                        errorTextEl.innerText = 'ACESSO NEGADO';
                    }
                    errorStripe.classList.remove('opacity-0', 'scale-150');
                    errorStripe.classList.add('opacity-100', 'scale-100', 'rotate-[12deg]');
                }, 50);
            }
        }

        function resumeScanning() {
            const overlay = document.getElementById('scanner-overlay');
            const validationStripe = document.getElementById('validation-stripe');
            const errorStripe = document.getElementById('error-stripe');
            const resumeBtn = document.getElementById('resume-btn');

            overlay.classList.add('hidden');
            overlay.classList.remove('flex');

            validationStripe.classList.remove('opacity-100', 'scale-100');
            validationStripe.classList.add('opacity-0', 'scale-150');

            errorStripe.classList.remove('opacity-100', 'scale-100');
            errorStripe.classList.add('opacity-0', 'scale-150');

            resumeBtn.classList.add('hidden', 'opacity-0');

            isProcessing = false;
        }

        function playBeep(type) {
            if (!audioCtx) return;

            try {
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }

                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);

                if (type === 'scan') {
                    osc.frequency.setValueAtTime(600, audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
                    osc.start(); osc.stop(audioCtx.currentTime + 0.05);
                } else if (type === 'success') {
                    osc.frequency.setValueAtTime(800, audioCtx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.1);
                    gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                    osc.start(); osc.stop(audioCtx.currentTime + 0.2);
                } else if (type === 'error') {
                    osc.type = 'square';
                    osc.frequency.setValueAtTime(150, audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                    osc.start(); osc.stop(audioCtx.currentTime + 0.3);
                }
            } catch (e) { }
        }
    </script>
@endpush