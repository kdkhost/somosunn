@extends('layouts.app')

@section('title', 'Galeria: ' . $event->title . ' - SOMOS UNN')

@php
    $featuredPhotoUrl = $featuredPhoto && $featuredPhoto->hasAccessibleFile()
        ? \App\Support\UploadStorage::url($featuredPhoto->file_path)
        : null;
    $coverUrl    = $event->gallery_cover_url ?: ($featuredPhotoUrl ?: ($event->image_url ?: asset('img/logo.svg')));
    $eventDate   = $event->start_at ? \Carbon\Carbon::parse($event->start_at)->translatedFormat('d \d\e F \d\e Y') : null;
    $photoSlides = $photos->map(fn($item) => [
        'src'     => $item->hasAccessibleFile() ? \App\Support\UploadStorage::url($item->file_path) : $coverUrl,
        'title'   => $event->title,
        'caption' => $item->created_at ? $item->created_at->format('d/m/Y H:i') : '',
    ])->values();
@endphp

@section('meta_image', $coverUrl)
@section('meta_description', 'Galeria do evento ' . $event->title . ' - fotos e videos da cobertura oficial.')

@section('content')
    {{-- Hero --}}
    <div class="relative overflow-hidden pt-28 pb-10">
        <div class="absolute inset-0">
            <img src="{{ $coverUrl }}" alt="{{ $event->title }}" class="h-full w-full object-cover opacity-30">
            <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(248,251,255,0.78),rgba(255,255,255,0.96))]"></div>
        </div>

        <div class="container relative mx-auto px-4">
            <a href="{{ route('gallery.index') }}"
                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/95 px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700">
                <i class="fas fa-arrow-left text-xs"></i> Voltar para a galeria
            </a>

            <div class="mt-5 max-w-5xl rounded-[2rem] border border-slate-200/85 bg-white/95 p-6 shadow-[0_18px_55px_rgba(15,23,42,0.1)] md:p-8">
                <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3.5 py-1.5 text-xs font-black uppercase tracking-[0.18em] text-blue-700">
                    <i class="fas fa-{{ $event->isAlbum() ? 'users' : 'photo-film' }}"></i> {{ $event->isAlbum() ? 'Comunidade UNN' : 'Cobertura oficial' }}
                </span>

                <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-950 md:text-5xl">{{ $event->title }}</h1>

                <div class="mt-5 flex flex-wrap gap-2.5 text-sm text-slate-600">
                    @if(!$event->isAlbum() && $eventDate)
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 font-semibold">
                            <i class="far fa-calendar text-blue-600"></i> {{ $eventDate }}
                        </span>
                    @endif
                    @if(!$event->isAlbum() && $event->location)
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 font-semibold">
                            <i class="fas fa-location-dot text-blue-600"></i> {{ $event->location }}
                        </span>
                    @endif
                    @if($photoCount > 0)
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 font-semibold">
                            <i class="fas fa-images text-blue-600"></i> {{ $photoCount }} {{ $photoCount === 1 ? 'foto' : 'fotos' }}
                        </span>
                    @endif
                    @if($videoCount > 0)
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 font-semibold">
                            <i class="fas fa-circle-play text-blue-600"></i> {{ $videoCount }} {{ $videoCount === 1 ? 'video' : 'videos' }}
                        </span>
                    @endif
                </div>

                @if($photoCount > 0 || $videoCount > 0)
                    <div class="mt-6 flex flex-wrap gap-3">
                        @if($photoCount > 0)
                            <a href="#galeria-fotos" class="inline-flex items-center gap-2 rounded-full bg-[linear-gradient(135deg,#1F5EDB,#177FD6)] px-6 py-2.5 text-sm font-black uppercase tracking-[0.14em] text-white transition hover:brightness-105">
                                <i class="fas fa-images"></i> Ver fotos
                            </a>
                        @endif
                        @if($videoCount > 0)
                            <a href="#galeria-videos" class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-6 py-2.5 text-sm font-black uppercase tracking-[0.14em] text-blue-700 transition hover:bg-blue-100">
                                <i class="fas fa-circle-play"></i> Ver videos
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
                            @php
                                $photoAvailable = $item->hasAccessibleFile();
                                $photoUrl = $photoAvailable ? \App\Support\UploadStorage::url($item->file_path) : $coverUrl;
                            @endphp
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
                                @unless($photoAvailable)
                                    <div class="absolute left-4 top-4">
                                        <span class="inline-flex items-center gap-2 rounded-full border border-yellow-200/40 bg-yellow-400/90 px-3 py-1 text-[11px] font-black uppercase tracking-[0.14em] text-slate-950 shadow-sm">
                                            <i class="fas fa-triangle-exclamation"></i>
                                            Arquivo indisponível
                                        </span>
                                    </div>
                                @endunless
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
                            @php
                                $videoAvailable = $item->hasAccessibleFile();
                                $videoUrl = $videoAvailable ? \App\Support\UploadStorage::url($item->file_path) : null;
                            @endphp
                            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-950 shadow-[0_20px_50px_rgba(15,23,42,0.16)]">
                                @if($videoAvailable)
                                    <video controls preload="metadata" playsinline data-gallery-video
                                        poster="{{ $coverUrl }}"
                                        class="h-72 w-full bg-slate-950 object-cover">
                                        <source src="{{ $videoUrl }}">
                                        Seu navegador nao suporta reproducao de video.
                                    </video>
                                @else
                                    <div class="flex h-72 w-full items-center justify-center bg-slate-900 px-6 text-center text-sm font-bold text-slate-300">
                                        <span class="inline-flex items-center gap-2 rounded-full border border-yellow-300/20 bg-yellow-300/10 px-4 py-2 text-yellow-100">
                                            <i class="fas fa-triangle-exclamation"></i>
                                            Vídeo indisponível no storage
                                        </span>
                                    </div>
                                @endif
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
        <div id="event-gallery-lightbox" class="fixed inset-0 z-[120] flex hidden touch-none items-center justify-center bg-black opacity-0 transition-opacity duration-300">
            
            {{-- Image Track --}}
            <div id="event-gallery-track" class="absolute inset-0 flex items-center justify-center transition-transform duration-300 ease-out">
                <img id="event-gallery-lightbox-image" src="" alt=""
                    class="h-full w-full object-contain transition-transform duration-300"
                    style="max-width: 100vw; max-height: 100dvh;">
            </div>

            {{-- Loader --}}
            <div id="event-gallery-loader" class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center opacity-0 transition-opacity duration-300">
                <i class="fas fa-circle-notch animate-spin text-4xl text-white/50"></i>
            </div>

            {{-- Top UI --}}
            <div id="event-gallery-ui-top" class="absolute left-0 right-0 top-0 z-50 flex items-center justify-between bg-gradient-to-b from-black/80 to-transparent px-4 py-4 sm:px-6 transition-opacity duration-300">
                <p id="event-gallery-counter" class="text-sm font-bold tracking-[0.15em] text-white/90"></p>
                <button type="button" data-gallery-close
                    class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/10 text-white backdrop-blur transition hover:bg-white/25">
                    <i class="fas fa-xmark text-xl"></i>
                </button>
            </div>

            {{-- Bottom UI --}}
            <div id="event-gallery-ui-bottom" class="absolute bottom-0 left-0 right-0 z-50 bg-gradient-to-t from-black/90 via-black/60 to-transparent px-4 pb-8 pt-12 sm:px-6 transition-opacity duration-300">
                <div class="mx-auto max-w-6xl">
                    <p id="event-gallery-caption" class="mb-4 text-center text-sm font-medium text-white/90 sm:text-left"></p>
                    <div id="event-gallery-thumbs" class="flex max-w-full gap-2 overflow-x-auto pb-2" style="scrollbar-width: none;"></div>
                </div>
            </div>

            {{-- Desktop Arrows --}}
            <button type="button" id="event-gallery-prev"
                class="absolute left-6 top-1/2 z-40 hidden -translate-y-1/2 items-center justify-center rounded-full bg-white/10 h-14 w-14 text-white backdrop-blur transition hover:bg-white/20 sm:inline-flex">
                <i class="fas fa-chevron-left text-xl"></i>
            </button>
            <button type="button" id="event-gallery-next"
                class="absolute right-6 top-1/2 z-40 hidden -translate-y-1/2 items-center justify-center rounded-full bg-white/10 h-14 w-14 text-white backdrop-blur transition hover:bg-white/20 sm:inline-flex">
                <i class="fas fa-chevron-right text-xl"></i>
            </button>
        </div>
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
        const uiTop    = document.getElementById('event-gallery-ui-top');
        const uiBottom = document.getElementById('event-gallery-ui-bottom');
        const loader   = document.getElementById('event-gallery-loader');
        const videos   = Array.from(document.querySelectorAll('[data-gallery-video]'));
        let current    = 0;
        let uiVisible  = true;
        let isTransitioning = false;

        // Swipe variables
        let touchStartX = 0;
        let touchStartY = 0;
        let touchEndX = 0;
        let touchEndY = 0;

        videos.forEach(v => v.addEventListener('play', () => videos.forEach(o => o !== v && o.pause())));

        function renderThumbs() {
            if (!thumbs || !slides.length) return;
            thumbs.innerHTML = slides.map((s, i) => `
                <button type="button" class="shrink-0 overflow-hidden rounded-lg border-2 ${i === current ? 'border-white' : 'border-transparent opacity-50'} transition-all duration-300 hover:opacity-100" data-gallery-thumb-index="${i}">
                    <img src="${s.src}" alt="" class="h-14 w-20 object-cover">
                </button>`).join('');
            thumbs.querySelectorAll('[data-gallery-thumb-index]').forEach(b =>
                b.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openSlide(Number(b.dataset.galleryThumbIndex));
                }));
            
            const activeThumb = thumbs.querySelector(`[data-gallery-thumb-index="${current}"]`);
            if (activeThumb) {
                activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        }

        function toggleUI() {
            uiVisible = !uiVisible;
            const opacity = uiVisible ? '1' : '0';
            const pointer = uiVisible ? 'auto' : 'none';
            if (uiTop) { uiTop.style.opacity = opacity; uiTop.style.pointerEvents = pointer; }
            if (uiBottom) { uiBottom.style.opacity = opacity; uiBottom.style.pointerEvents = pointer; }
            if (prevBtn) { prevBtn.style.opacity = opacity; prevBtn.style.pointerEvents = pointer; }
            if (nextBtn) { nextBtn.style.opacity = opacity; nextBtn.style.pointerEvents = pointer; }
        }

        function openSlide(index) {
            if (!lightbox || !slides.length || isTransitioning) return;
            current = (index + slides.length) % slides.length;
            const s = slides[current];
            
            isTransitioning = true;
            image.style.opacity = '0';
            image.style.transform = 'scale(0.96)';
            if (loader) loader.style.opacity = '1';

            const newImg = new Image();
            newImg.src = s.src;
            newImg.onload = () => {
                image.src = s.src;
                image.alt = s.title || '';
                if (caption) caption.textContent = s.caption || '';
                if (counter) counter.textContent = `${current + 1} / ${slides.length}`;
                renderThumbs();
                
                requestAnimationFrame(() => {
                    image.style.opacity = '1';
                    image.style.transform = 'scale(1)';
                    if (loader) loader.style.opacity = '0';
                    setTimeout(() => isTransitioning = false, 300);
                });
            };

            lightbox.classList.remove('hidden');
            requestAnimationFrame(() => lightbox.classList.remove('opacity-0'));
            document.body.classList.add('overflow-hidden');
            videos.forEach(v => v.pause());
        }

        function closeLightbox() {
            if (!lightbox) return;
            lightbox.classList.add('opacity-0');
            setTimeout(() => {
                lightbox.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }, 300);
        }

        function handleGesture() {
            const diffX = touchEndX - touchStartX;
            const diffY = touchEndY - touchStartY;
            
            if (Math.abs(diffX) > Math.abs(diffY)) {
                if (Math.abs(diffX) > 60) {
                    if (diffX > 0) openSlide(current - 1);
                    else openSlide(current + 1);
                } else {
                    toggleUI(); // Small movement = tap
                }
            } else {
                if (diffY > 100) closeLightbox();
                else if (Math.abs(diffY) < 10) toggleUI(); // Small movement = tap
            }
        }

        if (lightbox) {
            lightbox.addEventListener('touchstart', e => {
                if (e.target.closest('#event-gallery-ui-bottom') || e.target.closest('#event-gallery-ui-top') || e.target.closest('button')) return;
                touchStartX = e.changedTouches[0].screenX;
                touchStartY = e.changedTouches[0].screenY;
            }, {passive: true});

            lightbox.addEventListener('touchend', e => {
                if (e.target.closest('#event-gallery-ui-bottom') || e.target.closest('#event-gallery-ui-top') || e.target.closest('button')) return;
                touchEndX = e.changedTouches[0].screenX;
                touchEndY = e.changedTouches[0].screenY;
                handleGesture();
            });
            
            lightbox.addEventListener('click', (e) => {
                if(e.target === image) toggleUI();
            });
        }

        document.querySelectorAll('[data-gallery-open-slide]').forEach(b =>
            b.addEventListener('click', (e) => { e.preventDefault(); openSlide(Number(b.dataset.galleryOpenSlide || 0)); }));
        document.querySelectorAll('[data-gallery-close]').forEach(b =>
            b.addEventListener('click', closeLightbox));
        if (prevBtn) prevBtn.addEventListener('click', (e) => { e.stopPropagation(); openSlide(current - 1); });
        if (nextBtn) nextBtn.addEventListener('click', (e) => { e.stopPropagation(); openSlide(current + 1); });

        document.addEventListener('keydown', function (e) {
            if (!lightbox || lightbox.classList.contains('hidden')) return;
            if (e.key === 'ArrowLeft')  openSlide(current - 1);
            if (e.key === 'ArrowRight') openSlide(current + 1);
            if (e.key === 'Escape')     closeLightbox();
        });
    });
    </script>
@endpush
