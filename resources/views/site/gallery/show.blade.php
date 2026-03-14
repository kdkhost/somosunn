@extends('layouts.app')

@section('title', 'Galeria: ' . $event->title . ' - SOMOS UNN')

@php
    $coverUrl    = $event->gallery_cover_url ?: ($featuredPhoto ? \App\Support\UploadStorage::url($featuredPhoto->file_path) : ($event->image_url ?: asset('img/logo.svg')));
    $eventDate   = $event->start_at ? \Carbon\Carbon::parse($event->start_at)->translatedFormat('d \d\e F \d\e Y') : null;
    $photoSlides = $photos->map(fn($item) => [
        'src'     => \App\Support\UploadStorage::url($item->file_path),
        'title'   => $event->title,
        'caption' => $item->created_at ? $item->created_at->format('d/m/Y H:i') : '',
    ])->values();
@endphp

@section('meta_image', $coverUrl)
@section('meta_description', 'Galeria do evento ' . $event->title . ' - fotos e videos da cobertura oficial.')

@section('content')
    {{-- Hero --}}
    <div class="relative overflow-hidden bg-slate-950 pt-28 pb-16 text-white">
        <div class="absolute inset-0">
            <img src="{{ $coverUrl }}" alt="{{ $event->title }}" class="h-full w-full object-cover opacity-20">
            <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(2,6,23,0.94),rgba(15,23,42,0.92))]"></div>
            <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.1),rgba(15,23,42,0.92))]"></div>
        </div>

        <div class="container relative mx-auto px-4">
            <a href="{{ route('gallery.index') }}"
                class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-bold text-white/80 backdrop-blur transition hover:bg-white/10 hover:text-white">
                <i class="fas fa-arrow-left text-xs"></i> Voltar para a galeria
            </a>

            <div class="mt-8 max-w-4xl">
                <span class="inline-flex items-center gap-3 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-4 py-2 text-xs font-black uppercase tracking-[0.24em] text-cyan-100">
                    <i class="fas fa-photo-film"></i> Cobertura oficial
                </span>

                <h1 class="mt-5 text-4xl font-black tracking-tight text-white md:text-5xl">{{ $event->title }}</h1>

                <div class="mt-5 flex flex-wrap gap-3 text-sm text-slate-300">
                    @if($eventDate)
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 backdrop-blur">
                            <i class="far fa-calendar"></i> {{ $eventDate }}
                        </span>
                    @endif
                    @if($event->location)
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 backdrop-blur">
                            <i class="fas fa-location-dot"></i> {{ $event->location }}
                        </span>
                    @endif
                    @if($photoCount > 0)
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 backdrop-blur">
                            <i class="fas fa-images text-cyan-300"></i> {{ $photoCount }} {{ $photoCount === 1 ? 'foto' : 'fotos' }}
                        </span>
                    @endif
                    @if($videoCount > 0)
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 backdrop-blur">
                            <i class="fas fa-circle-play text-fuchsia-300"></i> {{ $videoCount }} {{ $videoCount === 1 ? 'video' : 'videos' }}
                        </span>
                    @endif
                </div>

                @if($photoCount > 0 || $videoCount > 0)
                    <div class="mt-7 flex flex-wrap gap-3">
                        @if($photoCount > 0)
                            <a href="#galeria-fotos" class="inline-flex items-center gap-3 rounded-full bg-white px-6 py-3 text-sm font-black uppercase tracking-[0.16em] text-slate-950 transition hover:-translate-y-0.5 hover:bg-slate-100">
                                <i class="fas fa-images text-blue-600"></i> Ver fotos
                            </a>
                        @endif
                        @if($videoCount > 0)
                            <a href="#galeria-videos" class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-6 py-3 text-sm font-black uppercase tracking-[0.16em] text-white backdrop-blur transition hover:bg-white/10">
                                <i class="fas fa-circle-play text-fuchsia-300"></i> Ver videos
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Conteudo --}}
    <div class="relative bg-[linear-gradient(180deg,#f8fbff_0%,#eef4ff_38%,#ffffff_100%)] pb-24">
        <div class="container relative mx-auto -mt-8 space-y-8 px-4">

            {{-- Fotos --}}
            @if($photoCount > 0)
                <section id="galeria-fotos" class="overflow-hidden rounded-[2.5rem] border border-slate-200/80 bg-white p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] md:p-8">
                    <div class="mb-6 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600">Fotos</p>
                            <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $photoCount }} {{ $photoCount === 1 ? 'foto publicada' : 'fotos publicadas' }}</h2>
                        </div>
                        @if($photos->hasPages())
                            <span class="text-sm text-slate-400">Pagina {{ $photos->currentPage() }} de {{ $photos->lastPage() }}</span>
                        @endif
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($photos as $item)
                            @php $photoUrl = \App\Support\UploadStorage::url($item->file_path); @endphp
                            <button type="button"
                                data-gallery-open-slide="{{ $loop->index }}"
                                class="group relative overflow-hidden rounded-[1.8rem] border border-slate-200 bg-slate-950 text-left shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_50px_rgba(15,23,42,0.16)]">
                                <img src="{{ $photoUrl }}" alt="{{ $event->title }}"
                                    class="h-64 w-full object-cover transition duration-700 group-hover:scale-[1.04]">
                                <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.0),rgba(15,23,42,0.55))]"></div>
                                <div class="absolute right-4 top-4">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-[1.1rem] border border-white/10 bg-white/10 text-white backdrop-blur">
                                        <i class="fas fa-expand text-sm"></i>
                                    </span>
                                </div>
                                <div class="absolute inset-x-4 bottom-4">
                                    <p class="text-xs font-bold text-slate-300">
                                        {{ $item->created_at ? $item->created_at->format('d/m/Y') : '' }}
                                    </p>
                                </div>
                            </button>
                        @endforeach
                    </div>

                    @if($photos->hasPages())
                        <div class="gallery-event-pagination mt-8">
                            {{ $photos->onEachSide(1)->links('pagination::tailwind') }}
                        </div>
                    @endif
                </section>
            @endif

            {{-- Videos --}}
            @if($videoCount > 0)
                <section id="galeria-videos" class="overflow-hidden rounded-[2.5rem] border border-slate-200/80 bg-white p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] md:p-8">
                    <div class="mb-6">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-fuchsia-600">Videos</p>
                        <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $videoCount }} {{ $videoCount === 1 ? 'video publicado' : 'videos publicados' }}</h2>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        @foreach($videos as $item)
                            @php $videoUrl = \App\Support\UploadStorage::url($item->file_path); @endphp
                            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-950 shadow-[0_20px_50px_rgba(15,23,42,0.16)]">
                                <video controls preload="metadata" playsinline data-gallery-video
                                    poster="{{ $coverUrl }}"
                                    class="h-72 w-full bg-slate-950 object-cover">
                                    <source src="{{ $videoUrl }}">
                                    Seu navegador nao suporta reproducao de video.
                                </video>
                                <div class="p-5">
                                    <h3 class="font-black text-white">{{ $event->title }}</h3>
                                    @if($item->created_at)
                                        <p class="mt-1 text-sm text-slate-400">{{ $item->created_at->format('d/m/Y H:i') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($videos->hasPages())
                        <div class="gallery-event-pagination mt-8">
                            {{ $videos->onEachSide(1)->links('pagination::tailwind') }}
                        </div>
                    @endif
                </section>
            @endif

            {{-- Eventos relacionados --}}
            @if($relatedEvents->isNotEmpty())
                <section class="overflow-hidden rounded-[2.5rem] border border-slate-200/80 bg-white p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] md:p-8">
                    <div class="mb-6 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600">Continuar explorando</p>
                            <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Outros eventos</h2>
                        </div>
                        <a href="{{ route('gallery.index') }}"
                            class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:text-slate-950">
                            <i class="fas fa-grid-2"></i> Ver todos
                        </a>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-3">
                        @foreach($relatedEvents as $relatedEvent)
                            <a href="{{ route('gallery.show', $relatedEvent) }}"
                                class="group overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-950 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_50px_rgba(15,23,42,0.18)]">
                                <div class="relative h-52 overflow-hidden">
                                    <img src="{{ $relatedEvent->gallery_cover_url ?: asset('img/logo.svg') }}" alt="{{ $relatedEvent->title }}"
                                        class="h-full w-full object-cover opacity-80 transition duration-700 group-hover:scale-[1.04] group-hover:opacity-100">
                                    <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.08),rgba(15,23,42,0.80))]"></div>
                                    <div class="absolute inset-x-5 bottom-5">
                                        @if($relatedEvent->start_at)
                                            <p class="text-xs font-black uppercase tracking-[0.18em] text-cyan-200">
                                                {{ \Carbon\Carbon::parse($relatedEvent->start_at)->format('d/m/Y') }}
                                            </p>
                                        @endif
                                        <h3 class="mt-2 text-xl font-black text-white">{{ \Illuminate\Support\Str::limit($relatedEvent->title, 42) }}</h3>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>

    {{-- Lightbox --}}
    @if(count($photoSlides) > 0)
        <div id="event-gallery-lightbox" class="fixed inset-0 z-[120] hidden">
            <div data-gallery-close class="absolute inset-0 bg-slate-950/95 backdrop-blur-md"></div>

            <div class="relative flex min-h-full items-center justify-center p-4 md:p-8">
                <button type="button" data-gallery-close
                    class="absolute right-4 top-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white transition hover:bg-white/20 md:right-8 md:top-8">
                    <i class="fas fa-xmark text-lg"></i>
                </button>

                <button type="button" id="event-gallery-prev"
                    class="absolute left-3 top-1/2 z-10 inline-flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white transition hover:bg-white/20 md:left-8 md:h-14 md:w-14">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <div class="w-full max-w-6xl">
                    <div class="overflow-hidden rounded-[2.2rem] border border-white/10 bg-slate-950/70 p-3 shadow-[0_30px_90px_rgba(15,23,42,0.55)]">
                        <img id="event-gallery-lightbox-image" src="" alt=""
                            class="max-h-[76vh] w-full rounded-[1.7rem] object-contain">
                    </div>
                    <div class="mt-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p id="event-gallery-counter" class="text-xs font-black uppercase tracking-[0.22em] text-slate-400"></p>
                            <p id="event-gallery-caption" class="mt-2 text-sm text-slate-300"></p>
                        </div>
                        <div id="event-gallery-thumbs" class="flex max-w-full gap-2 overflow-x-auto pb-2"></div>
                    </div>
                </div>

                <button type="button" id="event-gallery-next"
                    class="absolute right-3 top-1/2 z-10 inline-flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white transition hover:bg-white/20 md:right-8 md:h-14 md:w-14">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .gallery-event-pagination nav > div:first-child { display: none; }
        .gallery-event-pagination nav > div:last-child { display: flex; justify-content: center; }
        .gallery-event-pagination span[aria-current="page"] span,
        .gallery-event-pagination a,
        .gallery-event-pagination span[aria-disabled="true"] span { border-radius: 1rem; }
        #event-gallery-thumbs::-webkit-scrollbar { height: 6px; }
        #event-gallery-thumbs::-webkit-scrollbar-thumb { background: rgba(148,163,184,.45); border-radius: 999px; }
    </style>
