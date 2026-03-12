@extends('layouts.app')

@section('title', 'Galeria de Eventos - SOMOS UNN')

@php
    $featuredEvent = $events->first();
    $featuredCover = $featuredEvent?->gallery_cover_url ?: asset('img/logo.svg');
    $featuredDate = $featuredEvent?->start_at
        ? \Carbon\Carbon::parse($featuredEvent->start_at)->translatedFormat('d \d\e F \d\e Y')
        : 'Data a confirmar';
    $featuredLocation = $featuredEvent?->location ?: 'Cobertura oficial SOMOS UNN';
    $totalAlbums = method_exists($events, 'total') ? $events->total() : $events->count();
    $visibleMediaCount = (int) $events->getCollection()->sum('media_count');
    $gridEvents = $events->getCollection()->slice($featuredEvent ? 1 : 0)->values();
@endphp

@section('content')
    <div class="relative overflow-hidden bg-slate-950 pt-28 text-white">
        <div class="absolute inset-0">
            @if($featuredEvent)
                <img src="{{ $featuredCover }}" alt="{{ $featuredEvent->title }}" class="h-full w-full object-cover opacity-20">
            @endif
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.22),_transparent_24%),radial-gradient(circle_at_78%_18%,_rgba(59,130,246,0.24),_transparent_28%),radial-gradient(circle_at_60%_80%,_rgba(168,85,247,0.16),_transparent_26%),linear-gradient(135deg,_rgba(2,6,23,0.96),_rgba(15,23,42,0.94))]"></div>
            <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.08),rgba(15,23,42,0.92))]"></div>
        </div>

        <div class="container relative mx-auto px-4 pb-24">
            <div class="grid gap-10 xl:grid-cols-[minmax(0,1.08fr)_30rem] xl:items-end">
                <div class="max-w-5xl">
                    <span class="inline-flex items-center gap-3 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-4 py-2 text-xs font-black uppercase tracking-[0.24em] text-cyan-100">
                        <i class="fas fa-images"></i>
                        Arquivo visual da comunidade
                    </span>

                    <h1 class="mt-6 text-4xl font-black tracking-tight text-white md:text-6xl xl:text-7xl">
                        Galeria feita para
                        <span class="bg-[linear-gradient(135deg,#ffffff_0%,#7dd3fc_45%,#60a5fa_100%)] bg-clip-text text-transparent">
                            reviver cada encontro
                        </span>
                    </h1>

                    <p class="mt-6 max-w-3xl text-base leading-8 text-slate-300 md:text-lg">
                        Uma vitrine viva dos eventos da SOMOS UNN, com capas de album, transicoes suaves e uma navegacao mais elegante para descobrir os melhores momentos da comunidade.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <div class="gallery-index-chip">
                            <i class="fas fa-layer-group text-cyan-300"></i>
                            <span>{{ $totalAlbums }} albuns publicados</span>
                        </div>
                        <div class="gallery-index-chip">
                            <i class="fas fa-images text-blue-300"></i>
                            <span>{{ $visibleMediaCount }} midias nesta pagina</span>
                        </div>
                        <div class="gallery-index-chip">
                            <i class="fas fa-magic text-fuchsia-300"></i>
                            <span>Coberturas organizadas por evento</span>
                        </div>
                    </div>

                    @if($featuredEvent)
                        <div class="mt-10 flex flex-wrap gap-3">
                            <a href="{{ route('gallery.show', $featuredEvent) }}"
                                class="inline-flex items-center gap-3 rounded-full bg-white px-6 py-3 text-sm font-black uppercase tracking-[0.18em] text-slate-950 transition hover:-translate-y-0.5 hover:bg-slate-100">
                                <i class="fas fa-arrow-right text-blue-600"></i>
                                Abrir album em destaque
                            </a>
                            <a href="#albuns"
                                class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-6 py-3 text-sm font-black uppercase tracking-[0.18em] text-white backdrop-blur transition hover:border-white/20 hover:bg-white/10">
                                <i class="fas fa-th-large"></i>
                                Explorar todos
                            </a>
                        </div>
                    @endif
                </div>

                <div class="relative">
                    <div class="gallery-index-floating-card">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-400">Pulso da galeria</p>
                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <div class="rounded-[1.6rem] border border-white/10 bg-white/5 p-4 backdrop-blur">
                                <p class="text-3xl font-black text-white">{{ $totalAlbums }}</p>
                                <p class="mt-2 text-sm text-slate-300">Eventos com cobertura visual</p>
                            </div>
                            <div class="rounded-[1.6rem] border border-white/10 bg-white/5 p-4 backdrop-blur">
                                <p class="text-3xl font-black text-cyan-300">{{ $events->count() }}</p>
                                <p class="mt-2 text-sm text-slate-300">Albuns visiveis nesta pagina</p>
                            </div>
                        </div>
                        <div class="mt-4 rounded-[1.8rem] border border-cyan-400/15 bg-cyan-400/10 p-5">
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-cyan-100">Experiencia atualizada</p>
                            <p class="mt-3 text-sm leading-7 text-slate-200">
                                As capas dos eventos agora assumem protagonismo e o acesso aos albuns ficou mais direto, leve e visualmente marcante.
                            </p>
                        </div>
                    </div>

                    @if($featuredEvent)
                        <div class="gallery-index-spotlight-card mt-6 overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 p-4 shadow-[0_30px_90px_rgba(15,23,42,0.36)] backdrop-blur">
                            <div class="relative overflow-hidden rounded-[1.6rem]">
                                <img src="{{ $featuredCover }}" alt="{{ $featuredEvent->title }}" class="h-72 w-full object-cover">
                                <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.08),rgba(15,23,42,0.76))]"></div>
                                <div class="absolute inset-x-5 bottom-5">
                                    <p class="text-xs font-black uppercase tracking-[0.22em] text-cyan-200">Album em destaque</p>
                                    <h2 class="mt-3 text-2xl font-black text-white">{{ $featuredEvent->title }}</h2>
                                    <p class="mt-3 text-sm text-slate-200">{{ $featuredDate }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="relative overflow-hidden bg-[linear-gradient(180deg,#f8fbff_0%,#eef4ff_35%,#ffffff_100%)] pb-24">
        <div class="absolute left-0 top-24 h-80 w-80 rounded-full bg-cyan-200/25 blur-3xl"></div>
        <div class="absolute right-0 top-80 h-96 w-96 rounded-full bg-blue-200/20 blur-3xl"></div>

        <div class="container relative mx-auto -mt-12 px-4">
            @if($events->isEmpty())
                <div class="mx-auto max-w-4xl overflow-hidden rounded-[2.5rem] border border-slate-200/70 bg-white/90 p-6 shadow-[0_30px_80px_rgba(15,23,42,0.08)] backdrop-blur md:p-8">
                    <div class="grid gap-8 md:grid-cols-[14rem_minmax(0,1fr)] md:items-center">
                        <div class="relative overflow-hidden rounded-[2rem] bg-[linear-gradient(135deg,#0f172a,#1e3a8a)] p-8 text-white">
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(125,211,252,0.24),_transparent_30%),radial-gradient(circle_at_85%_80%,_rgba(96,165,250,0.26),_transparent_35%)]"></div>
                            <div class="relative flex h-full flex-col items-center justify-center text-center">
                                <span class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-white/10 text-3xl text-cyan-200">
                                    <i class="fas fa-camera-retro"></i>
                                </span>
                                <p class="mt-5 text-sm font-black uppercase tracking-[0.24em] text-cyan-100">Em construcao</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600">Galeria oficial</p>
                            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">
                                Ainda nao temos albuns publicados
                            </h2>
                            <p class="mt-4 max-w-2xl text-base leading-8 text-slate-500">
                                Assim que novas coberturas forem disponibilizadas, esta pagina passa a destacar os registros de cada evento com capas, conteudo organizado e acesso rapido aos albuns.
                            </p>
                            <div class="mt-8">
                                <a href="{{ route('home') }}"
                                    class="inline-flex items-center gap-3 rounded-full bg-slate-950 px-6 py-3 text-sm font-black uppercase tracking-[0.16em] text-white transition hover:-translate-y-0.5 hover:bg-slate-800">
                                    <i class="fas fa-arrow-left text-cyan-300"></i>
                                    Voltar para o inicio
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                @if($featuredEvent)
                    <section class="overflow-hidden rounded-[2.8rem] border border-slate-200/70 bg-white/92 p-6 shadow-[0_30px_80px_rgba(15,23,42,0.08)] backdrop-blur md:p-8">
                        <div class="grid gap-8 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] xl:items-center">
                            <a href="{{ route('gallery.show', $featuredEvent) }}"
                                class="group relative block overflow-hidden rounded-[2.2rem] border border-slate-200 bg-slate-950 shadow-[0_26px_70px_rgba(15,23,42,0.16)]">
                                <img src="{{ $featuredCover }}" alt="{{ $featuredEvent->title }}"
                                    class="h-[22rem] w-full object-cover transition duration-700 group-hover:scale-[1.04] md:h-[30rem]">
                                <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.08),rgba(15,23,42,0.76))]"></div>
                                <div class="absolute left-6 right-6 top-6 flex items-start justify-between gap-4">
                                    <span class="rounded-full border border-white/10 bg-slate-950/55 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-white backdrop-blur">
                                        Mais recente
                                    </span>
                                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-[1.3rem] border border-white/10 bg-white/10 text-white backdrop-blur">
                                            <i class="fas fa-external-link-alt"></i>
                                    </span>
                                </div>
                                <div class="absolute inset-x-6 bottom-6">
                                    <p class="text-xs font-black uppercase tracking-[0.22em] text-cyan-200">{{ $featuredDate }}</p>
                                    <h2 class="mt-3 text-3xl font-black tracking-tight text-white md:text-4xl">
                                        {{ $featuredEvent->title }}
                                    </h2>
                                    <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-200">
                                        {{ $featuredLocation }}. Album com {{ $featuredEvent->media_count }} {{ \Illuminate\Support\Str::plural('item', $featuredEvent->media_count) }} prontos para navegacao.
                                    </p>
                                </div>
                            </a>

                            <div class="space-y-5">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600">Curadoria visual</p>
                                    <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 md:text-4xl">
                                        Uma entrada mais viva para os albuns da plataforma
                                    </h2>
                                    <p class="mt-4 text-base leading-8 text-slate-500">
                                        Esta pagina agora funciona como uma vitrine: destaque forte para a capa, leitura rapida dos metadados e uma grade de albuns mais elegante para explorar o acervo sem poluicao visual.
                                    </p>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="rounded-[1.8rem] border border-slate-200 bg-slate-50 p-5">
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Album em foco</p>
                                        <p class="mt-3 text-3xl font-black text-slate-950">{{ $featuredEvent->media_count }}</p>
                                        <p class="mt-2 text-sm text-slate-500">Midias reunidas para acesso direto.</p>
                                    </div>
                                    <div class="rounded-[1.8rem] border border-slate-200 bg-slate-50 p-5">
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Cobertura</p>
                                        <p class="mt-3 text-3xl font-black text-slate-950">{{ $featuredDate }}</p>
                                        <p class="mt-2 text-sm text-slate-500">Data destacada para contextualizar o album.</p>
                                    </div>
                                </div>

                                <div class="rounded-[1.8rem] border border-cyan-200 bg-cyan-50 p-5">
                                    <div class="flex items-start gap-3">
                                        <span class="mt-1 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-cyan-500 text-white">
                                            <i class="fas fa-star"></i>
                                        </span>
                                        <div>
                                            <p class="text-sm font-black uppercase tracking-[0.18em] text-cyan-700">Mais atracao visual</p>
                                            <p class="mt-2 text-sm leading-7 text-cyan-900">
                                                O album principal fica em primeiro plano e os demais entram na grade logo abaixo, seguindo a mesma linguagem premium usada na galeria interna.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <a href="{{ route('gallery.show', $featuredEvent) }}"
                                        class="inline-flex items-center gap-3 rounded-full bg-slate-950 px-6 py-3 text-sm font-black uppercase tracking-[0.16em] text-white transition hover:-translate-y-0.5 hover:bg-slate-800">
                                        <i class="fas fa-play text-cyan-300"></i>
                                        Ver album em destaque
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

                <section id="albuns" class="mt-10 overflow-hidden rounded-[2.8rem] border border-slate-200/70 bg-white/92 p-6 shadow-[0_30px_80px_rgba(15,23,42,0.08)] backdrop-blur md:p-8">
                    <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 md:flex-row md:items-end md:justify-between">
                        <div class="max-w-3xl">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600">Albuns</p>
                            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 md:text-4xl">
                                Explore a cobertura dos eventos da comunidade
                            </h2>
                            <p class="mt-3 text-base leading-8 text-slate-500">
                                Cada card usa a capa do evento para criar uma entrada mais forte no album, com metadados limpos e hover mais leve para desktop e mobile.
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 sm:w-auto">
                            <div class="rounded-[1.6rem] border border-slate-200 bg-slate-50 px-5 py-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Pagina</p>
                                <p class="mt-2 text-2xl font-black text-slate-950">{{ $events->currentPage() }}</p>
                            </div>
                            <div class="rounded-[1.6rem] border border-slate-200 bg-slate-50 px-5 py-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Visiveis</p>
                                <p class="mt-2 text-2xl font-black text-slate-950">{{ $events->count() }}</p>
                            </div>
                        </div>
                    </div>

                    @php
                        $albumsToRender = $gridEvents->isNotEmpty() ? $gridEvents : ($featuredEvent ? collect([$featuredEvent]) : collect());
                    @endphp

                    <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($albumsToRender as $event)
                            @php
                                $eventDate = $event->start_at
                                    ? \Carbon\Carbon::parse($event->start_at)->translatedFormat('d \d\e M \d\e Y')
                                    : 'Data a confirmar';
                                $eventCover = $event->gallery_cover_url ?: asset('img/logo.svg');
                            @endphp
                            <a href="{{ route('gallery.show', $event) }}"
                                class="gallery-index-card group block overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_16px_40px_rgba(15,23,42,0.06)] transition duration-300 hover:-translate-y-1.5 hover:shadow-[0_30px_70px_rgba(15,23,42,0.14)]">
                                <div class="relative overflow-hidden">
                                    <img src="{{ $eventCover }}" alt="{{ $event->title }}"
                                        class="h-72 w-full object-cover transition duration-700 group-hover:scale-[1.05]">
                                    <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.02),rgba(15,23,42,0.74))]"></div>
                                    <div class="absolute left-5 right-5 top-5 flex items-start justify-between gap-4">
                                        <span class="rounded-full border border-white/10 bg-slate-950/55 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-white backdrop-blur">
                                            {{ $event->media_count }} {{ \Illuminate\Support\Str::plural('item', $event->media_count) }}
                                        </span>
                                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-[1.2rem] border border-white/10 bg-white/10 text-white backdrop-blur transition group-hover:bg-white/20">
                                            <i class="fas fa-arrow-right"></i>
                                        </span>
                                    </div>
                                    <div class="absolute inset-x-5 bottom-5">
                                        <p class="text-xs font-black uppercase tracking-[0.2em] text-cyan-200">{{ $eventDate }}</p>
                                        <h3 class="mt-3 gallery-index-title text-2xl font-black tracking-tight text-white">
                                            {{ $event->title }}
                                        </h3>
                                    </div>
                                </div>

                                <div class="space-y-4 p-6">
                                    <div class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-slate-400">
                                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-slate-600">
                                            <i class="far fa-calendar"></i>
                                            {{ $eventDate }}
                                        </span>
                                        @if($event->location)
                                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-slate-600">
                                                <i class="fas fa-map-marker-alt"></i>
                                                {{ \Illuminate\Support\Str::limit($event->location, 24) }}
                                            </span>
                                        @endif
                                    </div>

                                    <p class="gallery-index-summary text-sm leading-7 text-slate-500">
                                        Entre no album para navegar pela cobertura completa, com capas personalizadas e organizacao pensada para explorar as midias com mais contexto.
                                    </p>

                                    <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                                        <span class="text-sm font-black uppercase tracking-[0.18em] text-blue-600">Abrir album</span>
                                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-900 transition group-hover:bg-blue-600 group-hover:text-white">
                                            <i class="fas fa-arrow-right"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    @if($events->hasPages())
                        <div class="gallery-index-pagination mt-10">
                            {{ $events->onEachSide(1)->links('pagination::tailwind') }}
                        </div>
                    @endif
                </section>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .gallery-index-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            padding: 0.9rem 1.2rem;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
        }

        .gallery-index-floating-card {
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 2rem;
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            backdrop-filter: blur(20px);
            box-shadow: 0 30px 90px rgba(15, 23, 42, 0.34);
        }

        .gallery-index-spotlight-card {
            transform: translateY(0);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .gallery-index-spotlight-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 40px 90px rgba(15, 23, 42, 0.42);
        }

        .gallery-index-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .gallery-index-summary {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .gallery-index-pagination nav {
            display: flex;
            justify-content: center;
        }

        .gallery-index-pagination nav > div:first-child {
            display: none;
        }

        .gallery-index-pagination nav > div:last-child {
            width: 100%;
        }

        .gallery-index-pagination nav > div:last-child > div,
        .gallery-index-pagination nav > div:last-child > span {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .gallery-index-pagination span[aria-current="page"] span,
        .gallery-index-pagination a,
        .gallery-index-pagination span[aria-disabled="true"] span {
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

        .gallery-index-pagination a {
            border: 1px solid rgba(148, 163, 184, 0.24);
            background: rgba(255, 255, 255, 0.95);
            color: #0f172a;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        }

        .gallery-index-pagination a:hover {
            transform: translateY(-2px);
            border-color: rgba(37, 99, 235, 0.24);
            background: #eff6ff;
            color: #1d4ed8;
        }

        .gallery-index-pagination span[aria-current="page"] span {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            box-shadow: 0 18px 30px rgba(37, 99, 235, 0.24);
        }

        .gallery-index-pagination span[aria-disabled="true"] span {
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(248, 250, 252, 0.88);
            color: #94a3b8;
        }
    </style>
@endpush
