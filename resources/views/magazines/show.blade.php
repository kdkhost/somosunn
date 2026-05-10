@extends('layouts.app')

@section('title', $magazine->title . ' - Revista')

@push('head')
    <meta name="description" content="{{ $magazine->short_description }}">
    <style>
        .mag-viewer-wrap {
            background: radial-gradient(ellipse at top, #1e1b4b 0%, #0f172a 60%, #020617 100%);
            min-height: 100vh;
        }
        .mag-stage {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 70vh;
            padding: 2rem 1rem;
            perspective: 2000px;
        }
        .mag-flipbook {
            box-shadow: 0 30px 60px -15px rgba(0,0,0,0.8), 0 0 0 1px rgba(255,255,255,0.05);
            border-radius: 4px;
            overflow: hidden;
            background: #fff;
        }
        .mag-page {
            background: #fff;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mag-page img, .mag-page canvas {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: contain;
        }
        .mag-toolbar {
            backdrop-filter: blur(20px);
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .mag-btn {
            color: #e2e8f0;
            background: rgba(255,255,255,0.08);
            border-radius: 9999px;
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all .2s;
            border: 0;
            cursor: pointer;
        }
        .mag-btn:hover { background: rgba(168,85,247,0.3); transform: translateY(-2px); }
        .mag-btn:disabled { opacity: .3; cursor: not-allowed; }
        .mag-page-indicator {
            color: #cbd5e1;
            font-weight: 900;
            font-variant-numeric: tabular-nums;
        }
        .mag-loader {
            width: 48px; height: 48px;
            border: 4px solid rgba(168,85,247,0.2);
            border-top-color: #a855f7;
            border-radius: 50%;
            animation: magspin 0.8s linear infinite;
        }
        @keyframes magspin { to { transform: rotate(360deg); } }
        .mag-cover-placeholder {
            width: 420px; max-width: 90vw;
            aspect-ratio: 3/4;
            display: flex; align-items: center; justify-content: center;
            flex-direction: column;
            gap: 1rem;
            background: linear-gradient(135deg, #1e1b4b, #4c1d95);
            border-radius: 8px;
            color: #e9d5ff;
        }
        /* Fullscreen */
        .mag-viewer-wrap:fullscreen { padding: 0; }
        .mag-viewer-wrap:fullscreen .mag-stage { min-height: 100vh; }
    </style>
@endpush

@section('content')
<div class="mag-viewer-wrap" id="mag-viewer-wrap">

    {{-- Header --}}
    <div class="relative px-4 sm:px-8 pt-6 pb-2">
        <div class="max-w-6xl mx-auto flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('magazines.index') }}" class="inline-flex items-center gap-2 text-slate-300 hover:text-white text-sm font-bold">
                <i class="fas fa-arrow-left"></i> Voltar a banca
            </a>
            <div class="text-right">
                <div class="text-[10px] uppercase tracking-widest font-black text-purple-300">{{ $magazine->category ?: 'Revista' }}</div>
                <h1 class="text-xl sm:text-2xl font-black text-white">{{ $magazine->title }}</h1>
                @if($magazine->edition || $magazine->published_at)
                    <div class="text-xs text-slate-400 mt-0.5">
                        {{ $magazine->edition }}
                        @if($magazine->published_at) &middot; {{ $magazine->published_at->format('F Y') }} @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Stage (flipbook) --}}
    <div class="mag-stage">
        <div id="mag-loading" class="flex flex-col items-center gap-4 text-slate-300">
            <div class="mag-loader"></div>
            <div class="text-sm font-bold">Carregando revista...</div>
            <div id="mag-progress" class="text-xs text-slate-500">0%</div>
        </div>
        <div id="mag-flipbook-container" style="display:none;"></div>
    </div>

    {{-- Toolbar --}}
    <div class="px-4 pb-6">
        <div class="max-w-4xl mx-auto mag-toolbar rounded-full px-4 py-2.5 flex items-center justify-between gap-2">
            <button id="mag-prev" class="mag-btn" title="Pagina anterior"><i class="fas fa-chevron-left"></i></button>
            <button id="mag-first" class="mag-btn" title="Primeira pagina"><i class="fas fa-angles-left"></i></button>

            <div class="flex-1 text-center mag-page-indicator">
                Pagina <span id="mag-current">1</span> de <span id="mag-total">?</span>
            </div>

            <button id="mag-sound" class="mag-btn" title="Som"><i id="mag-sound-icon" class="fas fa-volume-high"></i></button>
            @if($magazine->allow_download)
                <a href="{{ $magazine->pdf_url }}" download class="mag-btn" title="Baixar PDF"><i class="fas fa-download"></i></a>
            @endif
            <button id="mag-fullscreen" class="mag-btn" title="Tela cheia"><i class="fas fa-expand"></i></button>

            <button id="mag-last" class="mag-btn" title="Ultima pagina"><i class="fas fa-angles-right"></i></button>
            <button id="mag-next" class="mag-btn" title="Proxima pagina"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>

    {{-- Descricao --}}
    @if($magazine->full_description)
        <div class="px-4 pb-12">
            <div class="max-w-3xl mx-auto bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                <h3 class="text-white font-black text-sm uppercase tracking-widest mb-3">Sobre esta edicao</h3>
                <div class="text-slate-300 text-sm leading-relaxed prose prose-invert max-w-none">
                    {!! nl2br(e($magazine->full_description)) !!}
                </div>
            </div>
        </div>
    @endif

    {{-- Relacionadas --}}
    @if($related->count())
        <div class="px-4 pb-16 bg-gradient-to-b from-transparent to-slate-950">
            <div class="max-w-6xl mx-auto">
                <h3 class="text-white font-black text-lg mb-4 flex items-center gap-2">
                    <i class="fas fa-layer-group text-purple-400"></i> Outras edicoes
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach($related as $r)
                        <a href="{{ route('magazines.show', $r->slug) }}" class="group block rounded-xl overflow-hidden bg-white/5 border border-white/10 hover:-translate-y-1 transition-transform">
                            <div class="aspect-[3/4] bg-slate-800">
                                @if($r->thumbnail_url)
                                    <img src="{{ $r->thumbnail_url }}" alt="{{ $r->title }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="p-3">
                                <div class="text-white text-xs font-black line-clamp-2">{{ $r->title }}</div>
                                <div class="text-slate-400 text-[10px] mt-1">{{ $r->edition }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

{{-- PDF.js + StPageFlip --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js"></script>
<script>
(function () {
    // Config
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const PDF_URL       = @json($magazine->pdf_url);
    const SOUND_ENABLED = @json((bool) $magazine->enable_sound);
    const MAG_ID        = @json($magazine->id);

    const loadingEl   = document.getElementById('mag-loading');
    const progressEl  = document.getElementById('mag-progress');
    const container   = document.getElementById('mag-flipbook-container');
    const currentEl   = document.getElementById('mag-current');
    const totalEl     = document.getElementById('mag-total');
    const btnPrev     = document.getElementById('mag-prev');
    const btnNext     = document.getElementById('mag-next');
    const btnFirst    = document.getElementById('mag-first');
    const btnLast     = document.getElementById('mag-last');
    const btnSound    = document.getElementById('mag-sound');
    const btnSoundIcn = document.getElementById('mag-sound-icon');
    const btnFull     = document.getElementById('mag-fullscreen');
    const wrap        = document.getElementById('mag-viewer-wrap');

    let soundOn = SOUND_ENABLED;
    let audioCtx = null;
    let pageFlip = null;

    function updateSoundIcon() {
        btnSoundIcn.className = soundOn ? 'fas fa-volume-high' : 'fas fa-volume-xmark';
    }
    updateSoundIcon();

    btnSound.addEventListener('click', () => {
        soundOn = !soundOn;
        updateSoundIcon();
    });

    /**
     * Som de virar pagina — gerado via Web Audio API (papel amassado sintetico).
     * Sem necessidade de arquivo externo.
     */
    function playPageSound() {
        if (!soundOn) return;
        try {
            if (!audioCtx) {
                const AC = window.AudioContext || window.webkitAudioContext;
                if (!AC) return;
                audioCtx = new AC();
            }
            const duration = 0.35;
            const sr = audioCtx.sampleRate;
            const buffer = audioCtx.createBuffer(1, sr * duration, sr);
            const data = buffer.getChannelData(0);
            for (let i = 0; i < data.length; i++) {
                const t = i / sr;
                // Noise with envelope (crinkle)
                const env = Math.exp(-t * 8) * (1 - t / duration);
                const noise = (Math.random() * 2 - 1) * env;
                // Subtle mid sweep (paper body)
                const sweep = Math.sin(2 * Math.PI * (800 - 400 * t) * t) * env * 0.15;
                data[i] = (noise * 0.6) + sweep;
            }
            const src = audioCtx.createBufferSource();
            src.buffer = buffer;
            const filter = audioCtx.createBiquadFilter();
            filter.type = 'highpass';
            filter.frequency.value = 1200;
            const gain = audioCtx.createGain();
            gain.gain.value = 0.35;
            src.connect(filter).connect(gain).connect(audioCtx.destination);
            src.start();
        } catch (e) { /* silencioso */ }
    }

    async function renderPage(pdf, pageNum, targetWidth) {
        const page = await pdf.getPage(pageNum);
        const unscaledViewport = page.getViewport({ scale: 1 });
        const scale = targetWidth / unscaledViewport.width;
        const viewport = page.getViewport({ scale });

        const canvas = document.createElement('canvas');
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        const ctx = canvas.getContext('2d');
        await page.render({ canvasContext: ctx, viewport }).promise;
        return canvas.toDataURL('image/jpeg', 0.88);
    }

    async function init() {
        try {
            // Load PDF with progress
            const loadingTask = pdfjsLib.getDocument({
                url: PDF_URL,
                cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                cMapPacked: true,
            });
            loadingTask.onProgress = (p) => {
                if (p.total > 0) {
                    const pct = Math.min(99, Math.round((p.loaded / p.total) * 100));
                    progressEl.textContent = pct + '% (carregando PDF)';
                }
            };
            const pdf = await loadingTask.promise;
            const numPages = pdf.numPages;
            totalEl.textContent = numPages;

            // Determine target render width based on viewport
            const isMobile = window.innerWidth < 900;
            const viewerW = Math.min(window.innerWidth - 40, 1100);
            const pageRenderWidth = isMobile ? viewerW : Math.floor(viewerW / 2);
            // Get aspect ratio from first page
            const firstPage = await pdf.getPage(1);
            const vp = firstPage.getViewport({ scale: 1 });
            const aspect = vp.height / vp.width;

            const pageW = pageRenderWidth;
            const pageH = Math.round(pageW * aspect);

            // Pre-render all pages (progressive)
            const pageImages = [];
            for (let i = 1; i <= numPages; i++) {
                progressEl.textContent = 'Renderizando pagina ' + i + ' de ' + numPages;
                pageImages.push(await renderPage(pdf, i, pageRenderWidth * window.devicePixelRatio));
            }

            // Build DOM
            loadingEl.style.display = 'none';
            container.style.display = 'block';
            container.className = 'mag-flipbook';

            pageImages.forEach((src) => {
                const div = document.createElement('div');
                div.className = 'mag-page';
                const img = document.createElement('img');
                img.src = src;
                div.appendChild(img);
                container.appendChild(div);
            });

            // Init StPageFlip
            pageFlip = new St.PageFlip(container, {
                width: pageW,
                height: pageH,
                size: 'stretch',
                minWidth: 260,
                maxWidth: 1200,
                minHeight: 380,
                maxHeight: 1800,
                maxShadowOpacity: 0.5,
                showCover: true,
                mobileScrollSupport: false,
                usePortrait: isMobile,
                drawShadow: true,
                flippingTime: 700,
                useMouseEvents: true,
                swipeDistance: 30,
            });

            pageFlip.loadFromHTML(container.querySelectorAll('.mag-page'));

            pageFlip.on('flip', (e) => {
                const idx = e.data;
                currentEl.textContent = Math.max(1, idx + 1);
                playPageSound();
                updateButtons();
            });

            updateButtons();
        } catch (err) {
            console.error(err);
            loadingEl.innerHTML = '<div class="text-red-300 text-center">'
                + '<i class="fas fa-exclamation-triangle text-4xl mb-3"></i>'
                + '<div class="text-sm">Nao foi possivel carregar o PDF.</div>'
                + '<div class="text-xs text-slate-500 mt-2">' + (err.message || err) + '</div>'
                + '</div>';
        }
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

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (!pageFlip) return;
        if (e.key === 'ArrowLeft')  pageFlip.flipPrev();
        if (e.key === 'ArrowRight') pageFlip.flipNext();
        if (e.key === 'Home')       pageFlip.flip(0);
        if (e.key === 'End')        pageFlip.flip(pageFlip.getPageCount() - 1);
    });

    // Fullscreen
    btnFull.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            wrap.requestFullscreen && wrap.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    });

    // Start
    init();
})();
</script>
@endsection
