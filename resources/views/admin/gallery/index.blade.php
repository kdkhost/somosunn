@extends('admin.layouts.app')

@section('title', 'Galeria de Fotos')

@section('page_title', 'Galeria de Fotos')

@section('breadcrumb_items')
    <li class="breadcrumb-item active">Galeria</li>
@endsection

@section('content')
    @php
        $selectedEventId = (int) request('event_id', 0);
        $selectedEventDate = $selectedEvent?->start_at
            ? \Carbon\Carbon::parse($selectedEvent->start_at)->format('d/m/Y H:i')
            : null;
        $selectedCoverUrl = $selectedEvent?->gallery_cover_url ?: asset('img/logo.svg');
        $selectedHasCustomCover = $selectedEvent && !blank($selectedEvent->gallery_cover_image);
    @endphp

    <div class="container-fluid">
        @include('admin.gallery.partials.filter-card')

        @if($selectedEvent)
            @include('admin.gallery.partials.cover-manager')
        @endif

        @include('admin.gallery.partials.media-grid')
    </div>

    @include('admin.gallery.partials.upload-modal')
@endsection

@push('styles')
    <style>
        .gallery-card {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .gallery-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .object-fit-cover {
            object-fit: cover;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .overlay-actions {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            opacity: 0;
            transition: opacity 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gallery-card:hover .overlay-actions {
            opacity: 1;
        }

        .gallery-card:hover img {
            transform: scale(1.05);
        }

        .truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gallery-cover-preview {
            position: relative;
            min-height: 260px;
            background: #0f172a;
        }

        .gallery-cover-preview img {
            width: 100%;
            height: 100%;
            min-height: 260px;
        }

        .gallery-cover-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: flex-end;
            padding: 1rem;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.08), rgba(15, 23, 42, 0.72));
        }

        .premium-upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 1.25rem;
            padding: 2.5rem 1.5rem;
            background: #f8fafc;
            transition: all 0.24s ease;
            cursor: pointer;
        }

        .premium-upload-box.dragover {
            border-color: #2563eb;
            background: rgba(37, 99, 235, 0.06);
            transform: translateY(-2px);
        }

        .drop-zone-area {
            text-align: center;
        }
    </style>
@endpush

@push('scripts')
    @include('admin.gallery.partials.scripts')
@endpush
