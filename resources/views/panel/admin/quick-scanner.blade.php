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
            class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6 relative">
            <div id="reader-wrapper"
                class="relative bg-slate-100 dark:bg-slate-950 rounded-3xl overflow-hidden aspect-square border-4 border-slate-50 dark:border-slate-800 shadow-inner flex items-center justify-center">

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
                    class="absolute inset-0 z-10 hidden flex-col items-center justify-center bg-white/95 dark:bg-slate-900/95 transition-all">
                    <div id="overlay-icon-wrapper"
                        class="w-20 h-20 rounded-full flex items-center justify-center mb-4 transition-transform scale-0 duration-300">
                        <i id="overlay-icon" class="fas fa-check text-3xl"></i>
                    </div>
                    <h2 id="overlay-status" class="text-lg font-black text-center px-6">Processando...</h2>
                    <p id="overlay-detail" class="text-sm text-slate-500 mt-1 text-center px-8"></p>

                    <button onclick="resumeScanning()"
                        class="mt-8 px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg transition-all active:scale-95">
                        Próximo Escaneamento
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
        let html5QrcodeScanner = null;
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
                        setTimeout(() => playBeep('scan'), 100);
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

        function startScanner() {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                {
                    fps: 15,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0,
                    showTorchButtonIfSupported: true,
                    showZoomSliderIfSupported: true,
                    defaultZoomValueIfSupported: 2
                },
                        /* verbose= */ false
            );
            html5QrcodeScanner.render(onScanSuccess, (errorMessage) => {
                // Erros de leitura ignorados silenciosamente
            });

            // Tratamento de erro de permissão da câmera
            setTimeout(() => {
                const cameraSelection = document.getElementById('html5-qrcode-button-camera-permission');
                if (cameraSelection) {
                    cameraSelection.addEventListener('click', () => {
                        // O navegador vai pedir a câmera aqui
                    });
                }
            }, 500);
        }

        function onScanSuccess(decodedText) {
            if (isProcessing) return;
            isProcessing = true;

            // Pausa a câmera
            html5QrcodeScanner.pause(true);

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
                        showOverlay('success', 'Acesso Liberado!', data.participant_name + '<br><span class="text-[10px]">' + data.event_title + '</span>');
                        playBeep('success');
                        if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                    } else {
                        showOverlay('error', 'Acesso Negado', data.message);
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

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            statusEl.innerHTML = status;
            detailEl.innerHTML = detail;
            iconWrapper.classList.remove('scale-0');
            iconWrapper.classList.add('scale-100');

            if (type === 'loading') {
                iconWrapper.className = 'w-20 h-20 rounded-full flex items-center justify-center mb-4 bg-blue-100 dark:bg-blue-900/30 text-blue-600';
                icon.className = 'fas fa-spinner fa-spin text-3xl';
            } else if (type === 'success') {
                iconWrapper.className = 'w-20 h-20 rounded-full flex items-center justify-center mb-4 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600';
                icon.className = 'fas fa-check text-3xl';
            } else {
                iconWrapper.className = 'w-20 h-20 rounded-full flex items-center justify-center mb-4 bg-rose-100 dark:bg-rose-900/30 text-rose-600';
                icon.className = 'fas fa-times text-3xl';
            }
        }

        function resumeScanning() {
            const overlay = document.getElementById('scanner-overlay');
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            isProcessing = false;
            html5QrcodeScanner.resume();
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
                    osc.frequency.setValueAtTime(600, ctx.currentTime);
                    gain.gain.setValueAtTime(0.1, ctx.currentTime);
                    osc.start(); osc.stop(ctx.currentTime + 0.05);
                } else if (type === 'success') {
                    osc.frequency.setValueAtTime(800, ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.1);
                    gain.gain.setValueAtTime(0.2, ctx.currentTime);
                    osc.start(); osc.stop(ctx.currentTime + 0.2);
                } else if (type === 'error') {
                    osc.type = 'square';
                    osc.frequency.setValueAtTime(150, ctx.currentTime);
                    gain.gain.setValueAtTime(0.2, ctx.currentTime);
                    osc.start(); osc.stop(ctx.currentTime + 0.3);
                }
            } catch (e) { }
        }
    </script>
@endpush