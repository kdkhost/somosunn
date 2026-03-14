@extends('panel.layouts.app')

@section('title', 'Scanner de Ingressos')

@section('panel_breadcrumb')
    <a href="{{ route('panel.instructor.dashboard') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Instrutor</a>
    <span class="mx-1 text-slate-400">/</span>
    <span>Scanner</span>
@endsection

@section('panel_content')
<div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-qrcode text-blue-600"></i> Scanner Universal de Ingressos
            </h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1 font-medium">
                Valide qualquer ingresso dos seus eventos. A geocerca de cada evento e aplicada automaticamente.
            </p>
        </div>
        <a href="{{ route('panel.instructor.dashboard') }}"
            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm text-slate-700 dark:text-slate-300 px-4 py-2 rounded-xl hover:bg-slate-50 transition font-medium text-sm">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

        {{-- Scanner --}}
        <div class="xl:col-span-3 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6">

            <div id="reader-wrapper" class="relative rounded-xl overflow-hidden border border-gray-200 bg-gray-50 flex items-center justify-center min-h-[380px]">

                <div id="start-screen"
                    class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-gray-50 dark:bg-slate-900 p-8 text-center rounded-xl">
                    <div class="w-20 h-20 rounded-full bg-blue-600 text-white flex items-center justify-center text-3xl mb-4 shadow-lg shadow-blue-600/20">
                        <i class="fas fa-camera"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mb-1">Ativar Scanner</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 max-w-xs">
                        Clique para habilitar a camera. O GPS so e usado quando o evento exigir localizacao.
                    </p>
                    <button onclick="initializeScanner()" id="start-btn"
                        class="w-full max-w-xs py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="fas fa-play"></i> Ativar Scanner
                    </button>
                </div>

                <div id="reader" class="rounded-lg overflow-hidden w-full"></div>

                <div id="scanner-overlay"
                    class="absolute inset-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm z-10 hidden flex-col items-center justify-center p-6 text-center">
                    <div id="overlay-spinner" class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-4"></div>
                    <i id="overlay-icon-success" class="fas fa-check-circle text-6xl text-green-500 mb-4 hidden"></i>
                    <i id="overlay-icon-error" class="fas fa-times-circle text-6xl text-red-500 mb-4 hidden"></i>
                    <h3 id="overlay-title" class="text-2xl font-black text-slate-900 dark:text-white mb-1">Processando...</h3>
                    <p id="overlay-message" class="text-slate-600 dark:text-slate-400 mb-6 font-medium"></p>
                    <button id="btn-scan-again" onclick="resumeScanning()"
                        class="w-full max-w-xs bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition-colors hidden items-center justify-center gap-2">
                        <i class="fas fa-sync-alt"></i> Ler Proximo Ingresso
                    </button>
                </div>
            </div>

            <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800">
                <p class="text-sm font-bold text-slate-600 dark:text-slate-400 mb-3 text-center uppercase tracking-wider">Ou digite o codigo manualmente</p>
                <form id="manual-ticket-form" class="flex gap-2">
                    <input type="text" id="manual-ticket-input" placeholder="Ex: e47b-891a-..."
                        class="flex-1 rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:border-blue-500 focus:ring-blue-500 shadow-sm text-sm"
                        required>
                    <button type="submit"
                        class="bg-slate-800 dark:bg-slate-700 text-white px-5 py-2 rounded-xl font-bold hover:bg-slate-900 transition shadow text-sm">
                        Validar
                    </button>
                </form>
            </div>
        </div>

        {{-- Eventos disponíveis --}}
        <div class="xl:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-black text-slate-900 dark:text-white text-sm uppercase tracking-wider">Seus eventos (proximos 3 dias)</h3>
            </div>
            <div class="p-4 space-y-3 max-h-[480px] overflow-y-auto">
                @forelse($todayEvents as $event)
                    @php
                        $mode = $event->scannerRestrictionMode();
                        $modeLabel = $mode === \App\Models\Event::SCANNER_RESTRICTION_DISABLED
                            ? 'Sem restricao'
                            : ($mode === \App\Models\Event::SCANNER_RESTRICTION_EXACT ? 'Localizacao exata' : 'Raio ' . $event->scannerFormattedRadius());
                        $modeColor = $mode === \App\Models\Event::SCANNER_RESTRICTION_DISABLED
                            ? 'bg-slate-100 text-slate-600'
                            : ($mode === \App\Models\Event::SCANNER_RESTRICTION_EXACT ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700');
                    @endphp
                    <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white text-sm">{{ $event->title }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <span class="text-xs font-bold px-2 py-1 rounded-lg {{ $modeColor }} whitespace-nowrap">{{ $modeLabel }}</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">{{ $event->scannerLocationMessage() }}</p>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400">
                        <i class="fas fa-calendar-times text-3xl mb-3"></i>
                        <p class="text-sm font-medium">Nenhum evento com ingressos ativos nos proximos dias.</p>
                    </div>
                @endforelse
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
    let isStarting = false;
    let userCoords = { lat: null, lng: null };
    let audioCtx = null;

    async function initializeScanner() {
        if (isStarting) return;
        isStarting = true;

        const btn = document.getElementById('start-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando...';

        try {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') await audioCtx.resume();
        } catch (e) {}

        captureLocationInBackground();
        document.getElementById('start-screen').classList.add('hidden');

        try {
            await startScanner();
        } catch (e) {
            document.getElementById('start-screen').classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-play"></i> Ativar Scanner';
        }

        isStarting = false;
    }

    function captureLocationInBackground() {
        if (!('geolocation' in navigator)) return;
        navigator.geolocation.getCurrentPosition(
            pos => { userCoords.lat = pos.coords.latitude; userCoords.lng = pos.coords.longitude; },
            () => {},
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 15000 }
        );
    }

    async function startScanner() {
        if (html5QrCode) {
            try { await html5QrCode.stop(); } catch (e) {}
        }
        html5QrCode = new Html5Qrcode('reader');
        const config = { fps: 15, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };

        try {
            await html5QrCode.start({ facingMode: 'environment' }, config, onScanSuccess);
            return;
        } catch (e) {}

        const cameras = await Html5Qrcode.getCameras();
        if (!cameras.length) throw new Error('Nenhuma camera disponivel.');
        const cam = cameras.find(c => /back|rear|traseira|environment/i.test(c.label || '')) || cameras[0];
        await html5QrCode.start(cam.id, config, onScanSuccess);
    }

    function onScanSuccess(code) {
        if (isProcessing) return;
        isProcessing = true;
        showOverlay('process', 'Processando...', 'Validando ingresso no sistema...');

        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(
                pos => { userCoords.lat = pos.coords.latitude; userCoords.lng = pos.coords.longitude; sendValidation(code); },
                () => sendValidation(code),
                { enableHighAccuracy: true, timeout: 3000, maximumAge: 8000 }
            );
            return;
        }
        sendValidation(code);
    }

    function sendValidation(code) {
        fetch('{{ route('panel.instructor.scanner.validate') }}', {
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
                playBeep(true);
                if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                setTimeout(resumeScanning, 2500);
                return;
            }
            showOverlay('error', 'Entrada Negada', data.message || 'Ingresso invalido.');
            playBeep(false);
            if (navigator.vibrate) navigator.vibrate(500);
        })
        .catch(() => {
            showOverlay('error', 'Erro tecnico', 'Nao foi possivel validar o ingresso.');
            playBeep(false);
        });
    }

    function showOverlay(state, title, message) {
        const overlay = document.getElementById('scanner-overlay');
        const spinner = document.getElementById('overlay-spinner');
        const iconOk = document.getElementById('overlay-icon-success');
        const iconErr = document.getElementById('overlay-icon-error');
        const titleEl = document.getElementById('overlay-title');
        const msgEl = document.getElementById('overlay-message');
        const btn = document.getElementById('btn-scan-again');

        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        titleEl.innerText = title;
        msgEl.innerHTML = message;
        spinner.classList.add('hidden');
        iconOk.classList.add('hidden');
        iconErr.classList.add('hidden');
        btn.classList.add('hidden');
        btn.classList.remove('flex');

        if (state === 'process') { spinner.classList.remove('hidden'); return; }
        if (state === 'success') {
            iconOk.classList.remove('hidden');
            btn.classList.remove('hidden'); btn.classList.add('flex');
            return;
        }
        iconErr.classList.remove('hidden');
        btn.classList.remove('hidden'); btn.classList.add('flex');
    }

    function resumeScanning() {
        document.getElementById('scanner-overlay').classList.add('hidden');
        document.getElementById('scanner-overlay').classList.remove('flex');
        isProcessing = false;
    }

    document.getElementById('manual-ticket-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const code = document.getElementById('manual-ticket-input').value.trim();
        if (code) { isProcessing = true; showOverlay('process', 'Processando...', ''); sendValidation(code); }
    });

    function playBeep(success) {
        if (!audioCtx) return;
        try {
            if (audioCtx.state === 'suspended') audioCtx.resume();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain); gain.connect(audioCtx.destination);
            if (success) {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(800, audioCtx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.1);
                gain.gain.setValueAtTime(0.5, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.2);
                osc.start(); osc.stop(audioCtx.currentTime + 0.2);
            } else {
                osc.type = 'square';
                osc.frequency.setValueAtTime(300, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.5, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
                osc.start(); osc.stop(audioCtx.currentTime + 0.3);
            }
        } catch (e) {}
    }
</script>
@endpush
