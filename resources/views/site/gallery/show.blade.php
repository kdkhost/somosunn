@extends('layouts.app')

@section('title', 'Galeria: ' . $event->title . ' - SOMOS UNN')

@php
    $coverUrl = $featuredPhoto
        ? \App\Support\UploadStorage::url($featuredPhoto->file_path)
        : ($event->image_url ?: asset('img/logo.svg'));
    $speakerName = $event->speaker ?: optional($event->user)->name ?: 'SOMOS UNN';
    $eventDate = $event->start_at
        ? \Carbon\Carbon::parse($event->start_at)->translatedFormat('d \d\e F \d\e Y')
        : 'Data a confirmar';
    $photoSlides = $photos->map(function ($item) use ($event) {
        return [
            'src' => \App\Support\UploadStorage::url($item->file_path),
            'title' => $event->title,
            'caption' => $item->created_at ? 'Publicado em ' . $item->created_at->format('d/m/Y H:i') : 'Galeria do evento',
        ];
    })->values();
@endphp

@section('meta_image', $coverUrl)
@section('meta_description', 'Galeria oficial do evento ' . $event->title . ' com fotos, videos e destaques da cobertura.')

@section('content')
    <div class="relative overflow-hidden bg-slate-950 pt-28 text-white">
        <div class="absolute inset-0">
            <img src="{{ $coverUrl }}" alt="{{ $event->title }}" class="h-full w-full object-cover opacity-20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.35),_transparent_24%),radial-gradient(circle_at_80%_20%,_rgba(14,165,233,0.18),_transparent_26%),linear-gradient(135deg,_rgba(2,6,23,0.94),_rgba(15,23,42,0.92))]"></div>
            <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.1),rgba(15,23,42,0.92))]"></div>
        </div>

        <div class="container relative mx-auto px-4 pb-20">
            <a href="{{ route('gallery.index') }}"
                class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white/80 backdrop-blur transition hover:border-white/20 hover:bg-white/10 hover:text-white">
                <i class="fas fa-arrow-left text-xs"></i>
                Voltar para a galeria
            </a>

            <div class="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1.2fr)_24rem] xl:items-end">
                <div>
                    <div class="inline-flex items-center gap-3 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-4 py-2 text-xs font-black uppercase tracking-[0.24em] text-cyan-100">
                        <i class="fas fa-photo-film"></i>
                        Cobertura oficial do evento
                    </div>

                    <h1 class="mt-6 max-w-5xl text-4xl font-black tracking-tight text-white md:text-6xl">
                        {{ $event->title }}
                    </h1>

                    <div class="mt-6 flex flex-wrap items-center gap-3 text-sm text-slate-200">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 backdrop-blur">
                            <i class="far fa-calendar"></i>
                            {{ $eventDate }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 backdrop-blur">
                            <i class="fas fa-user-tie"></i>
                            {{ $speakerName }}
                        </span>
                        @if($event->location)
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 backdrop-blur">
                                <i class="fas fa-location-dot"></i>
                                {{ $event->location }}
                            </span>
                        @endif
                    </div>

                    <p class="mt-6 max-w-3xl text-base leading-8 text-slate-300 md:text-lg">
                        Uma experiencia visual pensada para acompanhar o tema da plataforma: fotos em destaque, videos organizados com clareza e navegacao fluida para revisitar cada momento sem ruído.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @if($photoCount > 0)
                            <a href="#galeria-fotos"
                                class="inline-flex items-center gap-3 rounded-full bg-white px-6 py-3 text-sm font-black uppercase tracking-[0.16em] text-slate-950 transition hover:-translate-y-0.5 hover:bg-slate-100">
                                <i class="fas fa-images text-blue-600"></i>
                                Ver fotos
                            </a>
                        @endif
                        @if($videoCount > 0)
                            <a href="#galeria-videos"
                                class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-6 py-3 text-sm font-black uppercase tracking-[0.16em] text-white backdrop-blur transition hover:border-white/20 hover:bg-white/10">
                                <i class="fas fa-circle-play text-cyan-300"></i>
                                Ver videos
                            </a>
                        @endif
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3 xl:grid-cols-1">
                    <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5 backdrop-blur">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-400">Midias</p>
                        <p class="mt-4 text-4xl font-black text-white">{{ $totalMedia }}</p>
                        <p class="mt-2 text-sm text-slate-300">Itens publicados na cobertura desse evento.</p>
                    </div>
                    <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5 backdrop-blur">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-400">Fotos</p>
                        <p class="mt-4 text-4xl font-black text-cyan-300">{{ $photoCount }}</p>
                        <p class="mt-2 text-sm text-slate-300">Abrindo em lightbox com navegacao manual por slides.</p>
                    </div>
                    <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5 backdrop-blur">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-400">Videos</p>
                        <p class="mt-4 text-4xl font-black text-fuchsia-300">{{ $videoCount }}</p>
                        <p class="mt-2 text-sm text-slate-300">Reproducao inteligente: um video toca por vez.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="relative bg-[linear-gradient(180deg,#f8fbff_0%,#eef4ff_38%,#ffffff_100%)] pb-24 dark:bg-slate-950">
        <div class="container relative mx-auto -mt-10 space-y-10 px-4">
            @if($photoCount > 0)
                <section id="galeria-fotos" class="overflow-hidden rounded-[2.5rem] border border-slate-200/80 bg-white/95 p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] backdrop-blur md:p-8">
                    <div class="grid gap-8 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] xl:items-start">
                        <div class="overflow-hidden rounded-[2rem] border border-slate-200/70 bg-slate-950 shadow-[0_30px_80px_rgba(15,23,42,0.18)]">
                            @if($featuredPhoto)
                                <button type="button"
                                    data-gallery-open-slide="0"
                                    class="group relative block h-full w-full overflow-hidden text-left">
                                    <img src="{{ \App\Support\UploadStorage::url($featuredPhoto->file_path) }}"
                                        alt="{{ $event->title }}"
                                        class="h-full min-h-[24rem] w-full object-cover transition duration-700 group-hover:scale-[1.03]">
                                    <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.04),rgba(15,23,42,0.72))]"></div>
                                    <div class="absolute inset-x-6 bottom-6 flex items-end justify-between gap-6">
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-[0.24em] text-cyan-200">Destaque da cobertura</p>
                                            <h2 class="mt-3 max-w-2xl text-2xl font-black text-white md:text-4xl">
                                                Abertura em tela cheia com slides manuais
                                            </h2>
                                            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-200">
                                                Clique para navegar pelas fotos desta pagina usando setas, teclado e miniaturas.
                                            </p>
                                        </div>
                                        <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-[1.4rem] border border-white/10 bg-white/10 text-white backdrop-blur transition group-hover:bg-white/20">
                                            <i class="fas fa-up-right-and-down-left-from-center"></i>
                                        </span>
                                    </div>
                                </button>
                            @endif
                        </div>

                        <div class="space-y-5">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600">Fotografia</p>
                                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">
                                    Fotos separadas para leitura rapida e visual premium
                                </h2>
                                <p class="mt-3 text-base leading-8 text-slate-500">
                                    A pagina foi reorganizada para valorizar a cobertura do evento, com fotos em cards de alto contraste, textos curtos e navegacao mais natural para quem quer explorar bastante conteudo.
                                </p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-[1.8rem] border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Pagina atual</p>
                                    <p class="mt-3 text-3xl font-black text-slate-950">{{ $photos->currentPage() }}</p>
                                    <p class="mt-2 text-sm text-slate-500">Navegacao pagina a pagina para manter a experiencia leve.</p>
                                </div>
                                <div class="rounded-[1.8rem] border border-slate-200 bg-slate-50 p-5">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Itens na tela</p>
                                    <p class="mt-3 text-3xl font-black text-slate-950">{{ $photos->count() }}</p>
                                    <p class="mt-2 text-sm text-slate-500">Cada clique abre o slideshow manual desta pagina.</p>
                                </div>
                            </div>

                            <div class="rounded-[1.8rem] border border-blue-200 bg-blue-50 p-5">
                                <div class="flex items-start gap-3">
                                    <span class="mt-1 inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-white">
                                        <i class="fas fa-wand-magic-sparkles"></i>
                                    </span>
                                    <div>
                                        <p class="text-sm font-black uppercase tracking-[0.18em] text-blue-700">Nova experiencia</p>
                                        <p class="mt-2 text-sm leading-7 text-blue-900">
                                            Fotos e videos agora ficam visualmente separados, o que evita confusao, acelera o carregamento percebido e deixa a navegacao mais elegante.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($photos as $item)
                            @php
                                $photoUrl = \App\Support\UploadStorage::url($item->file_path);
                            @endphp
                            <button type="button"
                                data-gallery-open-slide="{{ $loop->index }}"
                                class="group relative overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-950 text-left shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-[0_28px_60px_rgba(15,23,42,0.18)]">
                                <img src="{{ $photoUrl }}" alt="{{ $event->title }}"
                                    class="h-72 w-full object-cover transition duration-700 group-hover:scale-[1.04]">
                                <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.06),rgba(15,23,42,0.7))]"></div>
                                <div class="absolute left-5 right-5 top-5 flex items-start justify-between gap-4">
                                    <span class="rounded-full border border-white/10 bg-slate-950/55 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-white backdrop-blur">
                                        Foto {{ $photos->firstItem() + $loop->index }}
                                    </span>
                                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-[1.2rem] border border-white/10 bg-white/10 text-white backdrop-blur">
                                        <i class="fas fa-expand"></i>
                                    </span>
                                </div>
                                <div class="absolute inset-x-5 bottom-5">
                                    <p class="text-sm font-black uppercase tracking-[0.18em] text-cyan-200">
                                        {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : 'Galeria oficial' }}
                                    </p>
                                    <p class="mt-2 text-lg font-black text-white">Abrir no slideshow manual</p>
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

            @if($videoCount > 0)
                <section id="galeria-videos" class="overflow-hidden rounded-[2.5rem] border border-slate-200/80 bg-white/95 p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] backdrop-blur md:p-8">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-3xl">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-fuchsia-600">Videos</p>
                            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">
                                Videos em trilha propria, com reproducao controlada e sem conflito
                            </h2>
                            <p class="mt-3 text-base leading-8 text-slate-500">
                                A galeria separa os videos em uma secao dedicada e garante que apenas um player toque por vez. Assim a pagina fica limpa e a experiencia nao se mistura com o slideshow das fotos.
                            </p>
                        </div>

                        <div class="inline-flex items-center gap-3 rounded-full border border-fuchsia-200 bg-fuchsia-50 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-fuchsia-700">
                            <i class="fas fa-volume-xmark"></i>
                            Um video por vez
                        </div>
                    </div>

                    <div class="mt-8 grid gap-6 lg:grid-cols-2">
                        @foreach($videos as $item)
                            @php
                                $videoUrl = \App\Support\UploadStorage::url($item->file_path);
                                $ownerName = optional($item->user)->name ?: 'Equipe UNN';
                            @endphp
                            <article class="overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-950 shadow-[0_24px_60px_rgba(15,23,42,0.18)]">
                                <div class="relative">
                                    <video controls preload="metadata" playsinline data-gallery-video
                                        poster="{{ $coverUrl }}"
                                        class="h-[20rem] w-full bg-slate-950 object-cover">
                                        <source src="{{ $videoUrl }}">
                                        Seu navegador nao suporta reproducao de video.
                                    </video>
                                    <div class="pointer-events-none absolute left-4 top-4 rounded-full border border-white/10 bg-slate-950/60 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-white backdrop-blur">
                                        Video da cobertura
                                    </div>
                                </div>
                                <div class="grid gap-4 p-5 sm:grid-cols-[minmax(0,1fr)_13rem] sm:items-end">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-fuchsia-300">Publicado na galeria</p>
                                        <h3 class="mt-3 text-xl font-black text-white">{{ $event->title }}</h3>
                                        <p class="mt-3 text-sm leading-7 text-slate-300">
                                            Enviado por {{ $ownerName }}{{ $item->created_at ? ' em ' . $item->created_at->format('d/m/Y H:i') : '' }}.
                                        </p>
                                    </div>
                                    <div class="rounded-[1.6rem] border border-white/10 bg-white/5 p-4 text-sm text-slate-200 backdrop-blur">
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Playback</p>
                                        <p class="mt-3 font-bold text-white">Ao dar play neste card, qualquer outro video em execucao sera pausado automaticamente.</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if($videos->hasPages())
                        <div class="gallery-event-pagination mt-8">
                            {{ $videos->onEachSide(1)->links('pagination::tailwind') }}
                        </div>
                    @endif
                </section>
            @endif

            @if($relatedEvents->isNotEmpty())
                <section class="rounded-[2.5rem] border border-slate-200/80 bg-white/95 p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] backdrop-blur md:p-8">
                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600">Continuar explorando</p>
                            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Outros eventos com cobertura publicada</h2>
                        </div>
                        <a href="{{ route('gallery.index') }}"
                            class="inline-flex items-center gap-3 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition hover:border-slate-300 hover:text-slate-950">
                            <i class="fas fa-grid-2"></i>
                            Ver todos
                        </a>
                    </div>

                    <div class="mt-8 grid gap-5 lg:grid-cols-3">
                        @foreach($relatedEvents as $relatedEvent)
                            @php
                                $relatedCover = optional($relatedEvent->media->first())->file_path
                                    ? \App\Support\UploadStorage::url(optional($relatedEvent->media->first())->file_path)
                                    : ($relatedEvent->image_url ?: asset('img/logo.svg'));
                            @endphp
                            <a href="{{ route('gallery.show', $relatedEvent) }}"
                                class="group overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-950 text-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-[0_28px_60px_rgba(15,23,42,0.18)]">
                                <div class="relative h-56 overflow-hidden">
                                    <img src="{{ $relatedCover }}" alt="{{ $relatedEvent->title }}"
                                        class="h-full w-full object-cover opacity-80 transition duration-700 group-hover:scale-[1.04] group-hover:opacity-100">
                                    <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.08),rgba(15,23,42,0.82))]"></div>
                                    <div class="absolute inset-x-5 bottom-5">
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-cyan-200">
                                            {{ $relatedEvent->start_at ? \Carbon\Carbon::parse($relatedEvent->start_at)->format('d/m/Y') : 'Data a confirmar' }}
                                        </p>
                                        <h3 class="mt-2 text-2xl font-black text-white">{{ \Illuminate\Support\Str::limit($relatedEvent->title, 42) }}</h3>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>

    @if(count($photoSlides) > 0)
        <div id="event-gallery-lightbox" class="fixed inset-0 z-[120] hidden">
            <div data-gallery-close class="absolute inset-0 bg-slate-950/95 backdrop-blur-md"></div>

            <div class="relative flex min-h-full items-center justify-center p-4 md:p-8">
                <button type="button"
                    data-gallery-close
                    class="absolute right-4 top-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white transition hover:bg-white/20 md:right-8 md:top-8">
                    <i class="fas fa-xmark text-lg"></i>
                </button>

                <button type="button"
                    id="event-gallery-prev"
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
                            <h3 id="event-gallery-title" class="mt-2 text-2xl font-black text-white"></h3>
                            <p id="event-gallery-caption" class="mt-2 text-sm leading-7 text-slate-300"></p>
                        </div>

                        <div id="event-gallery-thumbs" class="flex max-w-full gap-2 overflow-x-auto pb-2"></div>
                    </div>
                </div>

                <button type="button"
                    id="event-gallery-next"
                    class="absolute right-3 top-1/2 z-10 inline-flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white transition hover:bg-white/20 md:right-8 md:h-14 md:w-14">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .gallery-event-pagination nav>div:first-child {
            display: none;
        }

        .gallery-event-pagination nav>div:last-child {
            display: flex;
            justify-content: center;
        }

        .gallery-event-pagination span[aria-current="page"] span,
        .gallery-event-pagination a,
        .gallery-event-pagination span[aria-disabled="true"] span {
            border-radius: 1rem;
        }

        #event-gallery-thumbs::-webkit-scrollbar {
            height: 6px;
        }

        #event-gallery-thumbs::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.45);
            border-radius: 999px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const slides = @json($photoSlides);
            const lightbox = document.getElementById('event-gallery-lightbox');
            const image = document.getElementById('event-gallery-lightbox-image');
            const title = document.getElementById('event-gallery-title');
            const caption = document.getElementById('event-gallery-caption');
            const counter = document.getElementById('event-gallery-counter');
            const thumbs = document.getElementById('event-gallery-thumbs');
            const previousButton = document.getElementById('event-gallery-prev');
            const nextButton = document.getElementById('event-gallery-next');
            const pageVideos = Array.from(document.querySelectorAll('[data-gallery-video]'));
            let currentIndex = 0;

            function pauseAllVideos(except) {
                pageVideos.forEach(function (player) {
                    if (player !== except) {
                        player.pause();
                    }
                });
            }

            pageVideos.forEach(function (player) {
                player.addEventListener('play', function () {
                    pauseAllVideos(player);
                });
            });

            function renderThumbs() {
                if (!thumbs || slides.length === 0) {
                    return;
                }

                thumbs.innerHTML = slides.map(function (slide, index) {
                    const activeClass = index === currentIndex
                        ? 'border-white shadow-[0_12px_24px_rgba(15,23,42,0.4)]'
                        : 'border-white/10 opacity-70';

                    return `
                        <button type="button"
                            class="event-gallery-thumb shrink-0 overflow-hidden rounded-2xl border ${activeClass} bg-white/5 transition hover:opacity-100"
                            data-gallery-thumb-index="${index}">
                            <img src="${slide.src}" alt="${slide.title || 'Foto da galeria'}" class="h-16 w-24 object-cover">
                        </button>
                    `;
                }).join('');

                thumbs.querySelectorAll('[data-gallery-thumb-index]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        openSlide(Number(this.dataset.galleryThumbIndex || 0));
                    });
                });
            }

            function openSlide(index) {
                if (!lightbox || !image || slides.length === 0) {
                    return;
                }

                currentIndex = (index + slides.length) % slides.length;
                const slide = slides[currentIndex];

                image.src = slide.src;
                image.alt = slide.title || 'Foto da galeria';
                if (title) {
                    title.textContent = slide.title || '';
                }
                if (caption) {
                    caption.textContent = slide.caption || '';
                }
                if (counter) {
                    counter.textContent = 'Slide ' + (currentIndex + 1) + ' de ' + slides.length;
                }

                renderThumbs();
                lightbox.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                pauseAllVideos(null);
            }

            function closeLightbox() {
                if (!lightbox) {
                    return;
                }

                lightbox.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            document.querySelectorAll('[data-gallery-open-slide]').forEach(function (button) {
                button.addEventListener('click', function () {
                    openSlide(Number(this.dataset.galleryOpenSlide || 0));
                });
            });

            document.querySelectorAll('[data-gallery-close]').forEach(function (button) {
                button.addEventListener('click', closeLightbox);
            });

            if (previousButton) {
                previousButton.addEventListener('click', function () {
                    openSlide(currentIndex - 1);
                });
            }

            if (nextButton) {
                nextButton.addEventListener('click', function () {
                    openSlide(currentIndex + 1);
                });
            }

            document.addEventListener('keydown', function (event) {
                if (lightbox && !lightbox.classList.contains('hidden')) {
                    if (event.key === 'Escape') {
                        closeLightbox();
                    } else if (event.key === 'ArrowLeft') {
                        openSlide(currentIndex - 1);
                    } else if (event.key === 'ArrowRight') {
                        openSlide(currentIndex + 1);
                    }
                }
            });
        });
    </script>
@endpush
