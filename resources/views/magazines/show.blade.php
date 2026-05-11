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
    /* Setas laterais junto a borda da pagina do livro */
    .mag-flipbook-wrap .df-container > .df-ui-next.df-ui-btn,
    .mag-flipbook-wrap .df-container > .df-ui-prev.df-ui-btn,
    .mag-flipbook-wrap .df-container.df-floating > .df-ui-next,
    .mag-flipbook-wrap .df-container.df-floating > .df-ui-prev {
        position: absolute !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        -webkit-transform: translateY(-50%) !important;
        opacity: 0.85 !important;
        font-size: 32px !important;
        color: #fff !important;
        text-shadow: 0 2px 6px rgba(0,0,0,0.6) !important;
        width: 44px !important;
        height: 44px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: rgba(0,0,0,0.4) !important;
        border-radius: 50% !important;
        border: none !important;
        backdrop-filter: blur(4px) !important;
        transition: all 0.2s ease !important;
        margin: 0 !important;
        padding: 0 !important;
        z-index: 5 !important;
    }
    .mag-flipbook-wrap .df-container > .df-ui-next.df-ui-btn,
    .mag-flipbook-wrap .df-container.df-floating > .df-ui-next {
        right: 12px !important;
        left: auto !important;
    }
    .mag-flipbook-wrap .df-container > .df-ui-prev.df-ui-btn,
    .mag-flipbook-wrap .df-container.df-floating > .df-ui-prev {
        left: 12px !important;
        right: auto !important;
    }
    .mag-flipbook-wrap .df-container > .df-ui-next:hover,
    .mag-flipbook-wrap .df-container > .df-ui-prev:hover {
        opacity: 1 !important;
        background: rgba(0,0,0,0.7) !important;
        transform: translateY(-50%) scale(1.15) !important;
        -webkit-transform: translateY(-50%) scale(1.15) !important;
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

    // Reposition arrows next to the book edges (not screen edges)
    function fixArrows() {
        var container = document.querySelector('.mag-flipbook-wrap .df-container');
        var nextBtn = container ? container.querySelector('.df-ui-next') : null;
        var prevBtn = container ? container.querySelector('.df-ui-prev') : null;
        // Find the book wrapper element
        var bookWrapper = container ? container.querySelector('.df-book-wrapper') : null;

        if (!nextBtn || !prevBtn || !container) return;

        if (bookWrapper) {
            var containerRect = container.getBoundingClientRect();
            var bookRect = bookWrapper.getBoundingClientRect();

            // Position prev arrow at left edge of book - arrow width - gap
            var prevLeft = (bookRect.left - containerRect.left) - 52;
            if (prevLeft < 4) prevLeft = 4;

            // Position next arrow at right edge of book + gap
            var nextRight = (containerRect.right - bookRect.right) - 52;
            if (nextRight < 4) nextRight = 4;

            prevBtn.style.cssText = 'position:absolute;top:50%;left:' + prevLeft + 'px;right:auto;transform:translateY(-50%);width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.4);border-radius:50%;color:#fff;font-size:32px;opacity:0.85;border:none;z-index:5;margin:0;padding:0;cursor:pointer;';
            nextBtn.style.cssText = 'position:absolute;top:50%;right:' + nextRight + 'px;left:auto;transform:translateY(-50%);width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.4);border-radius:50%;color:#fff;font-size:32px;opacity:0.85;border:none;z-index:5;margin:0;padding:0;cursor:pointer;';
        } else {
            // Fallback: if no book wrapper found, use percentage-based positioning
            prevBtn.style.cssText = 'position:absolute;top:50%;left:15%;transform:translateY(-50%);width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.4);border-radius:50%;color:#fff;font-size:32px;opacity:0.85;border:none;z-index:5;margin:0;padding:0;cursor:pointer;';
            nextBtn.style.cssText = 'position:absolute;top:50%;right:15%;left:auto;transform:translateY(-50%);width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.4);border-radius:50%;color:#fff;font-size:32px;opacity:0.85;border:none;z-index:5;margin:0;padding:0;cursor:pointer;';
        }
    }

    // Run multiple times to catch DearFlip's delayed rendering
    setTimeout(fixArrows, 1500);
    setTimeout(fixArrows, 3000);
    setTimeout(fixArrows, 5000);
    setTimeout(fixArrows, 10000);
    // Also fix on window resize
    window.addEventListener('resize', function() { setTimeout(fixArrows, 300); });

    // Cleanup on back navigation
    window.addEventListener('beforeunload', function() {
        document.body.classList.remove('mag-viewer-active');
    });
</script>
@endpush
