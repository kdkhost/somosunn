@extends('layouts.app')

@section('title', $magazine->title . ' - Revista')

@push('styles')
<link href="{{ asset('assets-3dflipbook/css/black-book-view.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets-3dflipbook/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">
<style>
    .mag-viewer {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: #1a1a2e;
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
        background: linear-gradient(180deg, rgba(0,0,0,0.85) 0%, transparent 100%);
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

    .mag-info { text-align: right; }
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

    /* Override 3D FlipBook container to fill */
    .mag-flipbook-wrap .flip-book-container {
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

    {{-- 3D FlipBook container --}}
    <div class="mag-flipbook-wrap">
        <div class="flip-book-container" id="magazine-3dflipbook" src="{{ $magazine->pdf_url }}"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets-3dflipbook/js/three.min.js') }}"></script>
<script src="{{ asset('assets-3dflipbook/js/pdf.min.js') }}"></script>
<script src="{{ asset('assets-3dflipbook/js/html2canvas.min.js') }}"></script>
<script src="{{ asset('assets-3dflipbook/js/flip-book.min.js') }}"></script>
<script>
(function() {
    // Activate viewer mode
    document.body.classList.add('mag-viewer-active');

    // Set base path for templates/images/sounds
    if (typeof defined !== 'undefined' || true) {
        // 3D FlipBook auto-detects paths from the script location
        // but we need to override for our custom structure
    }

    // Initialize via jQuery
    $(function() {
        var container = $('#magazine-3dflipbook');
        var pdfUrl = container.attr('src');

        container.FlipBook({
            pdf: pdfUrl,
            template: {
                html: '{{ asset("assets-3dflipbook/templates/default-book-view.html") }}',
                styles: [
                    '{{ asset("assets-3dflipbook/css/black-book-view.css") }}',
                    '{{ asset("assets-3dflipbook/css/font-awesome.min.css") }}'
                ],
                sounds: {
                    startFlip: '{{ asset("assets-3dflipbook/sounds/start-flip.mp3") }}',
                    endFlip: '{{ asset("assets-3dflipbook/sounds/end-flip.mp3") }}'
                },
                images: {
                    loader: '{{ asset("assets-3dflipbook/images/dark-loader.gif") }}'
                }
            },
            controlsProps: {
                enableFullScreen: true,
                enableDownload: @json((bool) $magazine->allow_download),
                enableSound: @json((bool) $magazine->enable_sound),
                enableThumbnails: true,
                thumbnails: {
                    autoBuild: true
                }
            },
            propertiesProps: {
                autoPlay: {
                    enabled: false
                },
                startPage: 1
            },
            pdfVersion: '2.3.200'
        });
    });

    // Cleanup
    window.addEventListener('beforeunload', function() {
        document.body.classList.remove('mag-viewer-active');
    });
})();
</script>
@endpush
