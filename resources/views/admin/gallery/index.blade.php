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
        $filteredMediaCount = (int) $media->total();
        $eventCoverageCount = (int) $events->filter(fn ($event) => (int) $event->media_count > 0)->count();
        $selectedEventMediaCount = $selectedEvent ? (int) ($selectedEvent->media_count ?? 0) : 0;
        $albumCoverRecommendation = '1600 x 900 px (16:9), em JPG ou WEBP.';
    @endphp

    <div class="container-fluid">
        <div class="row gallery-admin-summary">
            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($filteredMediaCount, 0, ',', '.') }}</h3>
                        <p>midia(s) listada(s)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-images"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($eventCoverageCount, 0, ',', '.') }}</h3>
                        <p>evento(s) com cobertura</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $selectedEvent ? number_format($selectedEventMediaCount, 0, ',', '.') : number_format($events->count(), 0, ',', '.') }}</h3>
                        <p>{{ $selectedEvent ? 'item(ns) no album filtrado' : 'album(ns) disponivel(is)' }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas {{ $selectedEvent ? 'fa-star' : 'fa-layer-group' }}"></i>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.gallery.partials.filter-card')

        @if($selectedEvent)
            @include('admin.gallery.partials.cover-manager')
        @else
            <div class="callout callout-info">
                <h5><i class="fas fa-image mr-2"></i>Gerenciamento da capa do album</h5>
                <p class="mb-0">
                    Selecione um evento acima para enviar uma capa personalizada ou usar uma foto existente como capa oficial.
                    Tamanho ideal para capa: <strong>{{ $albumCoverRecommendation }}</strong>
                </p>
            </div>
        @endif

        @include('admin.gallery.partials.media-grid')
    </div>

    @include('admin.gallery.partials.upload-modal')
@endsection

@push('styles')
    <style>
        .gallery-admin-summary .small-box .icon {
            font-size: 62px;
            top: 12px;
        }

        .gallery-admin-cover-preview {
            width: 100%;
            max-height: 320px;
            object-fit: cover;
            background: #f4f6f9;
        }

        .gallery-admin-thumb {
            position: relative;
            overflow: hidden;
            padding-top: 66%;
            background: #f4f6f9;
        }

        .gallery-admin-thumb > img,
        .gallery-admin-thumb > a,
        .gallery-admin-thumb > .gallery-admin-video-link {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }

        .gallery-admin-thumb img {
            object-fit: contain;
            background: #fff;
            padding: .5rem;
        }

        .gallery-admin-video-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            background: #343a40;
            color: #fff;
            text-decoration: none;
        }

        .gallery-admin-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            overflow: hidden;
            border-radius: 999px;
            background: #6c757d;
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
        }

        .gallery-admin-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-admin-dropzone {
            border: 2px dashed #ced4da;
            border-radius: .5rem;
            background: #f8f9fa;
            cursor: pointer;
            transition: border-color .2s ease, background-color .2s ease;
        }

        .gallery-admin-dropzone-label {
            min-height: 220px;
            width: 100%;
            cursor: pointer;
        }

        .gallery-admin-inline-preview {
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px dashed #dee2e6;
        }

        .gallery-admin-inline-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(92px, 1fr));
            gap: .75rem;
        }

        .gallery-admin-inline-preview-item {
            position: relative;
            overflow: hidden;
            border-radius: .9rem;
            border: 1px solid #dee2e6;
            background: #fff;
            aspect-ratio: 1 / 1;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
        }

        .gallery-admin-inline-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .gallery-admin-dropzone.dragover {
            border-color: #007bff;
            background: #eef5ff;
        }

        .gallery-admin-selected-list {
            max-height: 240px;
            overflow-y: auto;
        }

        .gallery-admin-selected-item {
            gap: .85rem;
        }

        .gallery-admin-selected-preview {
            width: 60px;
            height: 60px;
            border-radius: .75rem;
            overflow: hidden;
            flex-shrink: 0;
            background: #e9ecef;
            border: 1px solid #dee2e6;
        }

        .gallery-admin-selected-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .gallery-admin-pagination .pagination {
            margin-bottom: 0;
        }

        .gallery-admin-actions {
            gap: .5rem;
        }

        @media (max-width: 767.98px) {
            .gallery-admin-actions {
                flex-direction: column;
            }
        }
    </style>
@endpush

@push('scripts')
    @include('admin.gallery.partials.scripts')
@endpush
