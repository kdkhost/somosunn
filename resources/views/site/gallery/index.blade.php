@extends('layouts.app')

@section('title', 'Galeria de Eventos - SOMOS UNN')

@php
    $totalAlbums = method_exists($events, 'total') ? $events->total() : $events->count();
@endphp

@section('content')
    {{-- Hero compacto --}}
    <div class="pt-4 pb-4">
        <div class="container mx-auto px-4">
            <div class="rounded-[1.6rem] border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-[11px] font-black uppercase tracking-[0.14em] text-blue-700">
                    <i class="fas fa-images"></i>
                    Galeria de eventos
                </span>

                <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 md:text-5xl">
                    Reviva cada momento da comunidade
                </h1>

                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600 md:text-base">
                    Fotos e videos organizados por evento. Clique em um album para explorar a cobertura completa.
                </p>

                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-1.5 text-sm font-semibold text-slate-700">
                        <i class="fas fa-layer-group text-blue-600"></i>
                        {{ $totalAlbums }} {{ $totalAlbums === 1 ? 'album' : 'albuns' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Conteudo --}}
    <div class="relative bg-[linear-gradient(180deg,#f8fbff_0%,#eef4ff_35%,#ffffff_100%)] pb-24">
        <div class="container relative mx-auto px-4">

            @if($events->isEmpty())
                <div class="mx-auto max-w-2xl overflow-hidden rounded-[2.5rem] border border-slate-200/70 bg-white p-10 text-center shadow-[0_30px_80px_rgba(15,23,42,0.08)]">
                    <span class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-3xl text-slate-400">
                        <i class="fas fa-camera-retro"></i>
                    </span>
                    <h2 class="mt-6 text-2xl font-black text-slate-950">Nenhum album publicado ainda</h2>
                    <p class="mt-3 text-slate-500">Em breve as coberturas dos eventos estarao disponiveis aqui.</p>
                    <a href="{{ route('home') }}" class="mt-8 inline-flex items-center gap-3 rounded-full bg-slate-950 px-6 py-3 text-sm font-black uppercase tracking-[0.16em] text-white transition hover:bg-slate-800">
                        <i class="fas fa-arrow-left text-cyan-300"></i> Voltar ao inicio
                    </a>
                </div>
            @else
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($events as $album)
                        @php
                            $albumDate  = $album->start_at ? \Carbon\Carbon::parse($album->start_at)->translatedFormat('d \d\e M \d\e Y') : null;
                            $albumCover = $album->gallery_cover_url ?: asset('img/logo.svg');
                        @endphp
                        <a href="{{ route('gallery.show', $album) }}"
                            class="group block overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_16px_40px_rgba(15,23,42,0.06)] transition duration-300 hover:-translate-y-1.5 hover:shadow-[0_30px_70px_rgba(15,23,42,0.14)]">
                            <div class="relative overflow-hidden">
                                <img src="{{ $albumCover }}" alt="{{ $album->title }}"
                                    class="h-64 w-full object-cover transition duration-700 group-hover:scale-[1.05]">
                                <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.02),rgba(15,23,42,0.72))]"></div>
                                <div class="absolute left-5 top-5">
                                    <span class="rounded-full border border-white/10 bg-slate-950/60 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-white backdrop-blur">
                                        {{ $album->media_count }} {{ $album->media_count === 1 ? 'item' : 'itens' }}
                                    </span>
                                </div>
                                <div class="absolute inset-x-5 bottom-5">
                                    @if($albumDate)
                                        <p class="text-xs font-black uppercase tracking-[0.2em] text-cyan-200">{{ $albumDate }}</p>
                                    @endif
                                    <h3 class="gallery-index-title mt-2 text-xl font-black tracking-tight text-white">{{ $album->title }}</h3>
                                </div>
                            </div>

                            <div class="flex items-center justify-between px-5 py-4">
                                @if($album->location)
                                    <span class="inline-flex items-center gap-2 text-xs font-bold text-slate-400">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ \Illuminate\Support\Str::limit($album->location, 28) }}
                                    </span>
                                @else
                                    <span></span>
                                @endif
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-900 transition group-hover:bg-blue-600 group-hover:text-white">
                                    <i class="fas fa-arrow-right text-sm"></i>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if($events->hasPages())
                    <div class="gallery-index-pagination mt-10">
                        {{ $events->onEachSide(1)->links('pagination::tailwind') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .gallery-index-chip {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            border-radius: 9999px;
            border: 1px solid rgba(255,255,255,.1);
            background: rgba(255,255,255,.05);
            padding: .7rem 1.1rem;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: rgba(255,255,255,.88);
            backdrop-filter: blur(14px);
        }
        .gallery-index-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .gallery-index-pagination nav { display: flex; justify-content: center; }
        .gallery-index-pagination nav > div:first-child { display: none; }
        .gallery-index-pagination nav > div:last-child { width: 100%; }
        .gallery-index-pagination nav > div:last-child > div,
        .gallery-index-pagination nav > div:last-child > span {
            display: flex; align-items: center; justify-content: center; gap: .6rem; flex-wrap: wrap;
        }
        .gallery-index-pagination span[aria-current="page"] span,
        .gallery-index-pagination a,
        .gallery-index-pagination span[aria-disabled="true"] span {
            display: inline-flex; min-width: 2.8rem; align-items: center; justify-content: center;
            border-radius: 9999px; padding: .75rem 1rem; font-weight: 800; transition: all .2s ease;
        }
        .gallery-index-pagination a { border: 1px solid rgba(148,163,184,.24); background: #fff; color: #0f172a; }
        .gallery-index-pagination a:hover { background: #eff6ff; color: #1d4ed8; }
        .gallery-index-pagination span[aria-current="page"] span { background: linear-gradient(135deg,#2563eb,#1d4ed8); color: #fff; }
        .gallery-index-pagination span[aria-disabled="true"] span { border: 1px solid #e2e8f0; background: #f8fafc; color: #94a3b8; }
    </style>
@endpush
