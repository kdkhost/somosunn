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
        $heroEvent = $selectedEvent ?: $events->firstWhere('media_count', '>', 0) ?: $events->first();
        $heroCoverUrl = $heroEvent?->gallery_cover_url ?: asset('img/logo.svg');
        $heroEventDate = $heroEvent?->start_at
            ? \Carbon\Carbon::parse($heroEvent->start_at)->translatedFormat('d \d\e F \d\e Y')
            : 'Cobertura ativa';
        $filteredMediaCount = $media->total();
        $eventCoverageCount = $events->filter(fn ($event) => (int) $event->media_count > 0)->count();
        $selectedEventMediaCount = $selectedEvent ? (int) ($selectedEvent->media_count ?? 0) : null;
    @endphp

    <div class="container-fluid gallery-admin-shell">
        <section class="gallery-admin-hero mb-4">
            <div class="gallery-admin-hero__backdrop">
                <img src="{{ $heroCoverUrl }}" alt="{{ $heroEvent?->title ?: 'Galeria' }}">
            </div>
            <div class="gallery-admin-hero__overlay"></div>

            <div class="gallery-admin-hero__content">
                <div class="gallery-admin-hero__copy">
                    <span class="gallery-admin-pill">
                        <i class="fas fa-images"></i>
                        Galeria central da plataforma
                    </span>

                    <h1 class="gallery-admin-hero__title">
                        {{ $selectedEvent ? 'Album filtrado com gestao premium' : 'Curadoria visual dos eventos em um painel mais vivo' }}
                    </h1>

                    <p class="gallery-admin-hero__text">
                        {{ $selectedEvent
                            ? 'Gerencie a capa, revise a cobertura do evento e mantenha o album com a mesma linguagem premium da vitrine publica.'
                            : 'Agora a galeria do admin tambem assume uma direcao mais editorial, com foco em capa, contexto do evento e leitura rapida da cobertura.' }}
                    </p>

                    <div class="gallery-admin-hero__chips">
                        <span class="gallery-admin-chip">
                            <i class="fas fa-photo-video text-info"></i>
                            {{ number_format($filteredMediaCount, 0, ',', '.') }} midias visiveis
                        </span>
                        <span class="gallery-admin-chip">
                            <i class="fas fa-calendar-check text-primary"></i>
                            {{ number_format($eventCoverageCount, 0, ',', '.') }} eventos com cobertura
                        </span>
                        @if($selectedEvent)
                            <span class="gallery-admin-chip">
                                <i class="fas fa-star text-warning"></i>
                                {{ number_format($selectedEventMediaCount, 0, ',', '.') }} item(ns) neste album
                            </span>
                        @endif
                    </div>
                </div>

                <div class="gallery-admin-hero__aside">
                    <div class="gallery-admin-stat-card">
                        <p class="gallery-admin-stat-card__label">Evento em foco</p>
                        <p class="gallery-admin-stat-card__title">{{ $heroEvent?->title ?: 'Galeria SOMOS UNN' }}</p>
                        <p class="gallery-admin-stat-card__meta">{{ $heroEventDate }}</p>
                    </div>

                    <div class="gallery-admin-stat-grid">
                        <div class="gallery-admin-stat-box">
                            <span class="gallery-admin-stat-box__value">{{ number_format($events->count(), 0, ',', '.') }}</span>
                            <span class="gallery-admin-stat-box__label">albuns listados</span>
                        </div>
                        <div class="gallery-admin-stat-box">
                            <span class="gallery-admin-stat-box__value">{{ number_format($filteredMediaCount, 0, ',', '.') }}</span>
                            <span class="gallery-admin-stat-box__label">registros</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

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
        .gallery-admin-shell {
            padding-bottom: 1.25rem;
        }

        .gallery-admin-hero {
            position: relative;
            overflow: hidden;
            border-radius: 2rem;
            color: #fff;
            background: #020617;
            box-shadow: 0 32px 90px rgba(15, 23, 42, 0.24);
        }

        .gallery-admin-hero__backdrop,
        .gallery-admin-hero__overlay {
            position: absolute;
            inset: 0;
        }

        .gallery-admin-hero__backdrop img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.18;
        }

        .gallery-admin-hero__overlay {
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.34), transparent 28%),
                radial-gradient(circle at 82% 14%, rgba(14, 165, 233, 0.2), transparent 24%),
                linear-gradient(135deg, rgba(2, 6, 23, 0.98), rgba(15, 23, 42, 0.94));
        }

        .gallery-admin-hero__content {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(300px, 0.9fr);
            gap: 1.5rem;
            padding: 2rem;
        }

        .gallery-admin-hero__copy {
            max-width: 62rem;
        }

        .gallery-admin-pill,
        .gallery-admin-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(12px);
        }

        .gallery-admin-pill {
            padding: 0.75rem 1rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.92);
        }

        .gallery-admin-hero__title {
            margin: 1.4rem 0 0;
            font-size: clamp(2rem, 4vw, 3.4rem);
            line-height: 1.02;
            font-weight: 900;
            letter-spacing: -0.04em;
            color: #fff;
        }

        .gallery-admin-hero__text {
            max-width: 52rem;
            margin: 1.2rem 0 0;
            font-size: 1rem;
            line-height: 1.95;
            color: rgba(226, 232, 240, 0.9);
        }

        .gallery-admin-hero__chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem;
            margin-top: 1.5rem;
        }

        .gallery-admin-chip {
            padding: 0.9rem 1rem;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.88);
        }

        .gallery-admin-hero__aside {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .gallery-admin-stat-card,
        .gallery-admin-stat-box,
        .gallery-admin-surface {
            border: 1px solid rgba(226, 232, 240, 0.7);
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
        }

        .gallery-admin-stat-card {
            border-radius: 1.6rem;
            padding: 1.25rem;
            color: #0f172a;
        }

        .gallery-admin-stat-card__label {
            margin: 0;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #64748b;
        }

        .gallery-admin-stat-card__title {
            margin: 0.85rem 0 0;
            font-size: 1.55rem;
            line-height: 1.2;
            font-weight: 900;
            color: #020617;
        }

        .gallery-admin-stat-card__meta {
            margin: 0.65rem 0 0;
            font-size: 0.95rem;
            color: #475569;
        }

        .gallery-admin-stat-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .gallery-admin-stat-box {
            border-radius: 1.5rem;
            padding: 1rem 1.1rem;
            color: #0f172a;
        }

        .gallery-admin-stat-box__value {
            display: block;
            font-size: 2rem;
            font-weight: 900;
            line-height: 1;
            color: #0f172a;
        }

        .gallery-admin-stat-box__label {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #64748b;
        }

        .gallery-admin-surface {
            border-radius: 1.9rem;
            overflow: hidden;
        }

        .gallery-admin-section-title {
            margin: 0;
            font-size: 2rem;
            line-height: 1.1;
            font-weight: 900;
            color: #020617;
        }

        .gallery-admin-section-eyebrow {
            margin: 0;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #2563eb;
        }

        .gallery-admin-subtext {
            color: #64748b;
            line-height: 1.85;
        }

        .gallery-admin-primary-btn,
        .gallery-admin-secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            border-radius: 999px;
            padding: 0.95rem 1.45rem;
            font-size: 0.8rem;
            font-weight: 900;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            transition: all 0.24s ease;
        }

        .gallery-admin-primary-btn {
            color: #fff !important;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 18px 40px rgba(37, 99, 235, 0.22);
            border: 1px solid rgba(37, 99, 235, 0.35);
        }

        .gallery-admin-primary-btn:hover {
            transform: translateY(-2px);
            color: #fff !important;
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
        }

        .gallery-admin-secondary-btn {
            color: #334155 !important;
            background: #fff;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .gallery-admin-secondary-btn:hover {
            transform: translateY(-2px);
            color: #0f172a !important;
            background: #f8fafc;
        }

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

        .gallery-admin-pagination .pagination {
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .gallery-admin-pagination .page-link {
            display: inline-flex;
            min-width: 2.9rem;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(148, 163, 184, 0.24);
            border-radius: 999px !important;
            padding: 0.75rem 1rem;
            font-weight: 800;
            color: #0f172a;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        }

        .gallery-admin-pagination .page-item.active .page-link {
            color: #fff;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-color: rgba(37, 99, 235, 0.3);
            box-shadow: 0 18px 30px rgba(37, 99, 235, 0.22);
        }

        .gallery-admin-pagination .page-item.disabled .page-link {
            color: #94a3b8;
            background: rgba(248, 250, 252, 0.9);
            box-shadow: none;
        }

        .gallery-admin-modal .modal-content {
            border-radius: 1.9rem;
            overflow: hidden;
            box-shadow: 0 28px 80px rgba(15, 23, 42, 0.22);
        }

        .gallery-admin-modal__header {
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            background: linear-gradient(180deg, #fff, #f8fafc);
        }

        .gallery-admin-media-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.78);
            border-radius: 1.9rem;
            background: #fff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .gallery-admin-media-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 30px 70px rgba(15, 23, 42, 0.14);
        }

        .gallery-admin-media-card__preview {
            position: relative;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            background: #020617;
        }

        .gallery-admin-media-card__preview-link {
            display: block;
            width: 100%;
            height: 100%;
        }

        .gallery-admin-media-card__image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .gallery-admin-media-card:hover .gallery-admin-media-card__image {
            transform: scale(1.04);
        }

        .gallery-admin-video-preview {
            display: flex;
            width: 100%;
            height: 100%;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, 0.22), transparent 28%),
                linear-gradient(135deg, #020617, #0f172a);
            color: #fff;
        }

        .gallery-admin-video-preview__icon {
            display: inline-flex;
            width: 4.4rem;
            height: 4.4rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            font-size: 1.2rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
        }

        .gallery-admin-video-preview__label {
            margin-top: 1rem;
            font-size: 0.8rem;
            font-weight: 900;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.84);
        }

        .gallery-admin-media-card__overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.06), rgba(15, 23, 42, 0.78));
            pointer-events: none;
        }

        .gallery-admin-media-card__badges {
            position: absolute;
            top: 1rem;
            left: 1rem;
            right: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            z-index: 2;
        }

        .gallery-admin-media-card__preview-cta {
            position: absolute;
            left: 1rem;
            right: 1rem;
            bottom: 1rem;
            z-index: 2;
        }

        .gallery-admin-preview-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.52);
            padding: 0.8rem 1rem;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #fff;
            backdrop-filter: blur(10px);
        }

        .gallery-admin-media-card__body {
            padding: 1.25rem;
            flex: 1 1 auto;
        }

        .gallery-admin-avatar {
            position: relative;
            display: inline-flex;
            width: 3rem;
            height: 3rem;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 1rem;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            color: #fff;
            font-size: 0.92rem;
            font-weight: 900;
            text-transform: uppercase;
            box-shadow: 0 18px 35px rgba(37, 99, 235, 0.18);
        }

        .gallery-admin-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-admin-media-card__meta-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .gallery-admin-meta-box {
            border-radius: 1.2rem;
            background: #f8fafc;
            padding: 0.95rem 1rem;
        }

        .gallery-admin-meta-box__label {
            margin: 0;
            font-size: 0.68rem;
            font-weight: 900;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .gallery-admin-meta-box__value {
            margin: 0.45rem 0 0;
            font-size: 0.92rem;
            line-height: 1.55;
            font-weight: 800;
            color: #0f172a;
        }

        .gallery-admin-primary-btn--cover {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-color: rgba(217, 119, 6, 0.36);
            box-shadow: 0 18px 35px rgba(245, 158, 11, 0.18);
        }

        .gallery-admin-primary-btn--cover:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
        }

        .gallery-admin-delete-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid rgba(251, 113, 133, 0.3);
            background: rgba(255, 241, 242, 0.98);
            padding: 0.95rem 1.15rem;
            font-size: 0.78rem;
            font-weight: 900;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #e11d48;
            transition: all 0.24s ease;
        }

        .gallery-admin-delete-btn:hover {
            transform: translateY(-2px);
            background: #ffe4e6;
            color: #be123c;
        }

        .gallery-admin-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 1px dashed rgba(148, 163, 184, 0.35);
            border-radius: 2rem;
            background: linear-gradient(180deg, #fff, #f8fafc);
            padding: 3rem 1.5rem;
        }

        .gallery-admin-empty-state__icon {
            display: inline-flex;
            width: 6rem;
            height: 6rem;
            align-items: center;
            justify-content: center;
            border-radius: 2rem;
            background: rgba(15, 23, 42, 0.04);
            color: #94a3b8;
            font-size: 2rem;
        }

        @media (max-width: 1199.98px) {
            .gallery-admin-hero__content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .gallery-admin-hero__content {
                padding: 1.5rem;
            }

            .gallery-admin-stat-grid {
                grid-template-columns: 1fr;
            }

            .gallery-admin-media-card__meta-grid {
                grid-template-columns: 1fr;
            }

            .gallery-admin-primary-btn,
            .gallery-admin-secondary-btn {
                width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    @include('admin.gallery.partials.scripts')
@endpush
