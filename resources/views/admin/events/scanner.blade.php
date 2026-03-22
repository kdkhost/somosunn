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
    /* ─── Theme-aware tokens ─────────────────────────────────────────── */
    :root {
        --sc-bg:          #f1f5f9;
        --sc-card:        #ffffff;
        --sc-card-border: rgba(0,0,0,.08);
        --sc-text:        #1e293b;
        --sc-muted:       #64748b;
        --sc-reader-bg:   #f8fafc;
        --sc-overlay-bg:  rgba(255,255,255,.95);
        --sc-header-bg:   linear-gradient(135deg,#1f5edb,#1D3FC4);
    }
    body.dark-mode {
        --sc-bg:          #1a1f2e;
        --sc-card:        #252d3d;
        --sc-card-border: rgba(255,255,255,.07);
        --sc-text:        #e2e8f0;
        --sc-muted:       #94a3b8;
        --sc-reader-bg:   #1e2535;
        --sc-overlay-bg:  rgba(30,37,53,.95);
        --sc-header-bg:   linear-gradient(135deg,#1D3FC4,#0f1e6e);
    }

    /* ─── Layout ─────────────────────────────────────────────────────── */
    .sc-wrapper      { background: var(--sc-bg); min-height: 100%; padding: 1.5rem 0 3rem; }
    .sc-card         { background: var(--sc-card); border: 1px solid var(--sc-card-border);
                       border-radius: 1rem; overflow: hidden;
                       box-shadow: 0 4px 24px rgba(0,0,0,.07); }

    /* ─── Header ─────────────────────────────────────────────────────── */
    .sc-header       { background: var(--sc-header-bg); padding: 1.25rem 1.5rem;
                       display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
    .sc-header h3    { color: #fff; margin: 0; font-weight: 800; font-size: 1.1rem; }
    .sc-back-btn     { background: rgba(255,255,255,.15); color: #fff !important;
                       border: 1px solid rgba(255,255,255,.3); border-radius: .5rem;
                       padding: .4rem 1rem; font-weight: 700; font-size: .85rem;
                       text-decoration: none; transition: background .2s; white-space: nowrap; }
    .sc-back-btn:hover{ background: rgba(255,255,255,.28); }

    /* ─── Body ───────────────────────────────────────────────────────── */
    .sc-body         { padding: 1.5rem; color: var(--sc-text); }
    .sc-event-title  { font-size: 1.15rem; font-weight: 700; color: var(--sc-text); margin: 0 0 .25rem; }
    .sc-event-date   { font-size: .88rem; color: var(--sc-muted); }

    /* ─── Status badge ───────────────────────────────────────────────── */
    .sc-status       { display: inline-flex; align-items: center; gap: .45rem;
                       padding: .5rem 1.1rem; border-radius: 2rem; font-weight: 700;
                       font-size: .87rem; margin: 1rem 0; }
    .sc-status.open  { background: #d1fae5; color: #065f46; }
    .sc-status.closed{ background: #fee2e2; color: #991b1b; }
    body.dark-mode .sc-status.open  { background: rgba(16,185,129,.18); color: #6ee7b7; }
    body.dark-mode .sc-status.closed{ background: rgba(239, 68, 68,.18); color: #fca5a5; }

    /* ─── Info chips ─────────────────────────────────────────────────── */
    .sc-info-grid    { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; margin-bottom: 1.5rem; }
    @media(max-width:576px){ .sc-info-grid{ grid-template-columns: 1fr; } }
    .sc-info-chip    { background: var(--sc-reader-bg); border: 1px solid var(--sc-card-border);
                       border-radius: .75rem; padding: .75rem 1rem; font-size: .83rem; color: var(--sc-muted); }
    .sc-info-chip strong { display: block; color: var(--sc-text); font-weight: 700; margin-bottom: .2rem; }

    /* ─── Reader box ─────────────────────────────────────────────────── */
    .sc-reader-box   { background: var(--sc-reader-bg); border: 1px solid var(--sc-card-border);
                       border-radius: .875rem; overflow: hidden; position: relative; max-width: 500px; margin: 0 auto; }
    /* html5-qrcode dark-mode adjustments */
    body.dark-mode #reader         { filter: invert(0); }
    body.dark-mode #reader button  { background:#334155!important; color:#e2e8f0!important; border-color:#475569!important; }
    body.dark-mode #reader select  { background:#1e2535!important; color:#e2e8f0!important; border-color:#475569!important; }
    body.dark-mode #reader__scan_region { background: var(--sc-reader-bg)!important; }

    /* ─── Overlay ────────────────────────────────────────────────────── */
    .sc-overlay      { position: absolute; inset: 0; display: none; flex-direction: column;
                       align-items: center; justify-content: center; text-align: center;
                       padding: 1.5rem; background: var(--sc-overlay-bg);
                       border-radius: .875rem; z-index: 20; }
    .sc-overlay.is-visible { display: flex; }
    .sc-overlay-icon { width: 80px; height: 80px; border-radius: 50%;
                       display: flex; align-items: center; justify-content: center;
                       font-size: 2rem; margin-bottom: 1rem;
                       transition: transform .25s ease; }
    .sc-overlay-msg  { font-weight: 700; font-size: 1.05rem; color: var(--sc-text); margin: 0 0 .5rem; }
    .sc-overlay-sub  { font-size: .87rem; color: var(--sc-muted); }

    /* ─── Hint ───────────────────────────────────────────────────────── */
    .sc-hint         { text-align: center; font-size: .83rem; color: var(--sc-muted); margin-top: .75rem; }

    /* ─── Btn theme-aware ────────────────────────────────────────────── */
    .btn-sc-primary  { background: linear-gradient(135deg,#1F5EDB,#1D3FC4); color: #fff!important;
                       border: none; border-radius: .5rem; padding: .55rem 1.4rem;
                       font-weight: 700; transition: opacity .2s; }
    .btn-sc-primary:hover { opacity: .88; }
    .btn-sc-outline  { background: transparent; color: var(--sc-text)!important;
                       border: 1.5px solid var(--sc-card-border); border-radius: .5rem;
                       padding: .5rem 1.2rem; font-weight: 700; transition: background .2s; }
    .btn-sc-outline:hover { background: var(--sc-reader-bg); }
</style>
@endpush

@section('content')
<div class="sc-wrapper">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-8">
                <div class="sc-card">

                    {{-- ── Header ── --}}
                    <div class="sc-header">
                        <h3>
                            <i class="fas fa-qrcode mr-2"></i>Scanner de Ingressos
                        </h3>
                        <a href="{{ route('admin.events.show', $event) }}" class="sc-back-btn">
                            <i class="fas fa-arrow-left mr-1"></i>Voltar ao Evento
                        </a>
                    </div>

                    {{-- ── Body ── --}}
                    <div class="sc-body">

                        {{-- Event info --}}
                        <p class="sc-event-title">{{ $event->title }}</p>
                        <p class="sc-event-date">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y \à\s H:i') }}
                        </p>

                        {{-- Status --}}
                        <div class="sc-status {{ $scannerOpen ? 'open' : 'closed' }}">
                            <i class="fas {{ $scannerOpen ? 'fa-check-circle' : 'fa-ban' }}"></i>
                            {{ $scannerStatusMessage }}
                        </div>

                        {{-- Info chips --}}
                        <div class="sc-info-grid">
                            <div class="sc-info-chip">
                                <strong><i class="fas fa-map-marker-alt mr-1 text-primary"></i>Regra de localização</strong>
                                {{ $event->scannerLocationMessage() }}
                            </div>
                            <div class="sc-info-chip">
                                <strong><i class="fas fa-shield-alt mr-1 text-success"></i>Auditoria</strong>
                                Toda tentativa de validação fica registrada no sistema.
                            </div>
                        </div>

                        {{-- Reader --}}
                        <div class="sc-reader-box">
                            <div id="reader" style="width:100%;"></div>

                            {{-- Overlay --}}
                            <div id="scanner-overlay" class="sc-overlay">
                                <div class="spinner-border text-primary mb-3" id="overlay-spinner" role="status" style="width:2.5rem;height:2.5rem;"></div>
                                <div id="overlay-icon-wrap" class="sc-overlay-icon d-none">
                                    <i id="overlay-icon" class="fas fa-check"></i>
                                </div>
                                <p id="overlay-message" class="sc-overlay-msg">Analisando...</p>
                                <p id="overlay-sub" class="sc-overlay-sub"></p>
                                <button id="btn-scan-again" class="btn btn-sc-primary mt-2 d-none" onclick="resumeScanning()">
                                    <i class="fas fa-sync-alt mr-1"></i>Ler próximo ingresso
                                </button>
                            </div>
                        </div>

                        @if($scannerOpen)
                        <p class="sc-hint mt-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Aponte a câmera para o QR Code do ingresso.
                        </p>
                        @endif

                    </div>{{-- /sc-body --}}
                </div>{{-- /sc-card --}}
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
    let userCoords = { lat: null, lng: null };
    const scannerOpen           = @json($scannerOpen);
    const scannerStatusMessage  = @json($scannerStatusMessage);
    const requiresLocation      = @json($event->hasScannerLocationConstraint());
    const scannerLocationMsg    = @json($event->scannerLocationMessage());

    document.addEventListener('DOMContentLoaded', function () {
        if (!scannerOpen) return;
        if (requiresLocation) captureUserLocationInBackground();
        startScanner();
    });

    /* ── GPS ── */
    function captureUserLocationInBackground() {
        if (!('geolocation' in navigator)) return;
        navigator.geolocation.getCurrentPosition(
            p => { userCoords.lat = p.coords.latitude; userCoords.lng = p.coords.longitude; },
            e => console.warn('GPS indisponível:', e),
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 15000 }
        );
    }

    /* ── Scanner ── */
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

    /* ── Callbacks ── */
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
            if (requiresLocation && (userCoords.lat === null || userCoords.lng === null)) {
                toastr.warning(scannerLocationMsg + ' Habilite o GPS.');
            }
        })
        .catch(() => {
            showOverlay('error', 'Erro de comunicação com o servidor.');
            toastr.error('Erro de comunicação com o servidor.');
            playBeep(false);
        });
    }

    /* ── Overlay ── */
    function showOverlay(state, message, sub) {
        const overlay  = document.getElementById('scanner-overlay');
        const spinner  = document.getElementById('overlay-spinner');
        const iconWrap = document.getElementById('overlay-icon-wrap');
        const icon     = document.getElementById('overlay-icon');
        const msgEl    = document.getElementById('overlay-message');
        const subEl    = document.getElementById('overlay-sub');
        const btn      = document.getElementById('btn-scan-again');

        overlay.classList.add('is-visible');
        msgEl.textContent = message;
        subEl.textContent = sub || '';

        if (state === 'process') {
            spinner.classList.remove('d-none');
            iconWrap.classList.add('d-none');
            btn.classList.add('d-none');
            return;
        }

        spinner.classList.add('d-none');
        iconWrap.classList.remove('d-none');
        btn.classList.remove('d-none');

        if (state === 'success') {
            iconWrap.style.cssText = 'background:#d1fae5;color:#059669;';
            icon.className = 'fas fa-check';
            return;
        }

        iconWrap.style.cssText = 'background:#fee2e2;color:#dc2626;';
        icon.className = 'fas fa-times';
    }

    function resumeScanning() {
        document.getElementById('scanner-overlay').classList.remove('is-visible');
        isProcessing = false;
        if (html5QrcodeScanner) html5QrcodeScanner.resume();
    }

    /* ── Beep ── */
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
