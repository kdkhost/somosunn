@extends('layouts.app')

@section('title', 'Banca de Revistas - SOMOS UNN')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<style>
    .mag-hero {
        position: relative;
        padding: 4rem 1rem 3rem;
        text-align: center;
        overflow: hidden;
    }
    .mag-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse at 20% 30%, rgba(31, 94, 219, 0.08), transparent 60%),
            radial-gradient(ellipse at 80% 70%, rgba(23, 127, 214, 0.06), transparent 60%);
        pointer-events: none;
    }
    .mag-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .4rem 1rem;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(31,94,219,0.1), rgba(23,127,214,0.1));
        border: 1px solid rgba(31,94,219,0.2);
        color: #1F5EDB;
        font-size: .7rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 1rem;
    }
    .mag-hero h1 {
        font-size: clamp(2rem, 5vw, 3.5rem);
        font-weight: 900;
        color: #0f172a !important;
        letter-spacing: -0.02em;
        line-height: 1.1;
        margin-bottom: 0.75rem;
        -webkit-text-fill-color: #0f172a !important;
    }
    .mag-hero h1 .accent {
        color: #1F5EDB !important;
        -webkit-text-fill-color: #1F5EDB !important;
    }
    .mag-hero p {
        font-size: 1rem;
        color: #64748b;
        max-width: 600px;
        margin: 0 auto;
    }
    .dark .mag-hero h1 { color: #f1f5f9; }
    .dark .mag-hero h1 .accent { color: #60a5fa; }
    .dark .mag-hero p { color: #94a3b8; }

    /* Filtros */
    .mag-filters {
        max-width: 900px;
        margin: 0 auto 2.5rem;
        padding: 0 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        align-items: center;
        justify-content: center;
    }
    .mag-filter-input {
        flex: 1;
        min-width: 220px;
        max-width: 400px;
        position: relative;
    }
    .mag-filter-input input {
        width: 100%;
        padding: .85rem 1rem .85rem 2.75rem;
        border-radius: 999px;
        border: 1.5px solid #e2e8f0;
        background: #ffffff !important;
        font-size: .9rem;
        color: #1e293b !important;
        transition: all .2s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .mag-filter-input input::placeholder {
        color: #94a3b8 !important;
    }
    .mag-filter-input input:focus {
        outline: none;
        border-color: #1F5EDB;
        box-shadow: 0 0 0 4px rgba(31,94,219,0.1);
    }
    .mag-filter-input i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }
    .mag-category-select {
        padding: .85rem 1.25rem;
        border-radius: 999px;
        border: 1.5px solid #e2e8f0;
        background: #ffffff !important;
        font-size: .9rem;
        font-weight: 600;
        color: #1e293b !important;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        appearance: none;
        -webkit-appearance: none;
        padding-right: 2.5rem;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 1rem center !important;
    }
    .mag-category-select:focus {
        outline: none;
        border-color: #1F5EDB;
        box-shadow: 0 0 0 4px rgba(31,94,219,0.1);
    }

    /* Grid desktop */
    .mag-grid {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 1rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1.5rem;
    }

    /* Card */
    .mag-card {
        position: relative;
        border-radius: 1rem;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        transition: transform .3s ease, box-shadow .3s ease;
        display: flex;
        flex-direction: column;
        text-decoration: none;
        color: inherit;
        border: 1px solid rgba(0,0,0,0.04);
    }
    .mag-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 40px rgba(31,94,219,0.18);
    }
    .dark .mag-card {
        background: #1e293b;
        border-color: rgba(255,255,255,0.06);
    }

    .mag-card-cover {
        position: relative;
        aspect-ratio: 3/4;
        overflow: hidden;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    }
    .mag-card-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .5s ease;
    }
    .mag-card:hover .mag-card-cover img {
        transform: scale(1.05);
    }
    .mag-card-cover-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 3rem;
    }

    /* Overlay gradient — apenas metade inferior com degrade fume */
    .mag-card-overlay {
        position: absolute;
        inset: auto 0 0 0;
        height: 50%;
        background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.85) 100%);
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 1rem;
        color: #fff;
    }
    .mag-card-category {
        font-size: .65rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #60a5fa;
        margin-bottom: .25rem;
    }
    .mag-card-title {
        font-size: .95rem;
        font-weight: 900;
        line-height: 1.2;
        margin-bottom: .25rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .mag-card-edition {
        font-size: .7rem;
        color: rgba(255,255,255,0.7);
    }

    /* Featured badge */
    .mag-card-featured {
        position: absolute;
        top: .75rem;
        right: .75rem;
        padding: .25rem .75rem;
        background: linear-gradient(135deg, #f59e0b, #ef4444);
        color: #fff;
        font-size: .65rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 999px;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        z-index: 2;
    }

    /* "Abrir revista" hover indicator */
    .mag-card-action {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.8);
        padding: .75rem 1.5rem;
        background: rgba(255,255,255,0.95);
        color: #1F5EDB;
        border-radius: 999px;
        font-weight: 900;
        font-size: .85rem;
        opacity: 0;
        transition: all .3s ease;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        white-space: nowrap;
        pointer-events: none;
    }
    .mag-card:hover .mag-card-action {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }

    /* Empty state */
    .mag-empty {
        max-width: 500px;
        margin: 3rem auto;
        padding: 3rem 2rem;
        text-align: center;
        background: #fff;
        border-radius: 1.5rem;
        border: 1px solid rgba(0,0,0,0.04);
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }
    .dark .mag-empty {
        background: #1e293b;
        border-color: rgba(255,255,255,0.06);
    }
    .mag-empty i {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }
    .mag-empty h3 {
        font-size: 1.25rem;
        font-weight: 900;
        color: #1e293b;
        margin-bottom: .5rem;
    }
    .mag-empty p {
        color: #64748b;
        font-size: .9rem;
    }
    .dark .mag-empty h3 { color: #f1f5f9; }
    .dark .mag-empty p { color: #94a3b8; }

    /* Mobile Swiper */
    .mag-mobile-swiper { display: none; }
    .mag-mobile-swiper .mag-card {
        margin: 0 auto;
        max-width: 320px;
    }
    .mag-mobile-swiper .swiper-pagination-bullet-active {
        background: #1F5EDB;
    }

    @media (max-width: 640px) {
        .mag-grid { display: none; }
        .mag-mobile-swiper { display: block; }
        .mag-hero { padding: 2.5rem 1rem 1.5rem; }
        .mag-filters { margin-bottom: 1.5rem; }
    }

    /* Pagination */
    .mag-pagination {
        max-width: 1280px;
        margin: 2.5rem auto 4rem;
        padding: 0 1rem;
        display: flex;
        justify-content: center;
    }
    .mag-pagination nav {
        display: flex;
        gap: .375rem;
        align-items: center;
        flex-wrap: wrap;
        justify-content: center;
    }
    .mag-pagination nav a,
    .mag-pagination nav span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 .75rem;
        border-radius: .75rem;
        background: #ffffff !important;
        border: 1.5px solid #e2e8f0 !important;
        color: #475569 !important;
        font-weight: 700;
        font-size: .85rem;
        text-decoration: none;
        transition: all .2s;
    }
    .mag-pagination nav a:hover {
        background: linear-gradient(135deg, #1F5EDB, #177FD6) !important;
        color: #fff !important;
        border-color: #1F5EDB !important;
        box-shadow: 0 4px 12px rgba(31,94,219,0.3);
    }
    .mag-pagination nav span[aria-current="page"] {
        background: linear-gradient(135deg, #1F5EDB, #177FD6) !important;
        color: #fff !important;
        border-color: #1F5EDB !important;
        box-shadow: 0 4px 12px rgba(31,94,219,0.3);
    }
    .mag-pagination nav span.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
        background: #f1f5f9 !important;
        color: #94a3b8 !important;
        border-color: #e2e8f0 !important;
    }

    /* Interest notice */
    .mag-interest-notice {
        max-width: 700px;
        margin: 0 auto 2rem;
        padding: 1rem 1.25rem;
        border-radius: 1rem;
        background: linear-gradient(135deg, rgba(31,94,219,0.06), rgba(23,127,214,0.06));
        border: 1px solid rgba(31,94,219,0.15);
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: .875rem;
        color: #334155;
    }
    .mag-interest-notice i {
        font-size: 1.25rem;
        color: #1F5EDB;
    }
    .mag-interest-notice a {
        color: #1F5EDB;
        font-weight: 800;
        text-decoration: underline;
    }
    .dark .mag-interest-notice {
        color: #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="mag-hero">
    <div class="relative z-10">
        <div class="mag-hero-badge">
            <i class="fas fa-newspaper"></i> Banca Digital
        </div>
        <h1>Revistas &amp; <span class="accent">Manchetes</span></h1>
        <p>Folheie edicoes completas com efeito de pagina real, som imersivo e leitura em tela cheia.</p>
    </div>
</div>

@if(auth()->check() && !$hasNewsInterest)
    <div class="mag-interest-notice">
        <i class="fas fa-info-circle"></i>
        <div>
            Para receber notificacoes de novas edicoes, marque <strong>Noticias</strong> como interesse no seu perfil.
            <a href="{{ route('panel.profile.edit') }}">Editar perfil</a>
        </div>
    </div>
@endif

<form method="GET" class="mag-filters">
    <div class="mag-filter-input">
        <i class="fas fa-search"></i>
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por titulo ou edicao...">
    </div>
    @if($categories->count())
        <select name="category" onchange="this.form.submit()" class="mag-category-select">
            <option value="">Todas as categorias</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" @selected($category === $cat)>{{ $cat }}</option>
            @endforeach
        </select>
    @endif
    <button type="submit" style="padding: .85rem 1.5rem; border-radius: 999px; background: linear-gradient(135deg, #1F5EDB, #177FD6); color: #fff; font-weight: 800; font-size: .85rem; border: 0; cursor: pointer; box-shadow: 0 8px 20px rgba(31,94,219,0.3);">Filtrar</button>
</form>

@if($magazines->count())
    {{-- Desktop Grid --}}
    <div class="mag-grid">
        @foreach($magazines as $m)
            <a href="{{ route('magazines.show', $m->slug) }}" class="mag-card">
                <div class="mag-card-cover">
                    @if($m->is_featured)
                        <div class="mag-card-featured"><i class="fas fa-star"></i> Destaque</div>
                    @endif
                    @if($m->thumbnail_url)
                        <img src="{{ $m->thumbnail_url }}" alt="{{ $m->title }}" loading="lazy">
                    @else
                        <div class="mag-card-cover-placeholder"><i class="fas fa-book-open"></i></div>
                    @endif
                    <div class="mag-card-overlay">
                        <div class="mag-card-category">{{ $m->category ?: 'Revista' }}</div>
                        <div class="mag-card-title">{{ $m->title }}</div>
                        <div class="mag-card-edition">
                            {{ $m->edition }}
                            @if($m->published_at) &middot; {{ $m->published_at->format('M/Y') }} @endif
                        </div>
                    </div>
                    <div class="mag-card-action">
                        <i class="fas fa-book-open mr-1"></i> Abrir revista
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Mobile Swiper --}}
    <div class="mag-mobile-swiper">
        <div class="swiper magSwiper">
            <div class="swiper-wrapper">
                @foreach($magazines as $m)
                    <div class="swiper-slide" style="padding: 0 1rem 1rem;">
                        <a href="{{ route('magazines.show', $m->slug) }}" class="mag-card">
                            <div class="mag-card-cover">
                                @if($m->is_featured)
                                    <div class="mag-card-featured"><i class="fas fa-star"></i> Destaque</div>
                                @endif
                                @if($m->thumbnail_url)
                                    <img src="{{ $m->thumbnail_url }}" alt="{{ $m->title }}" loading="lazy">
                                @else
                                    <div class="mag-card-cover-placeholder"><i class="fas fa-book-open"></i></div>
                                @endif
                                <div class="mag-card-overlay">
                                    <div class="mag-card-category">{{ $m->category ?: 'Revista' }}</div>
                                    <div class="mag-card-title">{{ $m->title }}</div>
                                    <div class="mag-card-edition">
                                        {{ $m->edition }}
                                        @if($m->published_at) &middot; {{ $m->published_at->format('M/Y') }} @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination" style="margin-top: 1rem; position: static;"></div>
        </div>
    </div>

    <div class="mag-pagination">
        @if($magazines->hasPages())
            <nav>
                @if($magazines->onFirstPage())
                    <span class="disabled">&laquo;</span>
                @else
                    <a href="{{ $magazines->previousPageUrl() }}">&laquo;</a>
                @endif

                @foreach($magazines->getUrlRange(1, $magazines->lastPage()) as $page => $url)
                    @if($page == $magazines->currentPage())
                        <span aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($magazines->hasMorePages())
                    <a href="{{ $magazines->nextPageUrl() }}">&raquo;</a>
                @else
                    <span class="disabled">&raquo;</span>
                @endif
            </nav>
        @endif
    </div>
@else
    <div class="mag-empty">
        <i class="fas fa-book-open"></i>
        <h3>Nenhuma revista disponivel ainda</h3>
        <p>Em breve novas edicoes serao publicadas aqui.</p>
    </div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
(function() {
    if (window.matchMedia('(max-width: 640px)').matches) {
        new Swiper('.magSwiper', {
            slidesPerView: 1,
            spaceBetween: 12,
            centeredSlides: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            grabCursor: true,
        });
    }
})();
</script>
@endpush
