@extends('layouts.app')

@section('title', $magazine->title . ' - Revista')

@push('styles')
<link href="{{ asset('assets-dflip/css/dflip.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets-dflip/css/themify-icons.min.css') }}" rel="stylesheet" type="text/css">
<style>
    .mag-viewer {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgb(61, 61, 61);
        display: flex;
        flex-direction: column;
    }

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
        background: linear-gradient(180deg, rgba(0,0,0,0.8) 0%, transparent 100%);
        pointer-events: none;
    }
    .mag-header > * { pointer-events: auto; }

    .mag-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: rgba(255,255,255,0.85);
        font-weight: 700;
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        border-radius: 999px;
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(8px);
        text-decoration: none;
        transition: all 0.2s;
    }
    .mag-back:hover { background: rgba(255,255,255,0.2); color: #fff; }

    .mag-info {
        text-align: right;
    }
    .mag-info-cat {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-weight: 900;
        color: rgba(168,85,247,0.9);
    }
    .mag-info-title {
        font-size: 0.95rem;
        font-weight: 900;
        color: #fff;
        max-width: 350px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mag-flipbook-wrap {
        flex: 1;
        width: 100%;
        height: 100%;
        min-height: 0;
    }
    .mag-flipbook-wrap ._df_book {
        width: 100% !important;
        height: 100% !important;
    }

    /* === DearFlip Controls Enhancement === */
    /* Esconder setas nativas do DearFlip (vamos usar as nossas) */
    .mag-flipbook-wrap .df-container > .df-ui-next,
    .mag-flipbook-wrap .df-container > .df-ui-prev {
        display: none !important;
    }

    /* Nossas setas customizadas, posicionadas junto ao livro */
    .mag-custom-arrow {
        position: fixed;
        top: 50%;
        transform: translateY(-50%);
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.55);
        color: #fff;
        font-size: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10000;
        border: 0;
        opacity: 0.85;
        transition: opacity 0.2s ease, background 0.2s ease;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
    }
    #mag-arrow-prev { left: 20px; }
    #mag-arrow-next { right: 20px; }
    .mag-custom-arrow:hover {
        opacity: 1;
        background: rgba(0, 0, 0, 0.8);
    }
    .mag-custom-arrow:disabled {
        opacity: 0.25;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* Barra de controles inferior mais visivel */
    .df-container .df-ui-controls {
        background-color: rgba(40, 40, 40, 0.95) !important;
        backdrop-filter: blur(10px) !important;
        border-radius: 12px !important;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.4) !important;
        padding: 4px 8px !important;
        margin-bottom: 8px !important;
    }
    .df-floating .df-ui-controls {
        border-radius: 12px !important;
    }

    /* Botoes da barra com mais destaque */
    .df-container .df-ui-btn {
        color: #ccc !important;
        font-size: 16px !important;
        width: 38px !important;
        height: 38px !important;
        border-radius: 8px !important;
        transition: all 0.15s !important;
    }
    .df-container .df-ui-btn:hover {
        color: #fff !important;
        background-color: rgba(168, 85, 247, 0.3) !important;
    }
    .df-container .df-ui-btn.df-active {
        color: #a855f7 !important;
        background-color: rgba(168, 85, 247, 0.15) !important;
    }

    /* Indicador de pagina */
    .df-container .df-ui-page {
        background-color: rgba(255,255,255,0.08) !important;
        border-radius: 8px !important;
        color: #fff !important;
    }
    .df-container .df-ui-page label {
        color: #ddd !important;
        font-weight: 700 !important;
    }

    /* Background do flipbook */
    .df-container {
        background-color: rgb(61, 61, 61) !important;
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
</style>
@endpush

@section('content')
<div class="mag-viewer" id="mag-viewer">
    {{-- Header --}}
    <div class="mag-header">
        <a href="{{ route('magazines.index') }}" class="mag-back">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <div class="mag-info">
            <div class="mag-info-cat">{{ $magazine->category ?: 'Revista' }}</div>
            <div class="mag-info-title">{{ $magazine->title }}</div>
        </div>
    </div>

    {{-- DearFlip Flipbook --}}
    <div class="mag-flipbook-wrap">
        <div class="_df_book" id="magazine_flipbook"
            source="{{ $magazine->pdf_url }}">
        </div>
    </div>

    {{-- Setas customizadas (posicionadas via JS junto ao livro) --}}
    <button type="button" class="mag-custom-arrow" id="mag-arrow-prev" aria-label="Pagina anterior">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button type="button" class="mag-custom-arrow" id="mag-arrow-next" aria-label="Proxima pagina">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>
@endsection

@push('scripts')
<script>
    // DearFlip location (local assets: images, sound, etc.)
    var dFlipLocation = "{{ asset('assets-dflip') }}/";

    // DearFlip options — must be defined BEFORE dflip.min.js loads
    var option_magazine_flipbook = {
        webgl: true,
        height: '100%',
        autoEnableOutline: false,
        autoEnableThumbnail: false,
        overwritePDFOutline: false,
        enableDownload: @json((bool) $magazine->allow_download),
        duration: 800,
        hard: 'none',
        soundEnable: @json((bool) $magazine->enable_sound),
        backgroundColor: '#3d3d3d',
        paddingTop: 50,
        paddingBottom: 5,
        paddingLeft: 60,
        paddingRight: 60,
        controlsPosition: 'bottom',
        allControls: 'thumbnail,outline,share,download,fullScreen,pageMode,startPage,endPage,sound,zoomIn,zoomOut',
        hideControls: '{{ $magazine->allow_download ? "" : "download" }}',
        pageMode: 2,
        singlePageMode: 2,
        direction: 1,
        scrollWheel: false,
        backgroundColor: 'rgb(61, 61, 61)',
        text: {
            toggleSound: 'Som',
            toggleThumbnails: 'Miniaturas',
            toggleOutline: 'Indice',
            previousPage: 'Anterior',
            nextPage: 'Proxima',
            toggleFullscreen: 'Tela cheia',
            zoomIn: 'Ampliar',
            zoomOut: 'Reduzir',
            share: 'Compartilhar',
        }
    };
</script>
<script src="{{ asset('assets-dflip/js/dflip.min.js') }}"></script>
<script>
    // Activate viewer mode
    document.body.classList.add('mag-viewer-active');

    var customPrev = document.getElementById('mag-arrow-prev');
    var customNext = document.getElementById('mag-arrow-next');

    // Posicionar as setas customizadas junto ao livro renderizado
    function positionArrows() {
        var container = document.querySelector('.mag-flipbook-wrap .df-container');
        if (!container) return false;

        var book = container.querySelector('.df-book-wrapper') || container.querySelector('.df-book-stage');
        if (!book || book.offsetWidth < 100) return false;

        var bookRect = book.getBoundingClientRect();

        // Posicionar no meio do gap entre o livro e a borda da tela
        var gapLeft = bookRect.left;
        var gapRight = window.innerWidth - bookRect.right;
        var leftPos = Math.max(20, (gapLeft / 2) - 27);
        var rightPos = Math.max(20, (gapRight / 2) - 27);

        customPrev.style.left = leftPos + 'px';
        customPrev.style.right = 'auto';
        customNext.style.right = rightPos + 'px';
        customNext.style.left = 'auto';

        return true;
    }

    // Disparar proxima/anterior pagina clicando nas setas nativas (hidden)
    function triggerDflip(direction) {
        var container = document.querySelector('.mag-flipbook-wrap .df-container');
        if (!container) return;
        var btn = container.querySelector(direction === 'next' ? '.df-ui-next' : '.df-ui-prev');
        if (btn) btn.click();
    }

    customPrev.addEventListener('click', function() { triggerDflip('prev'); });
    customNext.addEventListener('click', function() { triggerDflip('next'); });

    // Navegacao por teclado
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') { triggerDflip('prev'); e.preventDefault(); }
        if (e.key === 'ArrowRight') { triggerDflip('next'); e.preventDefault(); }
    });

    // Tentar posicionar repetidamente ate o livro renderizar
    var posInterval = setInterval(function() {
        if (positionArrows()) {
            // Posicionou com sucesso, continua monitorando mas com frequencia menor
        }
    }, 800);
    // Apos 15s, reduz frequencia
    setTimeout(function() {
        clearInterval(posInterval);
        posInterval = setInterval(positionArrows, 3000);
    }, 15000);

    // Reposicionar no resize
    window.addEventListener('resize', function() {
        setTimeout(positionArrows, 150);
    });

    // Cleanup on back navigation
    window.addEventListener('beforeunload', function() {
        document.body.classList.remove('mag-viewer-active');
    });
</script>
@endpush
