@extends('layouts.app')

@section('title', $magazine->title . ' - Revista')

@push('styles')
<link href="{{ asset('assets-dflip/css/dflip.min.css') }}" rel="stylesheet" type="text/css">
<style>
    .mag-viewer {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: #111;
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
        backgroundColor: '#111111',
        paddingTop: 60,
        paddingBottom: 10,
        paddingLeft: 20,
        paddingRight: 20,
        controlsPosition: 'bottom',
        allControls: 'thumbnail,outline,share,download,fullScreen,pageMode,startPage,endPage,sound',
        hideControls: '{{ $magazine->allow_download ? "" : "download" }}',
        pageMode: 2,
        singlePageMode: 2,
        direction: 1,
        text: {
            toggleSound: 'Som',
            toggleThumbnails: 'Miniaturas',
            toggleOutline: 'Indice',
            previousPage: 'Anterior',
            nextPage: 'Proxima',
            toggleFullscreen: 'Tela cheia',
            zoomIn: 'Ampliar',
            zoomOut: 'Reduzir',
        }
    };
</script>
<script src="{{ asset('assets-dflip/js/dflip.min.js') }}"></script>
<script>
    // Activate viewer mode
    document.body.classList.add('mag-viewer-active');

    // Cleanup on back navigation
    window.addEventListener('beforeunload', function() {
        document.body.classList.remove('mag-viewer-active');
    });
</script>
@endpush