@endpush

@push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const slides = @json($photoSlides);
        const lightbox = document.getElementById('event-gallery-lightbox');
        const image    = document.getElementById('event-gallery-lightbox-image');
        const caption  = document.getElementById('event-gallery-caption');
        const counter  = document.getElementById('event-gallery-counter');
        const thumbs   = document.getElementById('event-gallery-thumbs');
        const prevBtn  = document.getElementById('event-gallery-prev');
        const nextBtn  = document.getElementById('event-gallery-next');
        const videos   = Array.from(document.querySelectorAll('[data-gallery-video]'));
        let current    = 0;

        videos.forEach(v => v.addEventListener('play', () => videos.forEach(o => o !== v && o.pause())));

        function renderThumbs() {
            if (!thumbs || !slides.length) return;
            thumbs.innerHTML = slides.map((s, i) => `
                <button type="button" class="event-gallery-thumb shrink-0 overflow-hidden rounded-2xl border ${i === current ? 'border-white' : 'border-white/10 opacity-60'} transition hover:opacity-100" data-gallery-thumb-index="${i}">
                    <img src="${s.src}" alt="" class="h-16 w-24 object-cover">
                </button>`).join('');
            thumbs.querySelectorAll('[data-gallery-thumb-index]').forEach(b =>
                b.addEventListener('click', () => openSlide(Number(b.dataset.galleryThumbIndex))));
        }

        function openSlide(index) {
            if (!lightbox || !slides.length) return;
            current = (index + slides.length) % slides.length;
            const s = slides[current];
            image.src = s.src;
            image.alt = s.title || '';
            if (caption) caption.textContent = s.caption || '';
            if (counter) counter.textContent = `${current + 1} / ${slides.length}`;
            renderThumbs();
            lightbox.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            videos.forEach(v => v.pause());
        }

        function closeLightbox() {
            if (!lightbox) return;
            lightbox.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        document.querySelectorAll('[data-gallery-open-slide]').forEach(b =>
            b.addEventListener('click', () => openSlide(Number(b.dataset.galleryOpenSlide || 0))));
        document.querySelectorAll('[data-gallery-close]').forEach(b =>
            b.addEventListener('click', closeLightbox));
        if (prevBtn) prevBtn.addEventListener('click', () => openSlide(current - 1));
        if (nextBtn) nextBtn.addEventListener('click', () => openSlide(current + 1));

        document.addEventListener('keydown', function (e) {
            if (!lightbox || lightbox.classList.contains('hidden')) return;
            if (e.key === 'ArrowLeft')  openSlide(current - 1);
            if (e.key === 'ArrowRight') openSlide(current + 1);
            if (e.key === 'Escape')     closeLightbox();
        });
    });
    </script>
@endpush
