@extends('layouts.app')

@section('title', $magazine->title . ' - Revista')

@push('head')
    <meta name="description" content="{{ $magazine->short_description }}">
@endpush

@push('styles')
<style>
    /* ========== Stage ========== */
    .mag-body-lock { overflow: hidden; }

    .mag-stage {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background:
            radial-gradient(ellipse at 20% 10%, rgba(139, 92, 246, 0.18), transparent 55%),
            radial-gradient(ellipse at 80% 90%, rgba(79, 70, 229, 0.22), transparent 60%),
            linear-gradient(180deg, #0b0a1f 0%, #07061a 60%, #020617 100%);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .mag-stage::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            radial-gradient(circle at 25% 30%, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
            radial-gradient(circle at 75% 70%, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
        background-size: 120px 120px, 180px 180px;
        pointer-events: none;
    }

    /* ========== Header (floating) ========== */
    .mag-head {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        z-index: 10;
        background: linear-gradient(180deg, rgba(2, 6, 23, 0.9), rgba(2, 6, 23, 0));
    }
    .mag-head-left, .mag-head-right { display: flex; align-items: center; gap: 1rem; }
    .mag-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: rgba(226, 232, 240, 0.85);
        font-weight: 700;
        font-size: 0.875rem;
        padding: 0.5rem 0.875rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.06);
        backdrop-filter: blur(10px);
        transition: all 0.2s;
        text-decoration: none;
    }
    .mag-back:hover { background: rgba(168, 85, 247, 0.25); color: #fff; }
    .mag-title {
        color: #fff;
        font-weight: 900;
        font-size: 1rem;
        line-height: 1.2;
        max-width: 400px;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }
    .mag-subtitle {
        color: rgba(203, 213, 225, 0.7);
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-top: 2px;
    }

    /* ========== Book stage (central) ========== */
    .mag-canvas {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5rem 1rem 7rem;
        perspective: 2000px;
        position: relative;
        min-height: 0;
    }

    /* Subtle floor shadow under the book */
    .mag-canvas::after {
        content: '';
        position: absolute;
        bottom: 6rem;
        left: 50%;
        transform: translateX(-50%);
        width: 70%;
        height: 40px;
        background: radial-gradient(ellipse, rgba(0, 0, 0, 0.7) 0%, transparent 70%);
        filter: blur(20px);
        pointer-events: none;
        z-index: 1;
    }

    #mag-flipbook-container {
        box-shadow:
            0 40px 80px -20px rgba(0, 0, 0, 0.8),
            0 20px 40px -10px rgba(0, 0, 0, 0.5),
            0 0 0 1px rgba(255, 255, 255, 0.04);
        border-radius: 4px;
        background: #0f172a;
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

    /* ========== Loading ========== */
    .mag-loading {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.25rem;
        color: #e2e8f0;
        z-index: 2;
    }
    .mag-loader {
        width: 60px; height: 60px;
        border: 4px solid rgba(168, 85, 247, 0.15);
        border-top-color: #a855f7;
        border-right-color: #a855f7;
        border-radius: 50%;
        animation: magspin 0.9s linear infinite;
    }
    @keyframes magspin { to { transform: rotate(360deg); } }
    .mag-loading-label {
        font-size: 0.875rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
    }
    .mag-loading-progress {
        font-size: 0.75rem;
        color: rgba(203, 213, 225, 0.6);
        font-variant-numeric: tabular-nums;
    }
    .mag-loading-bar {
        width: 200px;
        height: 3px;
        background: rgba(168, 85, 247, 0.15);
        border-radius: 999px;
        overflow: hidden;
    }
    .mag-loading-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #a855f7, #6366f1);
        width: 0%;
        transition: width 0.3s ease;
    }

    /* ========== Toolbar (floating bottom) ========== */
    .mag-toolbar {
        position: absolute;
        bottom: 1.5rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.5rem;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 999px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        z-index: 10;
    }
    .mag-btn {
        width: 42px;
        height: 42px;
        border: 0;
        border-radius: 999px;
        background: transparent;
        color: #cbd5e1;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        transition: all 0.15s;
        text-decoration: none;
    }
    .mag-btn:hover {
        background: rgba(168, 85, 247, 0.25);
        color: #fff;
        transform: translateY(-1px);
    }
    .mag-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
        pointer-events: none;
    }
    .mag-btn.is-active { background: rgba(168, 85, 247, 0.35); color: #fff; }

    .mag-pages {
        padding: 0 0.75rem;
        color: #cbd5e1;
        font-size: 0.8rem;
        font-weight: 900;
        font-variant-numeric: tabular-nums;
        letter-spacing: 1px;
        min-width: 80px;
        text-align: center;
    }
    .mag-pages strong { color: #fff; }

    .mag-divider {
        width: 1px;
        height: 24px;
        background: rgba(255, 255, 255, 0.1);
        margin: 0 0.25rem;
    }

    /* ========== Error ========== */
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

    /* ========== Responsive ========== */
    @media (max-width: 768px) {
        .mag-head { padding: 0.75rem 1rem; }
        .mag-title { font-size: 0.85rem; max-width: 200px; }
        .mag-canvas { padding: 4rem 0.5rem 6rem; }
        .mag-btn { width: 38px; height: 38px; }
        .mag-pages { font-size: 0.7rem; padding: 0 0.5rem; min-width: 70px; }
    }

    /* ========== Hide site chrome while viewing ========== */
    body.mag-viewer-active > header,
    body.mag-viewer-active > footer,
    body.mag-viewer-active > nav,
    body.mag-viewer-active .site-header,
    body.mag-viewer-active .site-footer,
    body.mag-viewer-active .site-nav,
    body.mag-viewer-active .navbar,
    body.mag-viewer-active .site-back-to-top,
    body.mag-viewer-active #pwa-install-modal {
        display: none !important;
    }
    body.mag-viewer-active main {
        padding-top: 0 !important;
        margin-top: 0 !important;
        min-height: 100vh !important;
    }
    body.mag-viewer-active { overflow: hidden; }
</style>
@endpush

@section('content')
<div class="mag-stage" id="mag-stage">

    {{-- Header flutuante --}}
    <div class="mag-head">
        <div class="mag-head-left">
            <a href="{{ route('magazines.index') }}" class="mag-back">
                <i class="fas fa-arrow-left"></i> <span>Voltar a banca</span>
            </a>
        </div>
        <div class="mag-head-right">
            <div>
                <div class="mag-subtitle">{{ $magazine->category ?: 'Revista' }}</div>
                <div class="mag-title">{{ $magazine->title }}</div>
            </div>
        </div>
    </div>

    {{-- Palco central --}}
    <div class="mag-canvas" id="mag-canvas">
        <div class="mag-loading" id="mag-loading">
            <div class="mag-loader"></div>
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

    {{-- Toolbar --}}
    <div class="mag-toolbar" id="mag-toolbar" style="display:none;">
        <button id="mag-first" class="mag-btn" title="Primeira pagina"><i class="fas fa-angles-left"></i></button>
        <button id="mag-prev" class="mag-btn" title="Pagina anterior"><i class="fas fa-chevron-left"></i></button>

        <div class="mag-pages"><strong id="mag-current">1</strong> / <span id="mag-total">?</span></div>

        <button id="mag-next" class="mag-btn" title="Proxima pagina"><i class="fas fa-chevron-right"></i></button>
        <button id="mag-last" class="mag-btn" title="Ultima pagina"><i class="fas fa-angles-right"></i></button>

        <div class="mag-divider"></div>

        <button id="mag-sound" class="mag-btn is-active" title="Som de pagina virando"><i id="mag-sound-icon" class="fas fa-volume-high"></i></button>
        <button id="mag-zoom" class="mag-btn" title="Aumentar zoom"><i class="fas fa-magnifying-glass-plus"></i></button>
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

    const PDF_URL       = @json($magazine->pdf_url);
    const SOUND_ENABLED = @json((bool) $magazine->enable_sound);

    // Elements
    const stage       = document.getElementById('mag-stage');
    const loadingEl   = document.getElementById('mag-loading');
    const barFill     = document.getElementById('mag-loading-bar-fill');
    const progressEl  = document.getElementById('mag-loading-progress');
    const container   = document.getElementById('mag-flipbook-container');
    const toolbar     = document.getElementById('mag-toolbar');
    const errorEl     = document.getElementById('mag-error');
    const errorMsg    = document.getElementById('mag-error-msg');
    const currentEl   = document.getElementById('mag-current');
    const totalEl     = document.getElementById('mag-total');
    const btnPrev     = document.getElementById('mag-prev');
    const btnNext     = document.getElementById('mag-next');
    const btnFirst    = document.getElementById('mag-first');
    const btnLast     = document.getElementById('mag-last');
    const btnSound    = document.getElementById('mag-sound');
    const btnSoundIcn = document.getElementById('mag-sound-icon');
    const btnFull     = document.getElementById('mag-fullscreen');
    const btnZoom     = document.getElementById('mag-zoom');

    // Hide site chrome
    document.body.classList.add('mag-viewer-active');

    // Sound state
    let soundOn = SOUND_ENABLED;
    let audioCtx = null;
    let pageFlip = null;
    let zoomLevel = 1;

    function updateSoundIcon() {
        btnSoundIcn.className = soundOn ? 'fas fa-volume-high' : 'fas fa-volume-xmark';
        btnSound.classList.toggle('is-active', soundOn);
    }
    updateSoundIcon();
    btnSound.addEventListener('click', () => { soundOn = !soundOn; updateSoundIcon(); });

    /**
     * Page-turn sound — realistic paper flip via layered synthesis.
     * - Phase 1: Quick "woosh" (air moved by the flipping page) — bandpassed noise burst
     * - Phase 2: Multiple paper "crinkle" micro-transients at random intervals
     * - Phase 3: Subtle tail fade
     */
    function playPageSound() {
        if (!soundOn) return;
        try {
            if (!audioCtx) {
                const AC = window.AudioContext || window.webkitAudioContext;
                if (!AC) return;
                audioCtx = new AC();
            }
            if (audioCtx.state === 'suspended') { audioCtx.resume(); }

            const now = audioCtx.currentTime;
            const sr = audioCtx.sampleRate;

            // Master gain for the whole sound event
            const master = audioCtx.createGain();
            master.gain.value = 0.6;
            master.connect(audioCtx.destination);

            // --- Phase 1: Woosh (80ms) ---
            const wooshDur = 0.09;
            const wooshBuf = audioCtx.createBuffer(1, Math.floor(sr * wooshDur), sr);
            const wd = wooshBuf.getChannelData(0);
            for (let i = 0; i < wd.length; i++) {
                // Pink-ish noise with light lowpass character
                const t = i / wd.length;
                wd[i] = (Math.random() * 2 - 1) * (1 - t * 0.4);
            }
            const woosh = audioCtx.createBufferSource();
            woosh.buffer = wooshBuf;
            // Bandpass centred around 2.5kHz sweeping to 4kHz (frequency rise as page flips up)
            const wooshFilter = audioCtx.createBiquadFilter();
            wooshFilter.type = 'bandpass';
            wooshFilter.Q.value = 1.5;
            wooshFilter.frequency.setValueAtTime(2200, now);
            wooshFilter.frequency.linearRampToValueAtTime(4200, now + wooshDur);
            const wooshGain = audioCtx.createGain();
            wooshGain.gain.setValueAtTime(0.001, now);
            wooshGain.gain.exponentialRampToValueAtTime(0.5, now + 0.015);
            wooshGain.gain.exponentialRampToValueAtTime(0.001, now + wooshDur);
            woosh.connect(wooshFilter).connect(wooshGain).connect(master);
            woosh.start(now);

            // --- Phase 2: Paper crinkle — 8 tiny random transients ---
            const crinkleCount = 7 + Math.floor(Math.random() * 3);
            for (let i = 0; i < crinkleCount; i++) {
                const startOffset = 0.03 + Math.random() * 0.19;
                const startTime = now + startOffset;
                const burstDur = 0.012 + Math.random() * 0.025;
                const burstBuf = audioCtx.createBuffer(1, Math.floor(sr * burstDur), sr);
                const bd = burstBuf.getChannelData(0);
                for (let j = 0; j < bd.length; j++) bd[j] = Math.random() * 2 - 1;
                const burst = audioCtx.createBufferSource();
                burst.buffer = burstBuf;
                const bf = audioCtx.createBiquadFilter();
                bf.type = 'highpass';
                bf.frequency.value = 3000 + Math.random() * 3500;
                bf.Q.value = 0.7;
                const bg = audioCtx.createGain();
                const peak = 0.12 + Math.random() * 0.18;
                bg.gain.setValueAtTime(0.001, startTime);
                bg.gain.exponentialRampToValueAtTime(peak, startTime + 0.002);
                bg.gain.exponentialRampToValueAtTime(0.001, startTime + burstDur);
                burst.connect(bf).connect(bg).connect(master);
                burst.start(startTime);
            }

            // --- Phase 3: "Thwap" click when page lands (single impulse) ---
            const clickTime = now + 0.21;
            const clickBuf = audioCtx.createBuffer(1, Math.floor(sr * 0.02), sr);
            const cd = clickBuf.getChannelData(0);
            for (let i = 0; i < cd.length; i++) {
                cd[i] = (Math.random() * 2 - 1) * Math.exp(-i / (cd.length * 0.25));
            }
            const click = audioCtx.createBufferSource();
            click.buffer = clickBuf;
            const cf = audioCtx.createBiquadFilter();
            cf.type = 'bandpass';
            cf.frequency.value = 1400;
            cf.Q.value = 2;
            const cg = audioCtx.createGain();
            cg.gain.value = 0.15;
            click.connect(cf).connect(cg).connect(master);
            click.start(clickTime);

        } catch (e) { /* silencioso */ }
    }

    // ========================================================
    // Render PDF pages — LAZY / PROGRESSIVE
    // ========================================================
    async function renderPage(pdf, pageNum, targetCssWidth) {
        const page = await pdf.getPage(pageNum);
        const vp1 = page.getViewport({ scale: 1 });
        const dpr = Math.min(1.5, window.devicePixelRatio || 1); // Cap DPR for performance
        const scale = (targetCssWidth * dpr) / vp1.width;
        const viewport = page.getViewport({ scale });

        const canvas = document.createElement('canvas');
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        const ctx = canvas.getContext('2d');
        await page.render({ canvasContext: ctx, viewport }).promise;
        return canvas.toDataURL('image/jpeg', 0.75); // Lower quality = faster
    }

    function computeBookSize(pageAspect) {
        const canvas = document.getElementById('mag-canvas');
        const rect = canvas.getBoundingClientRect();
        const availableW = rect.width - 40;
        const availableH = rect.height - 40;
        const isMobile = window.innerWidth < 900;

        if (isMobile) {
            let w = Math.min(availableW, 620);
            let h = w * pageAspect;
            if (h > availableH) { h = availableH; w = h / pageAspect; }
            return { width: Math.floor(w), height: Math.floor(h), isMobile: true };
        }

        let bookW = Math.min(availableW, 1200);
        let pageW = bookW / 2;
        let pageH = pageW * pageAspect;
        if (pageH > availableH) {
            pageH = availableH;
            pageW = pageH / pageAspect;
        }
        return { width: Math.floor(pageW), height: Math.floor(pageH), isMobile: false };
    }

    // Placeholder SVG (grey page with spinner)
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
            const loadingTask = pdfjsLib.getDocument({
                url: PDF_URL,
                cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                cMapPacked: true,
            });
            loadingTask.onProgress = (p) => {
                if (p.total > 0) {
                    const pct = Math.round((p.loaded / p.total) * 100);
                    barFill.style.width = Math.min(90, pct) + '%';
                    progressEl.textContent = 'Baixando PDF... ' + pct + '%';
                }
            };
            const pdf = await loadingTask.promise;
            const numPages = pdf.numPages;
            totalEl.textContent = numPages;

            // Compute aspect ratio from first page
            const firstPage = await pdf.getPage(1);
            const fvp = firstPage.getViewport({ scale: 1 });
            const aspect = fvp.height / fvp.width;

            // Compute target book size
            const { width: pageW, height: pageH, isMobile } = computeBookSize(aspect);

            progressEl.textContent = 'Preparando paginas...';
            barFill.style.width = '95%';

            // Create placeholder DOM for ALL pages immediately
            container.innerHTML = '';
            container.style.display = 'block';
            container.style.width = (isMobile ? pageW : pageW * 2) + 'px';
            container.style.height = pageH + 'px';

            const placeholder = placeholderSvg(pageW, pageH);
            const pageElements = [];

            for (let i = 0; i < numPages; i++) {
                const div = document.createElement('div');
                div.className = 'mag-page';
                const img = document.createElement('img');
                img.src = placeholder;
                img.draggable = false;
                img.dataset.pageIndex = i;
                div.appendChild(img);
                container.appendChild(div);
                pageElements.push(img);
            }

            // Render first 4 pages immediately (cover + first spread)
            const initialPages = Math.min(4, numPages);
            for (let i = 0; i < initialPages; i++) {
                const dataUrl = await renderPage(pdf, i + 1, pageW);
                pageElements[i].src = dataUrl;
            }
            markInitialRendered(initialPages);

            barFill.style.width = '100%';
            loadingEl.style.display = 'none';
            toolbar.style.display = 'flex';

            // Init StPageFlip
            pageFlip = new St.PageFlip(container, {
                width: pageW,
                height: pageH,
                size: 'fixed',
                minWidth: 260,
                maxWidth: 1400,
                minHeight: 380,
                maxHeight: 2000,
                maxShadowOpacity: 0.6,
                showCover: true,
                mobileScrollSupport: false,
                usePortrait: isMobile,
                drawShadow: true,
                flippingTime: 750,
                useMouseEvents: true,
                swipeDistance: 30,
                disableFlipByClick: false,
            });

            pageFlip.loadFromHTML(container.querySelectorAll('.mag-page'));

            pageFlip.on('flip', (e) => {
                const idx = Number(e.data);
                currentEl.textContent = Math.max(1, idx + 1);
                playPageSound();
                updateButtons();
                // Lazy-load nearby pages
                lazyRenderNearby(pdf, idx, pageW, pageElements, numPages);
            });

            updateButtons();

            // Start background rendering of remaining pages
            lazyRenderAll(pdf, initialPages, pageW, pageElements, numPages);

        } catch (err) {
            console.error(err);
            loadingEl.style.display = 'none';
            errorEl.style.display = 'block';
            errorMsg.textContent = err.message || String(err);
        }
    }

    // Lazy render: load pages near the current view
    const renderedPages = new Set();
    async function lazyRenderNearby(pdf, currentIdx, pageW, pageElements, numPages) {
        // Render 3 pages ahead and 1 behind
        const targets = [currentIdx - 1, currentIdx, currentIdx + 1, currentIdx + 2, currentIdx + 3];
        for (const idx of targets) {
            if (idx < 0 || idx >= numPages || renderedPages.has(idx)) continue;
            renderedPages.add(idx);
            try {
                const dataUrl = await renderPage(pdf, idx + 1, pageW);
                pageElements[idx].src = dataUrl;
            } catch (e) { /* skip */ }
        }
    }

    // Background render all remaining pages (low priority)
    async function lazyRenderAll(pdf, startFrom, pageW, pageElements, numPages) {
        for (let i = startFrom; i < numPages; i++) {
            if (renderedPages.has(i)) continue;
            renderedPages.add(i);
            try {
                const dataUrl = await renderPage(pdf, i + 1, pageW);
                pageElements[i].src = dataUrl;
            } catch (e) { /* skip */ }
            // Yield to main thread every 2 pages to keep UI responsive
            if (i % 2 === 0) await new Promise(r => setTimeout(r, 50));
        }
    }
    // Mark initial pages as rendered
    function markInitialRendered(count) {
        for (let i = 0; i < count; i++) renderedPages.add(i);
    }

    function updateButtons() {
        if (!pageFlip) return;
        const cur = pageFlip.getCurrentPageIndex();
        const total = pageFlip.getPageCount();
        btnPrev.disabled = cur <= 0;
        btnFirst.disabled = cur <= 0;
        btnNext.disabled = cur >= total - 1;
        btnLast.disabled = cur >= total - 1;
    }

    btnPrev.addEventListener('click', () => pageFlip && pageFlip.flipPrev());
    btnNext.addEventListener('click', () => pageFlip && pageFlip.flipNext());
    btnFirst.addEventListener('click', () => pageFlip && pageFlip.flip(0));
    btnLast.addEventListener('click', () => pageFlip && pageFlip.flip(pageFlip.getPageCount() - 1));

    // Keyboard
    document.addEventListener('keydown', (e) => {
        if (!pageFlip) return;
        if (e.key === 'ArrowLeft')  { pageFlip.flipPrev(); e.preventDefault(); }
        if (e.key === 'ArrowRight') { pageFlip.flipNext(); e.preventDefault(); }
        if (e.key === 'Home')       { pageFlip.flip(0); e.preventDefault(); }
        if (e.key === 'End')        { pageFlip.flip(pageFlip.getPageCount() - 1); e.preventDefault(); }
        if (e.key === 'Escape')     { if (document.fullscreenElement) document.exitFullscreen(); }
    });

    // Fullscreen
    btnFull.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            stage.requestFullscreen && stage.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    });
    document.addEventListener('fullscreenchange', () => {
        btnFull.querySelector('i').className = document.fullscreenElement ? 'fas fa-compress' : 'fas fa-expand';
    });

    // Zoom (simple toggle 1x <-> 1.4x)
    btnZoom.addEventListener('click', () => {
        zoomLevel = zoomLevel === 1 ? 1.4 : 1;
        container.style.transition = 'transform 0.3s ease';
        container.style.transform = 'scale(' + zoomLevel + ')';
        btnZoom.querySelector('i').className = zoomLevel === 1
            ? 'fas fa-magnifying-glass-plus'
            : 'fas fa-magnifying-glass-minus';
    });

    // Cleanup on navigation away
    window.addEventListener('beforeunload', () => {
        document.body.classList.remove('mag-viewer-active');
    });

    // Start
    init();
})();
</script>
@endsection
