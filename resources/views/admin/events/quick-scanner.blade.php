@extends('admin.layouts.app')

@section('title', 'Scanner Universal de Ingressos')

@section('page_title', 'Scanner Universal de Ingressos')

@section('breadcrumb_items')
    <li class="breadcrumb-item"><a href="{{ route('admin.events.index') }}">Eventos</a></li>
    <li class="breadcrumb-item active">Scanner Universal</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                        <div>
                            <h3 class="mb-2 font-weight-bold">
                                <i class="fas fa-qrcode mr-2 text-primary"></i>Leitura rapida para todos os eventos
                            </h3>
                            <p class="text-muted mb-0">
                                Valide qualquer ingresso ativo. Se o evento usar geocerca, a regra do proprio evento sera aplicada automaticamente.
                            </p>
                        </div>
                        <div class="mt-3 mt-lg-0 d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.events.list') }}" class="btn btn-outline-primary">
                                <i class="fas fa-list mr-1"></i> Lista de eventos
                            </a>
                            <a href="{{ route('admin.events.index') }}" class="btn btn-light border">
                                <i class="fas fa-calendar-alt mr-1"></i> Calendario
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div id="reader-wrapper" class="position-relative rounded overflow-hidden border bg-light" style="min-height: 420px;">
                            <div id="start-screen"
                                class="position-absolute w-100 h-100 d-flex flex-column align-items-center justify-content-center text-center px-4"
                                style="inset: 0; z-index: 20; background: rgba(248, 250, 252, 0.96);">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mb-4 shadow"
                                    style="width: 90px; height: 90px; font-size: 34px;">
                                    <i class="fas fa-camera"></i>
                                </div>
                                <h4 class="font-weight-black mb-2">Camera e GPS sob demanda</h4>
                                <p class="text-muted mb-4" style="max-width: 520px;">
                                    O leitor abre com um toque. A camera sera iniciada imediatamente e o GPS so entra na validacao quando o evento exigir localizacao exata ou raio configurado.
                                </p>
                                <button id="start-scanner-btn" type="button" class="btn btn-primary btn-lg px-5 font-weight-bold"
                                    onclick="initializeScanner()">
                                    <i class="fas fa-play mr-2"></i> Ativar scanner
                                </button>
                            </div>

                            <div id="reader" style="width: 100%; min-height: 420px;"></div>

                            <div id="scanner-overlay"
                                class="position-absolute w-100 h-100 d-none flex-column align-items-center justify-content-center text-center px-4"
                                style="inset: 0; z-index: 30; background: rgba(255,255,255,0.96);">
                                <div id="overlay-spinner" class="spinner-border text-primary mb-3" role="status"></div>
                                <div id="overlay-icon-wrapper"
                                    class="d-flex align-items-center justify-content-center rounded-circle mb-3"
                                    style="width: 92px; height: 92px; background: #dbeafe; color: #2563eb; transform: scale(0); transition: transform .25s ease;">
                                    <i id="overlay-icon" class="fas fa-spinner fa-spin" style="font-size: 36px;"></i>
                                </div>
                                <h4 id="overlay-status" class="font-weight-black mb-2">Processando...</h4>
                                <p id="overlay-detail" class="text-muted font-weight-bold mb-0"></p>
                                <button id="resume-btn" type="button" class="btn btn-dark mt-4 d-none" onclick="resumeScanning()">
                                    <i class="fas fa-sync-alt mr-1"></i> Ler proximo ingresso
                                </button>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="alert alert-info h-100 mb-0">
                                    <strong><i class="fas fa-map-marked-alt mr-1"></i> Geocerca configuravel</strong><br>
                                    Cada evento pode exigir localizacao exata, margem em metros ou km, ou leitura livre sem restricao.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-secondary h-100 mb-0">
                                    <strong><i class="fas fa-shield-alt mr-1"></i> Auditoria</strong><br>
                                    Toda leitura com sucesso ou erro fica registrada para rastreabilidade e seguranca.
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-top">
                            <form id="manual-ticket-form" class="form-inline d-flex flex-column flex-md-row align-items-stretch align-items-md-center">
                                <input type="text" id="manual-ticket-input" class="form-control flex-fill mr-md-2 mb-2 mb-md-0"
                                    placeholder="Digite o codigo do ingresso manualmente" required>
                                <button type="submit" class="btn btn-dark font-weight-bold">
                                    <i class="fas fa-keyboard mr-1"></i> Validar manualmente
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Eventos liberados para leitura</h3>
                    </div>
                    <div class="card-body p-3">
                        @if($todayEvents->isEmpty())
                            <div class="alert alert-light border mb-0">
                                Nenhum evento publicado com ingressos ativos nos proximos dias.
                            </div>
                        @else
                            <div class="d-flex flex-column" style="gap: 12px;">
                                @foreach($todayEvents as $event)
                                    @php
                                        $mode = $event->scannerRestrictionMode();
                                        $modeLabel = $mode === \App\Models\Event::SCANNER_RESTRICTION_DISABLED
                                            ? 'Sem restricao'
                                            : ($mode === \App\Models\Event::SCANNER_RESTRICTION_EXACT ? 'Localizacao exata' : 'Raio ' . $event->scannerFormattedRadius());
                                        $modeClass = $mode === \App\Models\Event::SCANNER_RESTRICTION_DISABLED
                                            ? 'badge-secondary'
                                            : ($mode === \App\Models\Event::SCANNER_RESTRICTION_EXACT ? 'badge-warning' : 'badge-info');
                                    @endphp
                                    <div class="border rounded-lg p-3">
                                        <div class="d-flex align-items-start justify-content-between" style="gap: 12px;">
                                            <div class="pr-2">
                                                <h4 class="h6 font-weight-black mb-1">{{ $event->title }}</h4>
                                                <div class="text-muted small font-weight-bold">
                                                    {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i') }}
                                                </div>
                                            </div>
                                            <span class="badge {{ $modeClass }} px-3 py-2">{{ $modeLabel }}</span>
                                        </div>
                                        <p class="text-muted small mb-0 mt-2">{{ $event->scannerLocationMessage() }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        let html5QrCode = null;
        let isProcessing = false;
        let isStartingScanner = false;
        let userCoords = { lat: null, lng: null };
        let audioCtx = null;

        async function initializeScanner() {
            if (isStartingScanner) {
                return;
            }

            isStartingScanner = true;
            const startScreen = document.getElementById('start-screen');
            const startBtn = document.getElementById('start-scanner-btn');
            const originalText = startBtn.innerHTML;

            startBtn.disabled = true;
            startBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Iniciando...';

            try {
                await initializeAudio();
                startScreen.classList.add('d-none');
                captureUserLocationInBackground();
                await startScanner();
            } catch (error) {
                console.error('Erro ao iniciar scanner admin:', error);
                startScreen.classList.remove('d-none');
                toastr.error('Nao foi possivel abrir a camera. Verifique a permissao do navegador.');
            } finally {
                startBtn.disabled = false;
                startBtn.innerHTML = originalText;
                isStartingScanner = false;
            }
        }

        async function initializeAudio() {
            try {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                if (audioCtx.state === 'suspended') {
                    await audioCtx.resume();
                }
            } catch (error) {
                console.warn('Falha ao iniciar audio do scanner admin:', error);
            }
        }

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
                    console.warn('GPS indisponivel para o scanner admin:', error);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 15000 }
            );
        }

        async function startScanner() {
            if (html5QrCode) {
                try {
                    await html5QrCode.stop();
                } catch (error) {
                    console.warn('Falha ao parar scanner admin anterior:', error);
                }
            }

            html5QrCode = new Html5Qrcode('reader');

            const config = {
                fps: 15,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            try {
                await html5QrCode.start({ facingMode: 'environment' }, config, onScanSuccess);
                playBeep('scan');
                return;
            } catch (primaryError) {
                console.warn('Falha ao iniciar por facingMode no admin:', primaryError);
            }

            const cameras = await Html5Qrcode.getCameras();
            if (!cameras.length) {
                throw new Error('Nenhuma camera disponivel.');
            }

            const backCamera =
                cameras.find((camera) => /back|rear|traseira|environment/i.test(camera.label || '')) ||
                cameras[0];

            await html5QrCode.start(backCamera.id, config, onScanSuccess);
            playBeep('scan');
        }

        function onScanSuccess(decodedText) {
            if (isProcessing) {
                return;
            }

            isProcessing = true;
            showOverlay('loading', 'Validando ingresso...');
            playBeep('scan');

            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        userCoords.lat = position.coords.latitude;
                        userCoords.lng = position.coords.longitude;
                        sendValidation(decodedText);
                    },
                    function () {
                        sendValidation(decodedText);
                    },
                    { enableHighAccuracy: true, timeout: 3000, maximumAge: 8000 }
                );

                return;
            }

            sendValidation(decodedText);
        }

        function sendValidation(decodedText) {
            fetch('{{ route('admin.quick-scanner.validate') }}', {
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
                        showOverlay(
                            'success',
                            'Entrada liberada!',
                            (data.participant_name || 'Participante') + (data.event_title ? '<br><small>' + data.event_title + '</small>' : '')
                        );
                        toastr.success(data.message || 'Ingresso validado com sucesso.');
                        playBeep('success');
                        return setTimeout(resumeScanning, 1800);
                    }

                    showOverlay('error', 'Acesso negado', data.message || 'Ingresso invalido.');
                    toastr.error(data.message || 'Ingresso invalido.');
                    playBeep('error');
                })
                .catch(function () {
                    showOverlay('error', 'Erro de conexao', 'Nao foi possivel validar o ingresso.');
                    toastr.error('Nao foi possivel validar o ingresso.');
                    playBeep('error');
                });
        }

        function showOverlay(type, status, detail = '') {
            const overlay = document.getElementById('scanner-overlay');
            const spinner = document.getElementById('overlay-spinner');
            const iconWrapper = document.getElementById('overlay-icon-wrapper');
            const icon = document.getElementById('overlay-icon');
            const statusEl = document.getElementById('overlay-status');
            const detailEl = document.getElementById('overlay-detail');
            const resumeBtn = document.getElementById('resume-btn');

            overlay.classList.remove('d-none');
            overlay.classList.add('d-flex');
            statusEl.innerHTML = status;
            detailEl.innerHTML = detail;
            resumeBtn.classList.add('d-none');
            spinner.style.display = 'block';
            iconWrapper.style.transform = 'scale(0)';

            if (type === 'loading') {
                iconWrapper.style.background = '#dbeafe';
                iconWrapper.style.color = '#2563eb';
                icon.className = 'fas fa-spinner fa-spin';
                return;
            }

            spinner.style.display = 'none';
            iconWrapper.style.transform = 'scale(1)';

            if (type === 'success') {
                iconWrapper.style.background = '#d1fae5';
                iconWrapper.style.color = '#059669';
                icon.className = 'fas fa-check';
                return;
            }

            iconWrapper.style.background = '#ffe4e6';
            iconWrapper.style.color = '#e11d48';
            icon.className = 'fas fa-times';
            resumeBtn.classList.remove('d-none');
        }

        function resumeScanning() {
            const overlay = document.getElementById('scanner-overlay');
            overlay.classList.remove('d-flex');
            overlay.classList.add('d-none');
            isProcessing = false;
        }

        document.getElementById('manual-ticket-form').addEventListener('submit', function (event) {
            event.preventDefault();

            const input = document.getElementById('manual-ticket-input');
            const code = input.value.trim();

            if (!code) {
                return;
            }

            isProcessing = true;
            showOverlay('loading', 'Validando ingresso...');
            sendValidation(code);
        });

        function playBeep(type) {
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

                if (type === 'scan') {
                    osc.frequency.setValueAtTime(600, audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.05);
                    return;
                }

                if (type === 'success') {
                    osc.frequency.setValueAtTime(800, audioCtx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.1);
                    gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.2);
                    return;
                }

                osc.type = 'square';
                osc.frequency.setValueAtTime(150, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.3);
            } catch (error) {
                console.warn('Falha ao tocar beep no admin:', error);
            }
        }
    </script>
@endpush
