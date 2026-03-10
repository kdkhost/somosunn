@extends('panel.layouts.app')

@section('title', 'Scanner de Ingresso - ' . $event->title)

@section('content')
    <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-qrcode text-blue-600"></i> Validacao de Ingressos
                </h1>
                <p class="text-gray-500 mt-1">
                    {{ $event->title }} &bull; {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i') }}
                </p>
            </div>
            <a href="{{ route('panel.events.show', $event) }}"
                class="bg-white border shadow-sm text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition font-medium">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>

        <div
            class="mb-4 rounded-2xl border px-4 py-3 text-sm font-semibold {{ $scannerOpen ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' }}">
            <i class="fas {{ $scannerOpen ? 'fa-check-circle' : 'fa-ban' }} mr-2"></i>{{ $scannerStatusMessage }}
        </div>

        <div class="mb-4 grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                <div class="mb-1 text-xs font-bold uppercase tracking-wide">Regra de localizacao</div>
                <p>{{ $event->scannerLocationMessage() }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                <div class="mb-1 text-xs font-bold uppercase tracking-wide">Auditoria de seguranca</div>
                <p>Toda tentativa de validacao, com sucesso ou erro, fica registrada no sistema.</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-2xl overflow-hidden relative border border-gray-100 p-6">
            <div class="max-w-lg mx-auto">
                <div id="reader-wrapper"
                    class="relative rounded-xl overflow-hidden shadow-inner border border-gray-200 bg-gray-50 p-2 flex items-center justify-center min-h-[300px]">
                    <div id="start-screen"
                        class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-gray-50 p-8 text-center rounded-xl">
                        <div
                            class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl mb-4 shadow-lg shadow-blue-600/20">
                            <i class="fas fa-camera"></i>
                        </div>
                        <h3 class="text-lg font-black text-gray-900 mb-1">Permissoes de validacao</h3>
                    <p class="text-xs text-gray-500 mb-6">
                        Clique abaixo para habilitar a camera e os alertas sonoros.
                        @if($event->scannerLocationRestrictionEnabled())
                            O GPS sera usado conforme a regra configurada: {{ $event->scannerLocationMessage() }}
                        @else
                            Este evento nao exige geolocalizacao para validar o ingresso.
                        @endif
                        </p>
                        <button onclick="initializeScanner()"
                            {{ $scannerOpen ? '' : 'disabled' }}
                            class="w-full py-3 {{ $scannerOpen ? 'bg-blue-600 hover:bg-blue-700' : 'bg-slate-300 cursor-not-allowed' }} text-white font-bold rounded-xl shadow-md transition-all active:scale-95 flex items-center justify-center gap-2">
                            <i class="fas {{ $scannerOpen ? 'fa-play' : 'fa-ban' }}"></i>
                            {{ $scannerOpen ? 'INICIAR SCANNER' : 'SCANNER EXPIRADO' }}
                        </button>
                    </div>

                    <div id="reader" class="rounded-lg overflow-hidden w-full"></div>

                    <div id="scanner-overlay"
                        class="absolute inset-0 bg-white/95 backdrop-blur-sm z-10 hidden flex-col items-center justify-center p-6 text-center">
                        <div id="overlay-spinner"
                            class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-4">
                        </div>
                        <i id="overlay-icon-success" class="fas fa-check-circle text-6xl text-green-500 mb-4 hidden"></i>
                        <i id="overlay-icon-error" class="fas fa-times-circle text-6xl text-red-500 mb-4 hidden"></i>

                        <h3 id="overlay-title" class="text-2xl font-black text-gray-900 mb-1">Processando...</h3>
                        <p id="overlay-message" class="text-gray-600 mb-6 font-medium">Aguarde a validacao do ingresso.</p>

                        <button id="btn-scan-again" onclick="resumeScanning()"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition-colors hidden items-center justify-center gap-2">
                            <i class="fas fa-sync-alt"></i> Ler Proximo Ingresso
                        </button>
                    </div>
                </div>

                <p class="text-center text-gray-500 text-sm mt-4 flex justify-center items-center gap-2">
                    <i class="fas fa-camera"></i> Aponte a camera do seu dispositivo para o QR Code emitido para o participante.
                </p>

                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-sm font-bold text-gray-700 mb-3 text-center uppercase tracking-wider">Ou digite o codigo manualmente</p>
                    <form id="manual-ticket-form" class="flex gap-2">
                        <input type="text" id="manual-ticket-input" placeholder="Ex: e47b-891a-..."
                            class="flex-1 rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                            required>
                        <button type="submit"
                            class="bg-gray-800 text-white px-6 py-2 rounded-xl font-bold hover:bg-gray-900 transition shadow">Validar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        let audioCtx = null;
        let html5QrCode = null;
        let isProcessing = false;
        let userCoords = { lat: null, lng: null };
        const scannerOpen = @json($scannerOpen);
        const scannerStatusMessage = @json($scannerStatusMessage);
        const requiresLocation = @json($event->hasScannerLocationConstraint());
        const scannerLocationMessage = @json($event->scannerLocationMessage());

        async function initializeScanner() {
            if (!scannerOpen) {
                Swal.fire({
                    title: 'Scanner indisponivel',
                    text: scannerStatusMessage,
                    icon: 'error',
                    confirmButtonColor: '#1F5EDB'
                });
                return;
            }

            if ('Notification' in window) {
                await Notification.requestPermission();
            }

            try {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                if (audioCtx.state === 'suspended') {
                    await audioCtx.resume();
                }
            } catch (error) {
                console.error('Erro audio:', error);
            }

            const startBtn = document.querySelector('#start-screen button');
            const originalText = startBtn.innerHTML;
            startBtn.disabled = true;
            startBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> AUTORIZANDO...';

            if (requiresLocation) {
                captureUserLocationInBackground();
            }
            document.getElementById('start-screen').classList.add('hidden');
            await startScanner();
            setTimeout(() => playBeep(true), 100);

            startBtn.disabled = false;
            startBtn.innerHTML = originalText;
        }

        document.getElementById('manual-ticket-form').addEventListener('submit', function (event) {
            event.preventDefault();
            const input = document.getElementById('manual-ticket-input');
            const code = input.value.trim();
            if (code) {
                processTicket(code);
            }
        });

        function captureUserLocationInBackground() {
            if (!('geolocation' in navigator)) {
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    userCoords.lat = position.coords.latitude;
                    userCoords.lng = position.coords.longitude;
                },
                function (error) {
                    console.warn('GPS indisponivel para o scanner:', error);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 15000 }
            );
        }

        async function startScanner() {
            if (html5QrCode) {
                try {
                    await html5QrCode.stop();
                } catch (error) {
                    console.warn('Falha ao parar scanner anterior:', error);
                }
            }

            html5QrCode = new Html5Qrcode('reader');

            try {
                await html5QrCode.start(
                    { facingMode: 'environment' },
                    { fps: 15, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
                    onScanSuccess
                );
                playBeep(true);
            } catch (error) {
                console.error('Erro camera:', error);
                Swal.fire({
                    title: 'Erro na camera',
                    text: 'Nao conseguimos acessar a camera. Verifique as permissoes.',
                    icon: 'error',
                    confirmButtonColor: '#1F5EDB'
                });
            }
        }

        function onScanSuccess(decodedText) {
            if (isProcessing) {
                return;
            }

            processTicket(decodedText);
        }

        function processTicket(code) {
            isProcessing = true;
            showOverlay('process', 'Processando...', 'Validando ingresso no sistema...');

            if (requiresLocation && 'geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    userCoords.lat = position.coords.latitude;
                    userCoords.lng = position.coords.longitude;
                    sendValidation(code);
                }, function () {
                    sendValidation(code);
                }, { enableHighAccuracy: true, timeout: 3000, maximumAge: 8000 });
                return;
            }

            sendValidation(code);
        }

        function sendValidation(code) {
            fetch('{{ route('panel.events.scanner.validate', $event) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ticket_code: code,
                    latitude: userCoords.lat,
                    longitude: userCoords.lng
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showOverlay('success', 'Entrada Liberada!', data.participant_name);
                        playBeep(true);
                        if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                        return;
                    }

                    showOverlay('error', 'Entrada Negada', data.message);
                    playBeep(false);
                    if (navigator.vibrate) navigator.vibrate(500);

                    if (requiresLocation && (userCoords.lat === null || userCoords.lng === null)) {
                        Swal.fire({
                            title: 'GPS recomendado',
                            text: scannerLocationMessage + ' Habilite o GPS para evitar recusas.',
                            icon: 'warning',
                            confirmButtonColor: '#1F5EDB'
                        });
                    }
                })
                .catch(function () {
                    showOverlay('error', 'Erro tecnico', 'Nao foi possivel validar o ingresso.');
                    playBeep(false);
                    if (navigator.vibrate) navigator.vibrate(500);
                });
        }

        function showOverlay(state, title, message) {
            const overlay = document.getElementById('scanner-overlay');
            const spinner = document.getElementById('overlay-spinner');
            const iconSuccess = document.getElementById('overlay-icon-success');
            const iconError = document.getElementById('overlay-icon-error');
            const titleEl = document.getElementById('overlay-title');
            const messageEl = document.getElementById('overlay-message');
            const button = document.getElementById('btn-scan-again');

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');

            titleEl.innerText = title;
            messageEl.innerHTML = message;

            spinner.classList.add('hidden');
            iconSuccess.classList.add('hidden');
            iconError.classList.add('hidden');
            button.classList.add('hidden');
            button.classList.remove('flex');

            if (state === 'process') {
                spinner.classList.remove('hidden');
                titleEl.className = 'text-2xl font-black text-blue-600 mb-1';
                return;
            }

            if (state === 'success') {
                iconSuccess.classList.remove('hidden');
                button.classList.remove('hidden');
                button.classList.add('flex');
                titleEl.className = 'text-2xl font-black text-green-600 mb-1';
                messageEl.className = 'text-gray-600 mb-6 font-medium text-lg';

                setTimeout(function () {
                    if (isProcessing && document.getElementById('overlay-title').innerText === 'Entrada Liberada!') {
                        resumeScanning();
                    }
                }, 3000);

                return;
            }

            iconError.classList.remove('hidden');
            button.classList.remove('hidden');
            button.classList.add('flex');
            titleEl.className = 'text-2xl font-black text-red-600 mb-1';
            messageEl.className = 'text-red-700 mb-6 font-bold';
        }

        function resumeScanning() {
            const overlay = document.getElementById('scanner-overlay');
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            isProcessing = false;
        }

        function playBeep(success) {
            if (!audioCtx) {
                return;
            }

            try {
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }

                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);

                if (success) {
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(800, audioCtx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.1);
                    gain.gain.setValueAtTime(0.5, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.2);
                    osc.start(audioCtx.currentTime);
                    osc.stop(audioCtx.currentTime + 0.2);
                    return;
                }

                osc.type = 'square';
                osc.frequency.setValueAtTime(300, audioCtx.currentTime);
                osc.frequency.setValueAtTime(300, audioCtx.currentTime + 0.3);
                gain.gain.setValueAtTime(0.5, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
                osc.start(audioCtx.currentTime);
                osc.stop(audioCtx.currentTime + 0.3);
            } catch (error) {
                console.log('Audio not supported or disabled', error);
            }
        }
    </script>
@endpush
