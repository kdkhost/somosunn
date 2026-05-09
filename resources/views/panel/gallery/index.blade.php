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
        $visibleLabel = $selectedEvent ? 'Resultados do filtro' : 'Mídias visíveis';
    @endphp

    <div class="space-y-4">
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
            animation: gallery-modal-enter 0.2s ease-out;
        }

        .gallery-dropzone.is-dragover {
            border-color: #2563eb;
            background: rgba(37, 99, 235, 0.04);
        }

        .lightbox-image {
            animation: gallery-lightbox-enter 0.2s ease-out;
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
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        .gallery-panel-pagination .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            flex-wrap: wrap;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .gallery-panel-pagination .page-item .page-link,
        .gallery-panel-pagination span[aria-current="page"] span,
        .gallery-panel-pagination a,
        .gallery-panel-pagination span[aria-disabled="true"] span {
            display: inline-flex;
            min-width: 2rem;
            height: 2rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0 0.5rem;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .gallery-panel-pagination .page-item .page-link,
        .gallery-panel-pagination a {
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #334155;
        }

        .gallery-panel-pagination .page-item .page-link:hover,
        .gallery-panel-pagination a:hover {
            border-color: #3b82f6;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .gallery-panel-pagination .page-item.active .page-link,
        .gallery-panel-pagination span[aria-current="page"] span {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .gallery-panel-pagination .page-item.disabled .page-link,
        .gallery-panel-pagination span[aria-disabled="true"] span {
            border: 1px solid #f1f5f9;
            background: #f8fafc;
            color: #94a3b8;
            pointer-events: none;
        }

        .dark .gallery-panel-pagination .page-item .page-link,
        .dark .gallery-panel-pagination a {
            border-color: #334155;
            background: #1e293b;
            color: #e2e8f0;
        }

        .dark .gallery-panel-pagination .page-item .page-link:hover,
        .dark .gallery-panel-pagination a:hover {
            background: #334155;
            color: #93c5fd;
        }

        .dark .gallery-panel-pagination .page-item.active .page-link,
        .dark .gallery-panel-pagination span[aria-current="page"] span {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .dark .gallery-panel-pagination .page-item.disabled .page-link,
        .dark .gallery-panel-pagination span[aria-disabled="true"] span {
            border-color: #1e293b;
            background: #0f172a;
            color: #475569;
        }

        @keyframes gallery-modal-enter {
            from { opacity: 0; transform: translateY(8px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes gallery-lightbox-enter {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
@endpush

@push('scripts')
    @include('panel.gallery.partials.scripts')
@endpush
