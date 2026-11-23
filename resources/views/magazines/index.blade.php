@extends('layouts.app')

@section('title', 'Banca de Revistas - SOMOS UNN')

@push('styles')
@php
    $overlayOpacity = (int) (\App\Models\Setting::get('magazines_overlay_opacity', 70));
    $overlayAlpha = round($overlayOpacity / 100, 2);
@endphp
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
    .custom-select-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-select-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-select-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-select-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

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

    /* Overlay — faixa fume semi-transparente na base para texto legivel */
    .mag-card-overlay {
        position: absolute;
        inset: auto 0 0 0;
        padding: .75rem 1rem;
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        background: rgba(0, 0, 0, {{ $overlayAlpha }});
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);
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
        <p>Folheie edições completas com efeito de página real, som imersivo e leitura em tela cheia.</p>
    </div>
</div>

@if(auth()->check() && !$hasNewsInterest)
    <div class="mag-interest-notice">
        <i class="fas fa-info-circle"></i>
        <div>
            Para receber notificações de novas edições, marque <strong>Notícias</strong> como interesse no seu perfil.
            <a href="{{ route('panel.profile.edit') }}">Editar perfil</a>
        </div>
    </div>
@endif

<form method="GET" class="mag-filters" id="magazines-filter-form">
    <div class="mag-filter-input">
        <i class="fas fa-search"></i>
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por título ou edição...">
    </div>
    @if($categories->count())
        @php
            $selectedCategoryLabel = $category ?: 'Todas as categorias';
        @endphp
        <div class="relative select-none z-[45]" id="custom-category-container" style="min-width: 230px;">
            <input type="hidden" name="category" id="filtro-category" value="{{ $category }}">
            <button type="button" id="custom-category-trigger"
                class="w-full h-[48px] flex items-center justify-between px-5 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-left rounded-full border border-slate-200/90 dark:border-slate-800 shadow-2xs transition-all outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer">
                <span class="flex items-center min-w-0 pr-2">
                    <i class="fas fa-layer-group text-slate-400 dark:text-slate-500 mr-2.5 text-xs shrink-0"></i>
                    <span id="custom-category-label" class="truncate text-sm font-bold text-slate-800 dark:text-slate-200">
                        {{ $selectedCategoryLabel }}
                    </span>
                </span>
                <i id="custom-category-arrow" class="fas fa-chevron-down text-slate-400 text-xs transition-transform duration-200 shrink-0"></i>
            </button>
            <div id="custom-category-menu"
                class="hidden absolute top-full mt-2 left-0 right-0 w-full min-w-[240px] bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 py-2.5 z-[9999] overflow-hidden transform opacity-0 scale-95 transition-all duration-200 ease-out origin-top text-left shadow-[0_25px_60px_rgba(8,112,184,0.22),0_15px_30px_rgba(0,0,0,0.12)]">
                <div class="px-3.5 pb-2 mb-1.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    <span>Categorias</span>
                    <span class="text-[10px] bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full text-slate-500 font-bold">{{ $categories->count() + 1 }} opções</span>
                </div>
                <div class="max-h-60 overflow-y-auto custom-select-scrollbar px-1.5 space-y-1">
                    <div class="custom-category-opt px-3.5 py-2.5 rounded-xl text-sm font-semibold cursor-pointer transition-all flex items-center justify-between {{ empty($category) ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/25' : 'text-slate-800 dark:text-slate-200 hover:bg-blue-50/80 dark:hover:bg-slate-800 hover:text-blue-700' }}"
                        data-value="" data-label="Todas as categorias">
                        <span class="flex items-center gap-2 truncate">
                            <span class="opt-dot w-2 h-2 rounded-full {{ empty($category) ? 'bg-white' : 'bg-slate-300' }}"></span>
                            <span class="truncate">Todas as categorias</span>
                        </span>
                        <i class="fas fa-check text-xs check-icon {{ empty($category) ? 'text-white' : 'hidden' }}"></i>
                    </div>
                    @foreach($categories as $cat)
                        @php $isCatSelected = $category === $cat; @endphp
                        <div class="custom-category-opt px-3.5 py-2.5 rounded-xl text-sm font-semibold cursor-pointer transition-all flex items-center justify-between {{ $isCatSelected ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/25' : 'text-slate-800 dark:text-slate-200 hover:bg-blue-50/80 dark:hover:bg-slate-800 hover:text-blue-700' }}"
                            data-value="{{ $cat }}" data-label="{{ $cat }}">
                            <span class="flex items-center gap-2 truncate">
                                <span class="opt-dot w-2 h-2 rounded-full {{ $isCatSelected ? 'bg-white' : 'bg-slate-300' }}"></span>
                                <span class="truncate">{{ $cat }}</span>
                            </span>
                            <i class="fas fa-check text-xs check-icon {{ $isCatSelected ? 'text-white' : 'hidden' }}"></i>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    <button type="submit" style="height: 48px; padding: 0 1.75rem; border-radius: 999px; background: linear-gradient(135deg, #1F5EDB, #177FD6); color: #fff; font-weight: 800; font-size: .85rem; border: 0; cursor: pointer; box-shadow: 0 8px 20px rgba(31,94,219,0.3); display: inline-flex; align-items: center; justify-content: center; gap: .5rem;" class="transition-all hover:scale-105 active:scale-95">
        <i class="fas fa-filter text-xs"></i>
        <span>Filtrar</span>
    </button>
    @if(!empty($q) || !empty($category))
        <a href="{{ route('magazines.index') }}"
            style="height: 48px; padding: 0 1.25rem; border-radius: 999px; background: #f1f5f9; color: #64748b; font-weight: 700; font-size: .85rem; border: 1.5px solid #e2e8f0; display: inline-flex; align-items: center; justify-content: center; gap: .4rem; text-decoration: none;"
            class="hover:bg-slate-200 transition-colors" title="Limpar filtros">
            <i class="fas fa-times text-xs"></i>
            <span>Limpar</span>
        </a>
    @endif
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
                            @if($m->published_at) &middot; {{ $m->published_at->locale('pt_BR')->translatedFormat('M/Y') }} @endif
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
                                        @if($m->published_at) &middot; {{ $m->published_at->locale('pt_BR')->translatedFormat('M/Y') }} @endif
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
        <h3>Nenhuma revista disponível ainda</h3>
        <p>Em breve, novas edições serão publicadas aqui.</p>
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

    // Engine de Dropdown Customizado Padronizado
    function initCustomDropdown(containerId, triggerId, menuId, arrowId, labelId, inputId, optClass) {
        const container = document.getElementById(containerId);
        const trigger = document.getElementById(triggerId);
        const menu = document.getElementById(menuId);
        const arrow = document.getElementById(arrowId);
        const label = document.getElementById(labelId);
        const input = document.getElementById(inputId);
        const options = document.querySelectorAll('.' + optClass);

        if (!trigger || !menu || !container) return;
        let isOpen = false;

        const openMenu = () => {
            container.style.zIndex = '100';

            // Checagem inteligente de viewport para abrir para cima se o espaço abaixo for insuficiente
            const triggerRect = trigger.getBoundingClientRect();
            const spaceBelow = window.innerHeight - triggerRect.bottom;
            if (spaceBelow < 280 && triggerRect.top > 280) {
                menu.classList.remove('top-full', 'mt-2', 'origin-top');
                menu.classList.add('bottom-full', 'mb-2', 'origin-bottom');
            } else {
                menu.classList.remove('bottom-full', 'mb-2', 'origin-bottom');
                menu.classList.add('top-full', 'mt-2', 'origin-top');
            }

            isOpen = true;
            menu.classList.remove('hidden');
            requestAnimationFrame(() => {
                menu.classList.remove('opacity-0', 'scale-95');
                menu.classList.add('opacity-100', 'scale-100');
            });
            if (arrow) arrow.classList.add('rotate-180');
            trigger.setAttribute('aria-expanded', 'true');
        };

        const closeMenu = () => {
            if (!isOpen) return;
            isOpen = false;
            menu.classList.remove('opacity-100', 'scale-100');
            menu.classList.add('opacity-0', 'scale-95');
            if (arrow) arrow.classList.remove('rotate-180');
            trigger.setAttribute('aria-expanded', 'false');
            container.style.zIndex = '';
            setTimeout(() => {
                if (!isOpen) menu.classList.add('hidden');
            }, 180);
        };

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            isOpen ? closeMenu() : openMenu();
        });

        options.forEach(opt => {
            opt.addEventListener('click', (e) => {
                e.stopPropagation();
                const val = opt.getAttribute('data-value');
                const lbl = opt.getAttribute('data-label');

                if (input) input.value = val;
                if (label) label.textContent = lbl;

                options.forEach(o => {
                    o.classList.remove('bg-gradient-to-r', 'from-blue-600', 'to-blue-700', 'text-white', 'shadow-md', 'shadow-blue-500/25');
                    o.classList.add('text-slate-800', 'hover:bg-blue-50/80', 'hover:text-blue-700');
                    const check = o.querySelector('.check-icon');
                    if (check) { check.classList.add('hidden'); check.classList.remove('text-white'); }
                    const dot = o.querySelector('.opt-dot');
                    if (dot) { dot.classList.add('bg-slate-300'); dot.classList.remove('bg-white'); }
                });

                opt.classList.remove('text-slate-800', 'hover:bg-blue-50/80', 'hover:text-blue-700');
                opt.classList.add('bg-gradient-to-r', 'from-blue-600', 'to-blue-700', 'text-white', 'shadow-md', 'shadow-blue-500/25');
                const check = opt.querySelector('.check-icon');
                if (check) { check.classList.remove('hidden'); check.classList.add('text-white'); }
                const dot = opt.querySelector('.opt-dot');
                if (dot) { dot.classList.remove('bg-slate-300'); dot.classList.add('bg-white'); }

                closeMenu();

                // Submeter formulário automaticamente ao selecionar para agilidade
                const form = document.getElementById('magazines-filter-form');
                if (form) form.submit();
            });
        });

        document.addEventListener('click', (e) => {
            if (isOpen && !container.contains(e.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isOpen) {
                closeMenu();
            }
        });
    }

    initCustomDropdown('custom-category-container', 'custom-category-trigger', 'custom-category-menu', 'custom-category-arrow', 'custom-category-label', 'filtro-category', 'custom-category-opt');
})();
</script>
@endpush
