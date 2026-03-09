@extends('admin.layouts.app')

@section('title', 'Scanner de Ingresso: ' . $event->title)

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-qrcode mr-2"></i> Scanner de Ingresso
                        </h3>
                        <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left"></i> Voltar pro Evento
                        </a>
                    </div>
                    <div class="card-body bg-light">
                        <div class="text-center mb-4">
                            <h5>{{ $event->title }}</h5>
                            <p class="text-muted">
                                <i class="fas fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        <div class="alert {{ $scannerOpen ? 'alert-success' : 'alert-danger' }} text-center">
                            <i class="fas {{ $scannerOpen ? 'fa-check-circle' : 'fa-ban' }} mr-1"></i>
                            {{ $scannerStatusMessage }}
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="alert alert-info h-100 mb-0">
                                    <strong>Regra de localizacao:</strong><br>
                                    {{ $event->scannerLocationMessage() }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-secondary h-100 mb-0">
                                    <strong>Auditoria de seguranca:</strong><br>
                                    Toda tentativa de validacao, com sucesso ou erro, fica registrada no sistema.
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-md-8 col-lg-6">
                                <div class="bg-white p-3 rounded shadow-sm mb-4 position-relative">
                                    <div id="reader" width="100%"></div>

                                    <div id="scanner-overlay"
                                        class="position-absolute top-0 left-0 w-100 h-100 d-none flex-column align-items-center justify-content-center rounded"
                                        style="background: rgba(255,255,255,0.9); z-index: 10;">
                                        <div id="overlay-spinner" class="spinner-border text-primary mb-2" role="status"></div>
                                        <i id="overlay-icon" class="fas fa-check-circle text-success"
                                            style="font-size: 3rem; display: none;"></i>
                                        <h5 id="overlay-message" class="mt-3 font-weight-bold text-center px-4">Analisando...</h5>

                                        <button id="btn-scan-again" class="btn btn-primary mt-3" style="display: none;"
                                            onclick="resumeScanning()">
                                            <i class="fas fa-sync-alt mr-1"></i> Ler Proximo Ingresso
                                        </button>
                                    </div>
                                </div>

                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle mr-1"></i> Aponte a camera para o QR Code do ingresso.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        let html5QrcodeScanner = null;
        let isProcessing = false;
        let userCoords = { lat: null, lng: null };
        const scannerOpen = @json($scannerOpen);
        const scannerStatusMessage = @json($scannerStatusMessage);
        const requiresLocation = @json($event->hasScannerLocationConstraint());
        const scannerRadiusMeters = @json($event->scannerLocationRadiusMeters());

        document.addEventListener('DOMContentLoaded', function () {
            if (scannerOpen) {
                captureUserLocationInBackground();
                startScanner();
            }
        });

        function captureUserLocationInBackground() {
            if (!('geolocation' in navigator)) {
                return;
            }

            navigator.geolocation.getCurrentPosition(function (position) {
                userCoords.lat = position.coords.latitude;
                userCoords.lng = position.coords.longitude;
            }, function (error) {
                console.warn('GPS nao disponivel para o scanner admin:', error);
            }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 15000 });
        }

        function startScanner() {
            if (!scannerOpen) {
                toastr.error(scannerStatusMessage);
                return;
            }

            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
            }

            html5QrcodeScanner = new Html5QrcodeScanner(
                'reader',
                { fps: 10, qrbox: { width: 250, height: 250 } },
                false
            );

            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }

        function onScanSuccess(decodedText) {
            if (isProcessing) {
                return;
            }

            isProcessing = true;
            html5QrcodeScanner.pause(true);
            showOverlay('process', 'Validando ingresso...');

            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    userCoords.lat = position.coords.latitude;
                    userCoords.lng = position.coords.longitude;
                    sendValidation(decodedText);
                }, function () {
                    sendValidation(decodedText);
                }, { enableHighAccuracy: true, timeout: 3000, maximumAge: 8000 });
                return;
            }

            sendValidation(decodedText);
        }

        function sendValidation(decodedText) {
            fetch('{{ route('admin.events.scanner.validate', $event) }}', {
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
                        showOverlay('success', 'Entrada Liberada!<br><small class="text-muted">' + data.participant_name + '</small>');
                        toastr.success(data.message);
                        playBeep(true);
                        return;
                    }

                    showOverlay('error', data.message);
                    toastr.error(data.message);
                    playBeep(false);

                    if (requiresLocation && (userCoords.lat === null || userCoords.lng === null)) {
                        toastr.warning('Este evento exige validacao por localizacao em ate ' + scannerRadiusMeters + 'm do ponto do evento.');
                    }
                })
                .catch(function (error) {
                    console.error('Erro na validacao do ingresso:', error);
                    showOverlay('error', 'Erro de comunicacao com o servidor.');
                    toastr.error('Erro de comunicacao com o servidor.');
                    playBeep(false);
                });
        }

        function onScanFailure(error) {
            // Ignora falhas continuas enquanto nao encontra um QR.
        }

        function showOverlay(state, message) {
            const overlay = document.getElementById('scanner-overlay');
            const spinner = document.getElementById('overlay-spinner');
            const icon = document.getElementById('overlay-icon');
            const messageEl = document.getElementById('overlay-message');
            const button = document.getElementById('btn-scan-again');

            overlay.classList.remove('d-none');
            overlay.classList.add('d-flex');
            messageEl.innerHTML = message;

            if (state === 'process') {
                spinner.style.display = 'block';
                icon.style.display = 'none';
                button.style.display = 'none';
                messageEl.className = 'mt-3 font-weight-bold text-center px-4 text-primary';
                return;
            }

            spinner.style.display = 'none';
            icon.style.display = 'block';
            button.style.display = 'block';

            if (state === 'success') {
                icon.className = 'fas fa-check-circle text-success';
                messageEl.className = 'mt-3 font-weight-bold text-center px-4 text-success';
                return;
            }

            icon.className = 'fas fa-times-circle text-danger';
            messageEl.className = 'mt-3 font-weight-bold text-center px-4 text-danger';
        }

        function resumeScanning() {
            const overlay = document.getElementById('scanner-overlay');
            overlay.classList.remove('d-flex');
            overlay.classList.add('d-none');
            isProcessing = false;

            if (html5QrcodeScanner) {
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
                    return;
                }

                osc.type = 'square';
                osc.frequency.setValueAtTime(300, ctx.currentTime);
                osc.frequency.setValueAtTime(300, ctx.currentTime + 0.3);
                gain.gain.setValueAtTime(0.5, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.3);
            } catch (error) {
                console.log('Audio not supported or disabled', error);
            }
        }
    </script>
@endsection
