@extends('admin.layouts.app')

@section('title', 'Scanner: ' . $event->title)
@section('page_title', 'Scanner de Ingressos')

@section('breadcrumb_items')
    <li class="breadcrumb-item"><a href="{{ route('admin.events.list') }}">Eventos</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.events.show', $event) }}">{{ Str::limit($event->title, 30) }}</a></li>
    <li class="breadcrumb-item active">Scanner</li>
@endsection

@push('styles')
<style>
    /* Minimal dark-mode fixes for html5-qrcode internal UI */
    body.dark-mode #reader button  { background: #374151 !important; color: #e5e7eb !important; border-color: #4b5563 !important; }
    body.dark-mode #reader select  { background: #1f2937 !important; color: #e5e7eb !important; border-color: #4b5563 !important; }
    body.dark-mode #reader__scan_region img { filter: invert(1) !important; }

    /* Overlay inside the reader box */
    .reader-wrapper { position: relative; min-height: 300px; }
    .scanner-overlay {
        position: absolute; inset: 0;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        background: rgba(255,255,255,.94);
        z-index: 20;
        border-radius: .25rem;
        padding: 1.5rem;
    }
    body.dark-mode .scanner-overlay { background: rgba(30,37,53,.94); }
    .scanner-overlay.is-visible { display: flex; }

    .overlay-icon-wrap {
        width: 72px; height: 72px;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: .75rem;
    }
    .overlay-icon-wrap.is-visible { display: flex; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            {{-- ── Main card (AdminLTE style) ── --}}
            <div class="card card-primary card-outline shadow-sm">

                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-qrcode mr-2"></i>{{ $event->title }}
                    </h3>
                    <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Voltar ao Evento
                    </a>
                </div>

                <div class="card-body">

                    {{-- Date --}}
                    <p class="text-muted mb-3">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y \à\s H:i') }}
                    </p>

                    {{-- Scanner status --}}
                    <div class="alert {{ $scannerOpen ? 'alert-success' : 'alert-danger' }} mb-3">
                        <i class="fas {{ $scannerOpen ? 'fa-check-circle' : 'fa-ban' }} mr-1"></i>
                        <strong>{{ $scannerStatusMessage }}</strong>
                    </div>

                    {{-- Info row --}}
                    <div class="row mb-4">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <div class="alert alert-info h-100 mb-0">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                <strong>Regra de localização:</strong><br>
                                {{ $event->scannerLocationMessage() }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-secondary h-100 mb-0">
                                <i class="fas fa-shield-alt mr-1"></i>
                                <strong>Auditoria:</strong><br>
                                Toda tentativa de validação fica registrada no sistema.
                            </div>
                        </div>
                    </div>

                    {{-- QR Reader --}}
                    <div class="reader-wrapper border rounded p-2 bg-light mb-3">
                        <div id="reader" style="width:100%;"></div>

                        {{-- Result overlay --}}
                        <div id="scanner-overlay" class="scanner-overlay">
                            <div class="spinner-border text-primary mb-3" id="overlay-spinner"
                                 role="status" style="width:2.4rem;height:2.4rem;"></div>

                            <div id="overlay-icon-wrap" class="overlay-icon-wrap">
                                <i id="overlay-icon" class="fas fa-check"></i>
                            </div>

                            <h5 id="overlay-message" class="font-weight-bold mb-1">Analisando...</h5>
                            <p  id="overlay-sub" class="text-muted mb-3 small"></p>

                            <button id="btn-scan-again" class="btn btn-primary d-none" onclick="resumeScanning()">
                                <i class="fas fa-sync-alt mr-1"></i>Ler próximo ingresso
                            </button>
                        </div>
                    </div>

                    @if($scannerOpen)
                    <div class="alert alert-info text-center mb-0">
                        <i class="fas fa-info-circle mr-1"></i>
                        Aponte a câmera para o QR Code do ingresso.
                    </div>
                    @endif

                </div>{{-- /card-body --}}
            </div>{{-- /card --}}

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let html5QrcodeScanner = null;
    let isProcessing        = false;
    let userCoords          = { lat: null, lng: null };

    const scannerOpen          = @json($scannerOpen);
    const scannerStatusMessage = @json($scannerStatusMessage);
    const requiresLocation     = @json($event->hasScannerLocationConstraint());
    const scannerLocationMsg   = @json($event->scannerLocationMessage());

    document.addEventListener('DOMContentLoaded', function () {
        if (!scannerOpen) return;
        if (requiresLocation) captureUserLocationInBackground();
        startScanner();
    });

    /* ── GPS ─────────────────────────────────────────── */
    function captureUserLocationInBackground() {
        if (!('geolocation' in navigator)) return;
        navigator.geolocation.getCurrentPosition(
            p => { userCoords.lat = p.coords.latitude; userCoords.lng = p.coords.longitude; },
            e => console.warn('GPS indisponível:', e),
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 15000 }
        );
    }

    /* ── Scanner ──────────────────────────────────────── */
    function startScanner() {
        if (!scannerOpen) { toastr.error(scannerStatusMessage); return; }
        if (html5QrcodeScanner) html5QrcodeScanner.clear();

        html5QrcodeScanner = new Html5QrcodeScanner(
            'reader',
            { fps: 10, qrbox: { width: 250, height: 250 } },
            false
        );
        html5QrcodeScanner.render(onScanSuccess, () => {});
    }

    /* ── Scan callback ────────────────────────────────── */
    function onScanSuccess(decodedText) {
        if (isProcessing) return;
        isProcessing = true;
        html5QrcodeScanner.pause(true);
        showOverlay('process', 'Validando ingresso...');

        if (requiresLocation && 'geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(
                p => { userCoords.lat = p.coords.latitude; userCoords.lng = p.coords.longitude; sendValidation(decodedText); },
                ()  => sendValidation(decodedText),
                { enableHighAccuracy: true, timeout: 3000, maximumAge: 8000 }
            );
            return;
        }
        sendValidation(decodedText);
    }

    /* ── Validation request ───────────────────────────── */
    function sendValidation(code) {
        fetch('{{ route('admin.events.scanner.validate', $event) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ticket_code: code, latitude: userCoords.lat, longitude: userCoords.lng })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showOverlay('success', 'Entrada Liberada!', data.participant_name || '');
                toastr.success(data.message);
                playBeep(true);
                return;
            }
            showOverlay('error', data.message || 'Ingresso inválido.');
            toastr.error(data.message);
            playBeep(false);
            if (requiresLocation && userCoords.lat === null) {
                toastr.warning(scannerLocationMsg + ' Habilite o GPS.');
            }
        })
        .catch(() => {
            showOverlay('error', 'Erro de comunicação com o servidor.');
            toastr.error('Erro de comunicação com o servidor.');
            playBeep(false);
        });
    }

    /* ── Overlay helpers ──────────────────────────────── */
    function showOverlay(state, message, sub) {
        const overlay   = document.getElementById('scanner-overlay');
        const spinner   = document.getElementById('overlay-spinner');
        const iconWrap  = document.getElementById('overlay-icon-wrap');
        const icon      = document.getElementById('overlay-icon');
        const msgEl     = document.getElementById('overlay-message');
        const subEl     = document.getElementById('overlay-sub');
        const btn       = document.getElementById('btn-scan-again');

        overlay.classList.add('is-visible');
        msgEl.textContent = message;
        subEl.textContent = sub || '';

        if (state === 'process') {
            spinner.style.display = 'block';
            iconWrap.classList.remove('is-visible');
            btn.classList.add('d-none');
            msgEl.className = 'font-weight-bold mb-1 text-primary';
            return;
        }

        spinner.style.display = 'none';
        iconWrap.classList.add('is-visible');
        btn.classList.remove('d-none');

        if (state === 'success') {
            iconWrap.style.cssText = 'display:flex;background:#d1fae5;color:#059669;';
            icon.className = 'fas fa-check';
            msgEl.className = 'font-weight-bold mb-1 text-success';
            return;
        }

        iconWrap.style.cssText = 'display:flex;background:#fee2e2;color:#dc2626;';
        icon.className = 'fas fa-times';
        msgEl.className = 'font-weight-bold mb-1 text-danger';
    }

    function resumeScanning() {
        document.getElementById('scanner-overlay').classList.remove('is-visible');
        isProcessing = false;
        if (html5QrcodeScanner) html5QrcodeScanner.resume();
    }

    /* ── Beep ─────────────────────────────────────────── */
    function playBeep(success) {
        try {
            const ctx  = new (window.AudioContext || window.webkitAudioContext)();
            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            if (success) {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(800, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + .1);
                gain.gain.setValueAtTime(.5, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(.01, ctx.currentTime + .2);
                osc.start(); osc.stop(ctx.currentTime + .2);
            } else {
                osc.type = 'square';
                osc.frequency.setValueAtTime(300, ctx.currentTime);
                gain.gain.setValueAtTime(.4, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(.01, ctx.currentTime + .3);
                osc.start(); osc.stop(ctx.currentTime + .3);
            }
        } catch(e) {}
    }
</script>
@endpush
