@extends('layouts.app')

@section('title', $magazine->title . ' - Revista')

@push('styles')
<style>
    /* Forcar modo claro para o viewer (evita inversao de cores pelo browser) */
    :root { color-scheme: light; }
    .mag-viewer, .mag-viewer *, #mag-flipbook-container, .mag-page, .mag-page img {
        color-scheme: light !important;
        forced-color-adjust: none !important;
    }
    .mag-page img {
        filter: none !important;
        background: #fff !important;
    }

    .mag-viewer {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background:
            radial-gradient(ellipse at 20% 10%, rgba(139, 92, 246, 0.04), transparent 55%),
            radial-gradient(ellipse at 80% 90%, rgba(79, 70, 229, 0.05), transparent 60%),
            linear-gradient(180deg, rgb(240, 240, 240) 0%, rgb(220, 220, 225) 100%);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Header */
    .mag-header {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        z-index: 10;
        padding: 0.75rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(180deg, rgba(255,255,255,0.9) 0%, transparent 100%);
        pointer-events: none;
    }
    .mag-header > * { pointer-events: auto; }
    .mag-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: rgba(15, 23, 42, 0.85);
        font-weight: 700;
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        border-radius: 999px;
        background: rgba(255,255,255,0.8);
        backdrop-filter: blur(8px);
        text-decoration: none;
        transition: all 0.2s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .mag-back:hover { background: #fff; color: #0f172a; }
    .mag-info { text-align: right; }
    .mag-info-cat {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-weight: 900;
        color: rgb(124, 58, 237);
    }
    .mag-info-title {
        font-size: 0.95rem;
        font-weight: 900;
        color: #0f172a;
        max-width: 350px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Canvas area */
    .mag-canvas {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem 1rem 6rem;
        perspective: 2000px;
        position: relative;
        min-height: 0;
    }
    .mag-canvas::after {
        content: '';
        position: absolute;
        bottom: 5rem;
        left: 50%;
        transform: translateX(-50%);
        width: 70%;
        height: 40px;
        background: radial-gradient(ellipse, rgba(0, 0, 0, 0.25) 0%, transparent 70%);
        filter: blur(20px);
        pointer-events: none;
        z-index: 1;
    }
    #mag-flipbook-container {
        box-shadow:
            0 30px 60px -15px rgba(0, 0, 0, 0.35),
            0 15px 30px -10px rgba(0, 0, 0, 0.2),
            0 0 0 1px rgba(0, 0, 0, 0.04);
        border-radius: 4px;
        background: #fff;
        position: relative;
        z-index: 2;
    }
    .mag-page {
        background: #fff;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .mag-page img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: contain;
        user-select: none;
        -webkit-user-drag: none;
    }

    /* Loading */
    .mag-loading {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.25rem;
        color: #475569;
        z-index: 2;
    }
    .mag-loader-wrap {
        position: relative;
        width: 80px;
        height: 80px;
    }
    .mag-loader {
        width: 80px; height: 80px;
        border: 4px solid rgba(31, 94, 219, 0.15);
        border-top-color: #1F5EDB;
        border-right-color: #177FD6;
        border-radius: 50%;
        animation: magspin 0.9s linear infinite;
        position: absolute;
        inset: 0;
    }
    .mag-loader-logo {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: contain;
    }
    @keyframes magspin { to { transform: rotate(360deg); } }
    .mag-loading-label {
        font-size: 0.875rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #1e293b;
    }
    .mag-loading-progress {
        font-size: 0.75rem;
        color: rgba(100, 116, 139, 0.8);
        font-variant-numeric: tabular-nums;
    }
    .mag-loading-bar {
        width: 200px;
        height: 3px;
        background: rgba(31, 94, 219, 0.15);
        border-radius: 999px;
        overflow: hidden;
    }
    .mag-loading-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #1F5EDB, #177FD6);
        width: 0%;
        transition: width 0.3s ease;
    }

    /* Side arrows (custom) */
    .mag-arrow {
        position: fixed;
        top: 50%;
        transform: translateY(-50%);
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.95);
        color: #475569;
        font-size: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10000;
        border: 1px solid rgba(0, 0, 0, 0.08);
        opacity: 0.95;
        transition: opacity 0.2s, background 0.2s, color 0.2s;
        backdrop-filter: blur(6px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    #mag-arrow-prev { left: 20px; }
    #mag-arrow-next { right: 20px; }
    .mag-arrow:hover {
        opacity: 1;
        background: #fff;
        color: #7c3aed;
    }
    .mag-arrow:disabled { opacity: 0.35; cursor: not-allowed; pointer-events: none; }

    /* Toolbar bottom */
    .mag-toolbar {
        position: absolute;
        bottom: 1.5rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.5rem;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(24px);
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 999px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        z-index: 10;
    }
    .mag-btn {
        width: 42px;
        height: 42px;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #475569;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        transition: all 0.15s;
        text-decoration: none;
    }
    .mag-btn:hover {
        background: rgba(168, 85, 247, 0.12);
        color: #7c3aed;
    }
    .mag-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
        pointer-events: none;
    }
    .mag-btn.is-active { background: rgba(168, 85, 247, 0.15); color: #7c3aed; }
    .mag-pages {
        padding: 0 0.75rem;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 900;
        font-variant-numeric: tabular-nums;
        letter-spacing: 1px;
        min-width: 80px;
        text-align: center;
    }
    .mag-pages strong { color: #0f172a; }
    .mag-divider {
        width: 1px;
        height: 24px;
        background: rgba(0, 0, 0, 0.08);
        margin: 0 0.25rem;
    }

    /* Error */
    .mag-error {
        text-align: center;
        color: #fecaca;
        max-width: 400px;
        padding: 2rem;
        z-index: 2;
    }
    .mag-error i {
        font-size: 3rem;
        color: #f87171;
        margin-bottom: 1rem;
    }

    /* Hide site chrome */
    body.mag-viewer-active > header,
    body.mag-viewer-active > footer,
    body.mag-viewer-active > nav,
    body.mag-viewer-active .site-header,
    body.mag-viewer-active .site-footer,
    body.mag-viewer-active .navbar,
    body.mag-viewer-active .site-back-to-top,
    body.mag-viewer-active #pwa-install-modal {
        display: none !important;
    }
    body.mag-viewer-active main {
        padding: 0 !important;
        margin: 0 !important;
        min-height: 100vh !important;
    }
    body.mag-viewer-active { overflow: hidden; }

    @media (max-width: 768px) {
        .mag-header { padding: 0.75rem 1rem; }
        .mag-info-title { font-size: 0.85rem; max-width: 200px; }
        .mag-canvas { padding: 3.5rem 0.5rem 5rem; }
        .mag-btn { width: 36px; height: 36px; }
        .mag-pages { font-size: 0.7rem; padding: 0 0.5rem; min-width: 60px; }
        .mag-arrow { width: 44px; height: 44px; font-size: 20px; }
        #mag-arrow-prev { left: 8px; }
        #mag-arrow-next { right: 8px; }
    }
</style>
@endpush

@section('content')
<div class="mag-viewer" id="mag-viewer">

    {{-- Header --}}
    <div class="mag-header">
        <a href="{{ route('magazines.index') }}" class="mag-back">
            <i class="fas fa-arrow-left"></i> <span>Voltar</span>
        </a>
        <div class="mag-info">
            <div class="mag-info-cat">{{ $magazine->category ?: 'Revista' }}</div>
            <div class="mag-info-title">{{ $magazine->title }}</div>
        </div>
    </div>

    {{-- Canvas --}}
    <div class="mag-canvas" id="mag-canvas">
        <div class="mag-loading" id="mag-loading">
            <div class="mag-loader-wrap">
                <div class="mag-loader"></div>
                <img src="{{ \App\Models\Setting::getUrl('favicon_image') ?: \App\Models\Setting::getUrl('logo_image') ?: asset('img/logo.svg') }}" alt="UNN" class="mag-loader-logo">
            </div>
            <div class="mag-loading-label">Carregando revista</div>
            <div class="mag-loading-bar"><div class="mag-loading-bar-fill" id="mag-loading-bar-fill"></div></div>
            <div class="mag-loading-progress" id="mag-loading-progress">Preparando...</div>
        </div>
        <div id="mag-flipbook-container" style="display:none;"></div>
        <div class="mag-error" id="mag-error" style="display:none;">
            <i class="fas fa-exclamation-triangle"></i>
            <h3 style="color:#fff;margin-bottom:0.5rem;font-weight:900;">Nao foi possivel carregar</h3>
            <p id="mag-error-msg" style="font-size:0.85rem;opacity:0.7;"></p>
        </div>
    </div>

    {{-- Side arrows --}}
    <button type="button" class="mag-arrow" id="mag-arrow-prev" aria-label="Pagina anterior" style="display:none;">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button type="button" class="mag-arrow" id="mag-arrow-next" aria-label="Proxima pagina" style="display:none;">
        <i class="fas fa-chevron-right"></i>
    </button>

    {{-- Toolbar --}}
    <div class="mag-toolbar" id="mag-toolbar" style="display:none;">
        <button id="mag-first" class="mag-btn" title="Primeira pagina"><i class="fas fa-angles-left"></i></button>
        <button id="mag-prev" class="mag-btn" title="Pagina anterior"><i class="fas fa-chevron-left"></i></button>

        <div class="mag-pages"><strong id="mag-current">1</strong> / <span id="mag-total">?</span></div>

        <button id="mag-next" class="mag-btn" title="Proxima pagina"><i class="fas fa-chevron-right"></i></button>
        <button id="mag-last" class="mag-btn" title="Ultima pagina"><i class="fas fa-angles-right"></i></button>

        <div class="mag-divider"></div>

        <button id="mag-sound" class="mag-btn is-active" title="Som de pagina virando"><i id="mag-sound-icon" class="fas fa-volume-high"></i></button>
        <button id="mag-zoom" class="mag-btn" title="Ampliar pagina"><i id="mag-zoom-icon" class="fas fa-magnifying-glass-plus"></i></button>
        @if($magazine->allow_download)
            <a href="{{ $magazine->pdf_url }}" download class="mag-btn" title="Baixar PDF"><i class="fas fa-download"></i></a>
        @endif
        <button id="mag-fullscreen" class="mag-btn" title="Tela cheia"><i class="fas fa-expand"></i></button>
    </div>
</div>

{{-- PDF.js + StPageFlip --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js"></script>
<script>
(function () {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    var PDF_URL       = @json($magazine->pdf_url);
    var SOUND_ENABLED = @json((bool) $magazine->enable_sound);

    var stage       = document.getElementById('mag-viewer');
    var loadingEl   = document.getElementById('mag-loading');
    var barFill     = document.getElementById('mag-loading-bar-fill');
    var progressEl  = document.getElementById('mag-loading-progress');
    var container   = document.getElementById('mag-flipbook-container');
    var toolbar     = document.getElementById('mag-toolbar');
    var errorEl     = document.getElementById('mag-error');
    var errorMsg    = document.getElementById('mag-error-msg');
    var currentEl   = document.getElementById('mag-current');
    var totalEl     = document.getElementById('mag-total');
    var btnPrev     = document.getElementById('mag-prev');
    var btnNext     = document.getElementById('mag-next');
    var btnFirst    = document.getElementById('mag-first');
    var btnLast     = document.getElementById('mag-last');
    var btnSound    = document.getElementById('mag-sound');
    var btnSoundIcn = document.getElementById('mag-sound-icon');
    var btnFull     = document.getElementById('mag-fullscreen');
    var btnZoom     = document.getElementById('mag-zoom');
    var btnZoomIcn  = document.getElementById('mag-zoom-icon');
    var arrowPrev   = document.getElementById('mag-arrow-prev');
    var arrowNext   = document.getElementById('mag-arrow-next');

    document.body.classList.add('mag-viewer-active');

    var soundOn = SOUND_ENABLED;
    var audioCtx = null;
    var pageFlip = null;
    var renderedPages = new Set();

    function updateSoundIcon() {
        btnSoundIcn.className = soundOn ? 'fas fa-volume-high' : 'fas fa-volume-xmark';
        btnSound.classList.toggle('is-active', soundOn);
    }
    updateSoundIcon();
    btnSound.addEventListener('click', function() { soundOn = !soundOn; updateSoundIcon(); });

    // Realistic page-flip sound (3 phases)
    function playPageSound() {
        if (!soundOn) return;
        try {
            if (!audioCtx) {
                var AC = window.AudioContext || window.webkitAudioContext;
                if (!AC) return;
                audioCtx = new AC();
            }
            if (audioCtx.state === 'suspended') audioCtx.resume();

            var now = audioCtx.currentTime;
            var sr = audioCtx.sampleRate;
            var master = audioCtx.createGain();
            master.gain.value = 0.55;
            master.connect(audioCtx.destination);

            // Phase 1: Woosh
            var wooshDur = 0.09;
            var wooshBuf = audioCtx.createBuffer(1, Math.floor(sr * wooshDur), sr);
            var wd = wooshBuf.getChannelData(0);
            for (var i = 0; i < wd.length; i++) {
                wd[i] = (Math.random() * 2 - 1) * (1 - (i / wd.length) * 0.4);
            }
            var woosh = audioCtx.createBufferSource();
            woosh.buffer = wooshBuf;
            var wf = audioCtx.createBiquadFilter();
            wf.type = 'bandpass';
            wf.Q.value = 1.5;
            wf.frequency.setValueAtTime(2200, now);
            wf.frequency.linearRampToValueAtTime(4200, now + wooshDur);
            var wg = audioCtx.createGain();
            wg.gain.setValueAtTime(0.001, now);
            wg.gain.exponentialRampToValueAtTime(0.5, now + 0.015);
            wg.gain.exponentialRampToValueAtTime(0.001, now + wooshDur);
            woosh.connect(wf).connect(wg).connect(master);
            woosh.start(now);

            // Phase 2: Paper crinkle
            var crinkles = 7 + Math.floor(Math.random() * 3);
            for (var j = 0; j < crinkles; j++) {
                var startOffset = 0.03 + Math.random() * 0.19;
                var startTime = now + startOffset;
                var burstDur = 0.012 + Math.random() * 0.025;
                var burstBuf = audioCtx.createBuffer(1, Math.floor(sr * burstDur), sr);
                var bd = burstBuf.getChannelData(0);
                for (var k = 0; k < bd.length; k++) bd[k] = Math.random() * 2 - 1;
                var burst = audioCtx.createBufferSource();
                burst.buffer = burstBuf;
                var bf = audioCtx.createBiquadFilter();
                bf.type = 'highpass';
                bf.frequency.value = 3000 + Math.random() * 3500;
                bf.Q.value = 0.7;
                var bg = audioCtx.createGain();
                var peak = 0.12 + Math.random() * 0.18;
                bg.gain.setValueAtTime(0.001, startTime);
                bg.gain.exponentialRampToValueAtTime(peak, startTime + 0.002);
                bg.gain.exponentialRampToValueAtTime(0.001, startTime + burstDur);
                burst.connect(bf).connect(bg).connect(master);
                burst.start(startTime);
            }

            // Phase 3: Thwap
            var clickTime = now + 0.21;
            var clickBuf = audioCtx.createBuffer(1, Math.floor(sr * 0.02), sr);
            var cd = clickBuf.getChannelData(0);
            for (var m = 0; m < cd.length; m++) {
                cd[m] = (Math.random() * 2 - 1) * Math.exp(-m / (cd.length * 0.25));
            }
            var click = audioCtx.createBufferSource();
            click.buffer = clickBuf;
            var cf = audioCtx.createBiquadFilter();
            cf.type = 'bandpass';
            cf.frequency.value = 1400;
            cf.Q.value = 2;
            var cg = audioCtx.createGain();
            cg.gain.value = 0.15;
            click.connect(cf).connect(cg).connect(master);
            click.start(clickTime);
        } catch (e) { /* silent */ }
    }

    // Render a PDF page — returns 1 or 2 images depending if it's a spread
    // Pages with aspect > 1.15 (landscape) are detected as spreads and split in half
    async function renderPage(pdf, pdfPageNum, targetCssWidth, half) {
        var page = await pdf.getPage(pdfPageNum);
        var vp1 = page.getViewport({ scale: 1 });
        var dpr = Math.min(2, window.devicePixelRatio || 1); // Ate 2x para texto nitido no zoom

        var fullWidth = vp1.width;
        var fullHeight = vp1.height;

        // If no half specified, render the full page
        if (!half || half === 'full') {
            var scale = (targetCssWidth * dpr) / fullWidth;
            var viewport = page.getViewport({ scale: scale });
            var canvas = document.createElement('canvas');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            var ctx = canvas.getContext('2d');
            // Fundo branco explicito para evitar inversao de cores em dark mode
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            await page.render({
                canvasContext: ctx,
                viewport: viewport,
                background: '#ffffff',
                intent: 'display'
            }).promise;
            return canvas.toDataURL('image/jpeg', 0.88);
        }

        // Split mode: render full page then crop half
        var halfWidth = fullWidth / 2;
        var scale2 = (targetCssWidth * dpr) / halfWidth;
        var viewport2 = page.getViewport({ scale: scale2 });

        var canvas2 = document.createElement('canvas');
        canvas2.width = Math.floor(viewport2.width / 2);
        canvas2.height = viewport2.height;
        var ctx2 = canvas2.getContext('2d');
        // Fundo branco explicito
        ctx2.fillStyle = '#ffffff';
        ctx2.fillRect(0, 0, canvas2.width, canvas2.height);

        // Translate context to show only left or right half
        if (half === 'right') {
            ctx2.translate(-viewport2.width / 2, 0);
        }
        await page.render({
            canvasContext: ctx2,
            viewport: viewport2,
            background: '#ffffff',
            intent: 'display'
        }).promise;
        return canvas2.toDataURL('image/jpeg', 0.88);
    }

    // Detect which PDF pages are spreads (landscape aspect > 1.15)
    async function analyzePdfPages(pdf) {
        var map = []; // each entry: { pdfPage: N, half: 'full'|'left'|'right' }
        for (var p = 1; p <= pdf.numPages; p++) {
            var page = await pdf.getPage(p);
            var vp = page.getViewport({ scale: 1 });
            var aspectRatio = vp.width / vp.height;

            if (aspectRatio > 1.15) {
                // Spread page — split in two
                map.push({ pdfPage: p, half: 'left' });
                map.push({ pdfPage: p, half: 'right' });
            } else {
                map.push({ pdfPage: p, half: 'full' });
            }
        }
        return map;
    }

    function computeBookSize(pageAspect) {
        var canvas = document.getElementById('mag-canvas');
        var rect = canvas.getBoundingClientRect();
        var availableW = rect.width - 140; // espaco para setas laterais
        // Subtrair: toolbar (~70px) + espaco extra
        var availableH = rect.height - 40;
        var isMobile = window.innerWidth < 768;

        // Modo single page (1 pagina por vez)
        var maxW = isMobile ? (window.innerWidth - 20) : 720;
        var w = Math.min(availableW, maxW);
        var h = w * pageAspect;
        if (h > availableH) {
            h = availableH;
            w = h / pageAspect;
        }
        return { width: Math.floor(w), height: Math.floor(h), isMobile: isMobile };
    }

    function placeholderSvg(w, h) {
        return 'data:image/svg+xml,' + encodeURIComponent(
            '<svg xmlns="http://www.w3.org/2000/svg" width="' + w + '" height="' + h + '">' +
            '<rect fill="#f1f5f9" width="100%" height="100%"/>' +
            '<text x="50%" y="50%" text-anchor="middle" fill="#94a3b8" font-size="14" font-family="sans-serif">Carregando...</text>' +
            '</svg>'
        );
    }

    async function init() {
        try {
            var loadingTask = pdfjsLib.getDocument({
                url: PDF_URL,
                cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                cMapPacked: true,
            });
            loadingTask.onProgress = function(p) {
                if (p.total > 0) {
                    var pct = Math.round((p.loaded / p.total) * 100);
                    barFill.style.width = Math.min(85, pct) + '%';
                    progressEl.textContent = 'Baixando PDF... ' + pct + '%';
                }
            };
            var pdf = await loadingTask.promise;

            // Analyze all pages to detect spreads
            progressEl.textContent = 'Analisando paginas...';
            barFill.style.width = '90%';
            var pageMap = await analyzePdfPages(pdf);
            var totalPages = pageMap.length; // may differ from pdf.numPages if there are spreads
            totalEl.textContent = totalPages;

            // Use first entry aspect (assume most pages share same aspect)
            var firstPage = await pdf.getPage(pageMap[0].pdfPage);
            var fvp = firstPage.getViewport({ scale: 1 });
            var pageAspect;
            if (pageMap[0].half === 'full') {
                pageAspect = fvp.height / fvp.width;
            } else {
                // Half of a spread
                pageAspect = fvp.height / (fvp.width / 2);
            }

            var sizes = computeBookSize(pageAspect);
            var pageW = sizes.width;
            var pageH = sizes.height;

            progressEl.textContent = 'Preparando paginas...';
            barFill.style.width = '95%';

            // Build placeholders for all (flipbook) pages
            container.innerHTML = '';
            container.style.display = 'block';
            container.style.width = pageW + 'px';
            container.style.height = pageH + 'px';

            var placeholder = placeholderSvg(pageW, pageH);
            var pageElements = [];
            for (var i = 0; i < totalPages; i++) {
                var div = document.createElement('div');
                div.className = 'mag-page';
                var img = document.createElement('img');
                img.src = placeholder;
                img.draggable = false;
                img.dataset.pageIndex = i;
                div.appendChild(img);
                container.appendChild(div);
                pageElements.push(img);
            }

            // Render first 4 flipbook pages immediately
            var initialPages = Math.min(4, totalPages);
            for (var k = 0; k < initialPages; k++) {
                var entry = pageMap[k];
                var dataUrl = await renderPage(pdf, entry.pdfPage, pageW, entry.half);
                pageElements[k].src = dataUrl;
                renderedPages.add(k);
            }

            barFill.style.width = '100%';
            loadingEl.style.display = 'none';
            toolbar.style.display = 'flex';
            arrowPrev.style.display = 'flex';
            arrowNext.style.display = 'flex';

            // Init StPageFlip (modo single page — 1 pagina por vez)
            pageFlip = new St.PageFlip(container, {
                width: pageW,
                height: pageH,
                size: 'fixed',
                minWidth: 200,
                maxWidth: 1400,
                minHeight: 300,
                maxHeight: 2000,
                maxShadowOpacity: 0.5,
                showCover: true,
                mobileScrollSupport: false,
                usePortrait: true,
                drawShadow: true,
                flippingTime: 700,
                useMouseEvents: true,
                swipeDistance: 30,
            });

            pageFlip.loadFromHTML(container.querySelectorAll('.mag-page'));

            pageFlip.on('flip', function(e) {
                var idx = Number(e.data);
                currentEl.textContent = Math.max(1, idx + 1);
                playPageSound();
                updateButtons();
                lazyRenderNearby(pdf, pageMap, idx, pageW, pageElements, totalPages);
            });

            updateButtons();

            // Background render remaining pages
            lazyRenderAll(pdf, pageMap, initialPages, pageW, pageElements, totalPages);

        } catch (err) {
            console.error(err);
            loadingEl.style.display = 'none';
            errorEl.style.display = 'block';
            errorMsg.textContent = err.message || String(err);
        }
    }

    async function lazyRenderNearby(pdf, pageMap, currentIdx, pageW, pageElements, numPages) {
        var targets = [currentIdx - 1, currentIdx, currentIdx + 1, currentIdx + 2, currentIdx + 3];
        for (var t = 0; t < targets.length; t++) {
            var idx = targets[t];
            if (idx < 0 || idx >= numPages || renderedPages.has(idx)) continue;
            renderedPages.add(idx);
            try {
                var entry = pageMap[idx];
                var dataUrl = await renderPage(pdf, entry.pdfPage, pageW, entry.half);
                pageElements[idx].src = dataUrl;
            } catch (e) { /* skip */ }
        }
    }

    async function lazyRenderAll(pdf, pageMap, startFrom, pageW, pageElements, numPages) {
        for (var i = startFrom; i < numPages; i++) {
            if (renderedPages.has(i)) continue;
            renderedPages.add(i);
            try {
                var entry = pageMap[i];
                var dataUrl = await renderPage(pdf, entry.pdfPage, pageW, entry.half);
                pageElements[i].src = dataUrl;
            } catch (e) { /* skip */ }
            if (i % 2 === 0) await new Promise(function(r) { setTimeout(r, 40); });
        }
    }

    function updateButtons() {
        if (!pageFlip) return;
        var cur = pageFlip.getCurrentPageIndex();
        var total = pageFlip.getPageCount();
        btnPrev.disabled = cur <= 0;
        btnFirst.disabled = cur <= 0;
        btnNext.disabled = cur >= total - 1;
        btnLast.disabled = cur >= total - 1;
        arrowPrev.disabled = cur <= 0;
        arrowNext.disabled = cur >= total - 1;
    }

    function positionArrows() {
        if (!container) return;
        var rect = container.getBoundingClientRect();
        if (rect.width < 100) return;
        var gapLeft = rect.left;
        var gapRight = window.innerWidth - rect.right;
        var leftPos = Math.max(12, (gapLeft / 2) - 27);
        var rightPos = Math.max(12, (gapRight / 2) - 27);
        arrowPrev.style.left = leftPos + 'px';
        arrowNext.style.right = rightPos + 'px';
    }

    btnPrev.addEventListener('click', function() { if (pageFlip) pageFlip.flipPrev(); });
    btnNext.addEventListener('click', function() { if (pageFlip) pageFlip.flipNext(); });
    btnFirst.addEventListener('click', function() { if (pageFlip) pageFlip.flip(0); });
    btnLast.addEventListener('click', function() { if (pageFlip) pageFlip.flip(pageFlip.getPageCount() - 1); });
    arrowPrev.addEventListener('click', function() { if (pageFlip) pageFlip.flipPrev(); });
    arrowNext.addEventListener('click', function() { if (pageFlip) pageFlip.flipNext(); });

    document.addEventListener('keydown', function(e) {
        if (!pageFlip) return;
        if (e.key === 'ArrowLeft')  { pageFlip.flipPrev(); e.preventDefault(); }
        if (e.key === 'ArrowRight') { pageFlip.flipNext(); e.preventDefault(); }
        if (e.key === 'Home')       { pageFlip.flip(0); e.preventDefault(); }
        if (e.key === 'End')        { pageFlip.flip(pageFlip.getPageCount() - 1); e.preventDefault(); }
        if (e.key === 'Escape')     { if (document.fullscreenElement) document.exitFullscreen(); }
    });

    btnFull.addEventListener('click', function() {
        if (!document.fullscreenElement) {
            if (stage.requestFullscreen) stage.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    });
    document.addEventListener('fullscreenchange', function() {
        btnFull.querySelector('i').className = document.fullscreenElement ? 'fas fa-compress' : 'fas fa-expand';
    });

    // Zoom: toggle entre 1x, 1.5x, 2x
    var zoomLevels = [1, 1.5, 2];
    var zoomIndex = 0;
    btnZoom.addEventListener('click', function() {
        zoomIndex = (zoomIndex + 1) % zoomLevels.length;
        var z = zoomLevels[zoomIndex];
        container.style.transition = 'transform 0.3s ease';
        container.style.transformOrigin = 'center center';
        container.style.transform = 'scale(' + z + ')';
        btnZoomIcn.className = z === 1
            ? 'fas fa-magnifying-glass-plus'
            : 'fas fa-magnifying-glass-minus';
        btnZoom.classList.toggle('is-active', z > 1);
    });

    // Reposition arrows on render + resize
    setInterval(positionArrows, 1500);
    window.addEventListener('resize', function() { setTimeout(positionArrows, 200); });

    window.addEventListener('beforeunload', function() {
        document.body.classList.remove('mag-viewer-active');
    });

    init();
})();
</script>
@endsection
