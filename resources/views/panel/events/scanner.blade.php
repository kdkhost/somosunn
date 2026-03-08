@extends('panel.layouts.app')

@section('title', 'Scanner de Ingresso - ' . $event->title)

@section('content')
    <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-qrcode text-blue-600"></i> Validação de Ingressos
                </h1>
                <p class="text-gray-500 mt-1">{{ $event->title }} &bull;
                    {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i') }}</p>
            </div>
            <a href="{{ route('panel.events.show', $event) }}"
                class="bg-white border shadow-sm text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition font-medium">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>

        <div class="bg-white shadow rounded-2xl overflow-hidden relative border border-gray-100 p-6">

            <div class="max-w-lg mx-auto">
                <div id="reader-container"
                    class="relative rounded-xl overflow-hidden shadow-inner border border-gray-200 bg-gray-50 p-2">
                    <div id="reader" class="rounded-lg overflow-hidden w-full"></div>

                    <!-- Overlay -->
                    <div id="scanner-overlay"
                        class="absolute inset-0 bg-white/95 backdrop-blur-sm z-10 hidden flex-col items-center justify-center p-6 text-center">

                        <!-- Loading Spinner -->
                        <div id="overlay-spinner"
                            class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-4">
                        </div>

                        <!-- Icons -->
                        <i id="overlay-icon-success" class="fas fa-check-circle text-6xl text-green-500 mb-4 hidden"></i>
                        <i id="overlay-icon-error" class="fas fa-times-circle text-6xl text-red-500 mb-4 hidden"></i>

                        <h3 id="overlay-title" class="text-2xl font-black text-gray-900 mb-1">Processando...</h3>
                        <p id="overlay-message" class="text-gray-600 mb-6 font-medium">Aguarde a validação do ingresso.</p>

                        <button id="btn-scan-again" onclick="resumeScanning()"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition-colors hidden items-center justify-center gap-2">
                            <i class="fas fa-sync-alt"></i> Ler Próximo Ingresso
                        </button>
                    </div>
                </div>

                <p class="text-center text-gray-500 text-sm mt-4 flex justify-center items-center gap-2">
                    <i class="fas fa-camera"></i> Aponte a câmera do seu dispositivo para o QR Code emitido para o
                    participante.
                </p>

                <!-- Manual Entry (Optional fallback) -->
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-sm font-bold text-gray-700 mb-3 text-center uppercase tracking-wider">Ou digite o código
                        manualmente</p>
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
        let html5QrcodeScanner = null;
        let isProcessing = false;

        document.addEventListener('DOMContentLoaded', function () {
            startScanner();

            document.getElementById('manual-ticket-form').addEventListener('submit', function (e) {
                e.preventDefault();
                const input = document.getElementById('manual-ticket-input');
                const code = input.value.trim();
                if (code) {
                    input.value = '';
                    processTicket(code);
                }
            });
        });

        function startScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
            }

            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0,
                },
                /* verbose= */ false
            );

            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }

        function onScanSuccess(decodedText) {
            if (isProcessing) return;
            processTicket(decodedText);
        }

        function processTicket(code) {
            if (isProcessing) return;

            isProcessing = true;
            if (html5QrcodeScanner && html5QrcodeScanner.getState() === 2) { // 2 = SCANNING
                html5QrcodeScanner.pause(true);
            }

            showOverlay('process', 'Processando...', 'Validando ingresso no sistema...');

            fetch('{{ route('panel.events.scanner.validate', $event) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ticket_code: code })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showOverlay('success', 'Entrada Liberada!', data.participant_name ? 'Participante: ' + data.participant_name : data.message);

                        if (typeof toastr !== 'undefined') toastr.success(data.message);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Acesso Liberado!',
                                text: data.participant_name ? 'Participante: ' + data.participant_name : data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                        playBeep(true);
                    } else {
                        showOverlay('error', 'Ingresso Inválido', data.message);

                        if (typeof toastr !== 'undefined') toastr.error(data.message);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Acesso Negado',
                                text: data.message,
                                icon: 'error'
                            });
                        }
                        playBeep(false);
                    }
                })
                .catch(error => {
                    console.error('Erro na validação do ingresso:', error);
                    showOverlay('error', 'Erro no Sistema', 'Verifique a conexão de internet e tente novamente.');
                    if (typeof toastr !== 'undefined') toastr.error('Erro de comunicação com o servidor');
                    playBeep(false);
                });
        }

        function onScanFailure(error) {
            // console.warn(`Code scan error = ${error}`);
        }

        function showOverlay(state, title, message) {
            const overlay = document.getElementById('scanner-overlay');
            const spinner = document.getElementById('overlay-spinner');
            const iconSuccess = document.getElementById('overlay-icon-success');
            const iconError = document.getElementById('overlay-icon-error');
            const titleEl = document.getElementById('overlay-title');
            const msgEl = document.getElementById('overlay-message');
            const btn = document.getElementById('btn-scan-again');

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');

            titleEl.innerText = title;
            msgEl.innerHTML = message;

            spinner.classList.add('hidden');
            iconSuccess.classList.add('hidden');
            iconError.classList.add('hidden');
            btn.classList.add('hidden');
            btn.classList.remove('flex');

            if (state === 'process') {
                spinner.classList.remove('hidden');
                titleEl.className = 'text-2xl font-black text-blue-600 mb-1';
            } else if (state === 'success') {
                iconSuccess.classList.remove('hidden');
                btn.classList.remove('hidden');
                btn.classList.add('flex');
                titleEl.className = 'text-2xl font-black text-green-600 mb-1';
                msgEl.className = 'text-gray-600 mb-6 font-medium text-lg';

                // Auto close success after 3 seconds
                setTimeout(() => {
                    if (isProcessing && document.getElementById('overlay-title').innerText === 'Entrada Liberada!') {
                        resumeScanning();
                    }
                }, 3000);

            } else if (state === 'error') {
                iconError.classList.remove('hidden');
                btn.classList.remove('hidden');
                btn.classList.add('flex');
                titleEl.className = 'text-2xl font-black text-red-600 mb-1';
                msgEl.className = 'text-red-700 mb-6 font-bold';
            }
        }

        function resumeScanning() {
            const overlay = document.getElementById('scanner-overlay');
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');

            isProcessing = false;

            if (html5QrcodeScanner && html5QrcodeScanner.getState() === 3) { // 3 = PAUSED
                html5QrcodeScanner.resume();
            }
        }

        function playBeep(success) {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                const ctx = new AudioContext();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.connect(gain);
                gain.connect(ctx.destination);

                if (success) {
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(800, ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.1);
                    gain.gain.setValueAtTime(0.5, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.2);
                } else {
                    osc.type = 'square';
                    osc.frequency.setValueAtTime(300, ctx.currentTime);
                    osc.frequency.setValueAtTime(300, ctx.currentTime + 0.3);
                    gain.gain.setValueAtTime(0.5, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.3);
                }
            } catch (e) {
                console.log("Audio not supported or disabled");
            }
        }
    </script>
@endpush