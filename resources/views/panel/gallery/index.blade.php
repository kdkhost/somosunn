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
