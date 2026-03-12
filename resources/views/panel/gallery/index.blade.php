@extends('panel.layouts.app')

@section('title', 'Galeria')

@section('panel_breadcrumb')
    <span class="text-blue-600 dark:text-blue-400 font-bold uppercase tracking-wider">Galeria</span>
@endsection

@section('panel_content')
    @php
        $isAdmin = auth()->user()->isAdmin();
        $selectedEventId = (int) request('event_id', 0);
        $selectedEventDate = $selectedEvent?->start_at
            ? \Carbon\Carbon::parse($selectedEvent->start_at)->format('d/m/Y')
            : null;
        $visibleLabel = $selectedEvent ? 'Resultados do filtro' : 'Fotos visiveis';
    @endphp

    <div class="space-y-6">
        @include('panel.gallery.partials.hero')
        @include('panel.gallery.partials.filter-bar')
        @include('panel.gallery.partials.cover-manager')
        @include('panel.gallery.partials.media-grid')
    </div>

    @include('panel.gallery.partials.upload-modal')
    @include('panel.gallery.partials.lightbox')
@endsection

@push('styles')
    <style>
        .gallery-modal-panel {
            animation: gallery-modal-enter 0.28s ease-out;
        }

        .gallery-dropzone.is-dragover {
            border-color: #2563eb;
            background: rgba(37, 99, 235, 0.06);
            transform: translateY(-2px);
            box-shadow: 0 24px 50px rgba(37, 99, 235, 0.12);
        }

        .lightbox-image {
            animation: gallery-lightbox-enter 0.24s ease-out;
        }

        .gallery-panel-pagination nav {
            display: flex;
            justify-content: center;
        }

        .gallery-panel-pagination nav > div:first-child {
            display: none;
        }

        .gallery-panel-pagination nav > div:last-child {
            width: 100%;
        }

        .gallery-panel-pagination nav > div:last-child > div,
        .gallery-panel-pagination nav > div:last-child > span {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .gallery-panel-pagination span[aria-current="page"] span,
        .gallery-panel-pagination a,
        .gallery-panel-pagination span[aria-disabled="true"] span {
            display: inline-flex;
            min-width: 3rem;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            padding: 0.85rem 1rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            transition: all 0.24s ease;
        }

        .gallery-panel-pagination a {
            border: 1px solid rgba(148, 163, 184, 0.24);
            background: rgba(255, 255, 255, 0.95);
            color: #0f172a;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        }

        .gallery-panel-pagination a:hover {
            transform: translateY(-2px);
            border-color: rgba(37, 99, 235, 0.24);
            background: #eff6ff;
            color: #1d4ed8;
        }

        .dark .gallery-panel-pagination a {
            border-color: rgba(51, 65, 85, 0.9);
            background: rgba(15, 23, 42, 0.88);
            color: #e2e8f0;
            box-shadow: none;
        }

        .dark .gallery-panel-pagination a:hover {
            background: rgba(30, 41, 59, 0.98);
            color: #93c5fd;
        }

        .gallery-panel-pagination span[aria-current="page"] span {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            box-shadow: 0 18px 30px rgba(37, 99, 235, 0.24);
        }

        .gallery-panel-pagination span[aria-disabled="true"] span {
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(248, 250, 252, 0.88);
            color: #94a3b8;
        }

        .dark .gallery-panel-pagination span[aria-disabled="true"] span {
            border-color: rgba(51, 65, 85, 0.9);
            background: rgba(15, 23, 42, 0.7);
            color: #64748b;
        }

        @keyframes gallery-modal-enter {
            from {
                opacity: 0;
                transform: translateY(16px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes gallery-lightbox-enter {
            from {
                opacity: 0;
                transform: scale(0.96);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
@endpush

@push('scripts')
    @include('panel.gallery.partials.scripts')
@endpush
