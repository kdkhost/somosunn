@extends('layouts.app')

@php
    $storeBioHtml = \App\Support\RichText::toHtml($store->bio);
    $coinName = (string) (app(\App\Services\PointsExchangeService::class)->settings()['coin_name'] ?? 'UNNBIT');
    $catalogSearch = trim((string) request('q', ''));
    $catalogChannel = trim((string) request('canal', ''));
    $catalogType = trim((string) request('tipo', ''));
    $catalogPrice = trim((string) request('preco', ''));
    $catalogSort = trim((string) request('ordem', 'featured'));
    $isPlatformStore = $store->isPlatformStore();
    $themePrimary = '#1F5EDB';
    $themeDark = '#0F172A';
    $themeSoft = '#f8fbff';

    $discountPercent = static function ($product): ?int {
        $basePrice = (float) ($product->price ?? 0);
        $salePrice = (float) ($product->sale_price ?? 0);

        if ($basePrice <= 0 || $salePrice <= 0 || $salePrice >= $basePrice) {
            return null;
        }

        return (int) round((($basePrice - $salePrice) / $basePrice) * 100);
    };

    $filteredProducts = $products->filter(function ($product) use ($catalogSearch, $catalogChannel, $catalogType, $catalogPrice) {
        if ($catalogSearch !== '') {
            $haystack = strtolower(trim((string) ($product->title . ' ' . $product->excerpt . ' ' . strip_tags((string) $product->description))));
            if (!str_contains($haystack, strtolower($catalogSearch))) {
                return false;
            }
        }

        if ($catalogType !== '' && (string) $product->type !== $catalogType) {
            return false;
        }

        if ($catalogChannel === 'store' && !$product->supportsInternalCheckout()) {
            return false;
        }
        if ($catalogChannel === 'points' && !$product->supportsPointsRedemption()) {
            return false;
        }
        if ($catalogChannel === 'external' && !$product->supportsExternalCheckout()) {
            return false;
        }

        $effectivePrice = (float) $product->effective_price;
        if ($catalogPrice === 'under_100' && $effectivePrice >= 100) {
            return false;
        }
        if ($catalogPrice === '100_300' && ($effectivePrice < 100 || $effectivePrice > 300)) {
            return false;
        }
        if ($catalogPrice === '300_700' && ($effectivePrice < 300 || $effectivePrice > 700)) {
            return false;
        }
        if ($catalogPrice === 'above_700' && $effectivePrice <= 700) {
            return false;
        }

        return true;
    })->values();

    if ($catalogSort === 'lowest') {
        $filteredProducts = $filteredProducts->sortBy(fn ($product) => (float) $product->effective_price)->values();
    } elseif ($catalogSort === 'highest') {
        $filteredProducts = $filteredProducts->sortByDesc(fn ($product) => (float) $product->effective_price)->values();
    } elseif ($catalogSort === 'latest') {
        $filteredProducts = $filteredProducts->sortByDesc(fn ($product) => (int) $product->id)->values();
    } else {
        $filteredProducts = $filteredProducts->sortByDesc(function ($product) use ($discountPercent) {
            return ($product->is_featured ? 100000 : 0) + (int) ($discountPercent($product) ?? 0) + (int) $product->id;
        })->values();
    }

    $heroProduct = $filteredProducts->firstWhere('is_featured', true)
        ?? $filteredProducts->first()
        ?? ($products->firstWhere('is_featured', true) ?? $products->first());
    $heroDiscount = $heroProduct ? $discountPercent($heroProduct) : null;
    $offerProducts = $filteredProducts->filter(fn ($product) => $product->is_featured || ($discountPercent($product) ?? 0) > 0)->take(10)->values();
    if ($offerProducts->isEmpty()) {
        $offerProducts = $filteredProducts->take(10)->values();
    }

    $gridProducts = $filteredProducts->take(15)->values();
    if ($gridProducts->isEmpty()) {
        $gridProducts = $products->take(15)->values();
    }

    $whatsappUrl = null;
    if (filled($store->whatsapp)) {
        $whatsappDigits = preg_replace('/\D+/', '', (string) $store->whatsapp);
        if ($whatsappDigits) {
            $whatsappUrl = 'https://wa.me/' . $whatsappDigits;
        }
    }

    $socialLinks = collect([
        ['label' => 'Instagram', 'url' => $store->instagram_url, 'icon' => 'fab fa-instagram'],
        ['label' => 'Facebook', 'url' => $store->facebook_url, 'icon' => 'fab fa-facebook-f'],
        ['label' => 'YouTube', 'url' => $store->youtube_url, 'icon' => 'fab fa-youtube'],
        ['label' => 'Site', 'url' => $store->website_url, 'icon' => 'fas fa-globe'],
        ['label' => 'WhatsApp', 'url' => $whatsappUrl, 'icon' => 'fab fa-whatsapp'],
    ])->filter(fn ($item) => filled($item['url']))->values();

    $navLinks = collect([
        ['label' => 'Home', 'href' => route('seller-stores.show', $store->slug), 'visible' => true],
        ['label' => 'Ofertas', 'href' => '#ofertas', 'visible' => $offerProducts->isNotEmpty()],
        ['label' => 'Produtos', 'href' => '#produtos', 'visible' => $products->isNotEmpty()],
        ['label' => 'Cursos', 'href' => '#conteudos', 'visible' => $courses->isNotEmpty()],
        ['label' => 'Mentorias', 'href' => '#conteudos', 'visible' => $mentorships->isNotEmpty()],
        ['label' => 'Eventos', 'href' => '#conteudos', 'visible' => $events->isNotEmpty()],
        ['label' => 'Sobre a loja', 'href' => '#sobre-loja', 'visible' => true],
        ['label' => 'Atendimento', 'href' => '#atendimento', 'visible' => true],
    ])->filter(fn ($item) => $item['visible'])->values();

    $serviceHighlights = collect([
        ['icon' => 'fas fa-shield-halved', 'title' => 'Compra segura', 'text' => 'Checkout integrado ao marketplace com split e governanca centralizada.'],
        ['icon' => 'fas fa-truck-fast', 'title' => 'Frete calculado', 'text' => 'Produtos fisicos usam cotacao dos Correios direto no fluxo de compra.'],
        ['icon' => 'fas fa-coins', 'title' => 'Troca de pontos', 'text' => 'Itens elegiveis podem ser vendidos e resgatados com ' . $coinName . '.'],
        ['icon' => 'fas fa-headset', 'title' => 'Atendimento da loja', 'text' => 'Canais oficiais e identidade da marca em uma vitrine dedicada.'],
    ]);

    $contentCards = collect();
    foreach ($courses as $course) {
        $contentCards->push(['type' => 'Curso', 'title' => $course->title, 'excerpt' => \Illuminate\Support\Str::limit(strip_tags((string) $course->short_description), 120), 'href' => route('courses.show', $course->slug ?: $course->id), 'icon' => 'fas fa-graduation-cap']);
    }
    foreach ($mentorships as $mentorship) {
        $contentCards->push(['type' => 'Mentoria', 'title' => $mentorship->title, 'excerpt' => \Illuminate\Support\Str::limit(strip_tags((string) $mentorship->description), 120), 'href' => route('mentorships.show', $mentorship), 'icon' => 'fas fa-user-tie']);
    }
    foreach ($events as $event) {
        $contentCards->push(['type' => 'Evento', 'title' => $event->title, 'excerpt' => \Illuminate\Support\Str::limit(strip_tags((string) $event->description), 120), 'href' => route('events.show', $event), 'icon' => 'fas fa-calendar-days']);
    }
    $contentCards = $contentCards->take(6)->values();

    $catalogCount = $filteredProducts->count();
    $activeChannelCount = collect([
        $products->contains(fn ($product) => $product->supportsInternalCheckout()),
        $products->contains(fn ($product) => $product->supportsPointsRedemption()),
        $products->contains(fn ($product) => $product->supportsExternalCheckout()),
    ])->filter()->count();
    $heroHeadline = $heroProduct
        ? 'Os produtos em destaque da ' . $store->brand_name . ' com condicoes especiais por tempo limitado.'
        : 'Explore a loja oficial da ' . $store->brand_name . ' dentro do ecossistema UNN.';
    $heroText = $heroProduct
        ? ($heroProduct->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $heroProduct->description), 170))
        : 'Uma vitrine premium com identidade propria, compra direta, pontos e canais oficiais de atendimento.';
@endphp

@push('styles')
    <style>
        .store-home-shell {
            background:
                radial-gradient(circle at top left, rgba(31, 94, 219, 0.14), transparent 24%),
                radial-gradient(circle at top right, rgba(15, 23, 42, 0.12), transparent 26%),
                linear-gradient(180deg, {{ $themeSoft }} 0%, #eef4ff 26%, #ffffff 62%);
        }

        .store-home-wrap {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .store-home-card {
            border: 1px solid rgba(148, 163, 184, 0.16);
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.06);
        }

        .store-topbar {
            background: linear-gradient(135deg, {{ $themeDark }} 0%, #16243f 100%);
            color: #fff;
        }

        .store-main-nav {
            background: linear-gradient(135deg, {{ $themePrimary }} 0%, #2b7cff 100%);
            color: #fff;
        }

        .store-sale-badge,
        .store-button-primary {
            background: linear-gradient(135deg, {{ $themePrimary }} 0%, #2b7cff 100%);
            color: #fff;
        }

        .store-header-search,
        .store-filter-field {
            border: 1px solid rgba(148, 163, 184, 0.24);
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .store-header-search:focus-within,
        .store-filter-field:focus {
            border-color: {{ $themePrimary }};
            box-shadow: 0 0 0 4px rgba(31, 94, 219, 0.1);
        }

        .store-main-nav a:hover,
        .store-button-primary:hover {
            filter: brightness(1.04);
        }

        .store-hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, {{ $themeDark }} 0%, #16243f 48%, {{ $themePrimary }} 100%);
        }

        .store-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.12), transparent 28%),
                linear-gradient(90deg, rgba(15, 23, 42, 0.16) 0%, rgba(15, 23, 42, 0.08) 100%);
            pointer-events: none;
        }

        .store-product-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .store-grid-card {
            display: flex;
            min-height: 100%;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.05);
            transition: transform 0.28s cubic-bezier(.4,0,.2,1), box-shadow 0.28s cubic-bezier(.4,0,.2,1), border-color 0.28s ease;
        }

        .store-grid-card:hover {
            transform: translateY(-6px);
            border-color: rgba(31, 94, 219, 0.22);
            box-shadow: 0 28px 55px rgba(15, 23, 42, 0.12);
        }

        .store-card-image {
            aspect-ratio: 1 / 1;
            background: linear-gradient(180deg, #fafafa 0%, #f0f4fb 100%);
        }

        .store-line-clamp-2,
        .store-line-clamp-3,
        .store-line-clamp-4 {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .store-line-clamp-2 { -webkit-line-clamp: 2; }
        .store-line-clamp-3 { -webkit-line-clamp: 3; }
        .store-line-clamp-4 { -webkit-line-clamp: 4; }

        .store-button-secondary {
            background: #fff;
            color: {{ $themeDark }};
        }

        .store-newsletter {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, {{ $themeDark }} 0%, #16243f 58%, {{ $themePrimary }} 100%);
        }

        .store-newsletter::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(255, 255, 255, 0.05), transparent 22%);
            pointer-events: none;
        }

        .store-richtext,
        .store-richtext p,
        .store-richtext ul,
        .store-richtext ol,
        .store-richtext blockquote {
            color: #475569;
            line-height: 1.85;
        }

        .store-richtext p,
        .store-richtext ul,
        .store-richtext ol,
        .store-richtext blockquote {
            margin-top: 0.85rem;
            margin-bottom: 0;
        }

        .store-richtext ul,
        .store-richtext ol {
            padding-left: 1.25rem;
        }

        .store-richtext a {
            color: {{ $themePrimary }};
            font-weight: 700;
        }

        @media (max-width: 1279px) { .store-product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 1023px) { .store-product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 767px) { .store-home-wrap { padding: 0 0.75rem; } }
        @media (max-width: 520px) { .store-product-grid { grid-template-columns: minmax(0, 1fr); } }
    </style>
@endpush

@section('title', ($store->brand_name ?: 'Loja') . ' - UNN')

@section('content')
    <div class="store-home-shell min-h-screen pb-16 md:pb-20">
        <div class="store-home-wrap">
            <section class="store-topbar overflow-hidden rounded-t-[1.65rem] text-white">
                <div class="flex flex-col gap-3 px-4 py-3 text-[11px] font-semibold sm:px-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-white/90">
                        @if($store->support_phone)
                            <span class="inline-flex items-center gap-2"><i class="fas fa-phone-volume text-[10px]"></i> {{ $store->support_phone }}</span>
                        @endif
                        @if($store->support_email)
                            <span class="inline-flex items-center gap-2"><i class="fas fa-envelope text-[10px]"></i> {{ $store->support_email }}</span>
                        @endif
                        <span class="inline-flex items-center gap-2"><i class="fas fa-location-dot text-[10px]"></i> {{ $isPlatformStore ? 'Loja oficial da plataforma' : 'Loja oficial ' . $store->brand_name }}</span>
                    </div>

                    @if($socialLinks->isNotEmpty())
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach($socialLinks->take(5) as $link)
                                <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white transition hover:-translate-y-0.5">
                                    <i class="{{ $link['icon'] }} text-[12px]"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            <section class="store-home-card overflow-hidden rounded-b-[1.65rem] bg-white">
                <div class="flex flex-wrap items-center gap-4 px-4 py-4 sm:px-5 lg:flex-nowrap">
                    {{-- Logo + Brand (mais espaço) --}}
                    <div class="flex items-center gap-4 flex-shrink-0">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-[1.2rem] border border-slate-200 bg-white shadow-sm">
                            @if($store->logo_url)
                                <img src="{{ $store->logo_url }}" alt="{{ $store->brand_name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-2xl text-slate-300">
                                    <i class="fas fa-store"></i>
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-xl font-black tracking-tight text-slate-900 sm:text-2xl">{{ $store->brand_name }}</h1>
                            <p class="mt-0.5 text-xs font-medium text-slate-500 sm:text-sm">{{ $store->tagline ?: ($isPlatformStore ? 'Loja institucional oficial dentro do ecossistema UNN.' : 'Loja virtual oficial dentro do marketplace UNN.') }}</p>
                        </div>
                    </div>

                    {{-- Busca (menor) --}}
                    <form method="GET" class="store-header-search hidden lg:flex items-center rounded-full px-3 py-2 flex-1 max-w-sm ml-auto">
                        <i class="fas fa-magnifying-glass ml-1 mr-2 text-slate-400 text-xs"></i>
                        <input type="text" name="q" value="{{ $catalogSearch }}" placeholder="Buscar produtos..." class="min-w-0 flex-1 border-0 bg-transparent px-0 py-0 text-sm font-medium text-slate-900 outline-none focus:ring-0">
                        <input type="hidden" name="tipo" value="{{ $catalogType }}">
                        <input type="hidden" name="canal" value="{{ $catalogChannel }}">
                        <input type="hidden" name="preco" value="{{ $catalogPrice }}">
                        <input type="hidden" name="ordem" value="{{ $catalogSort }}">
                        <button type="submit" class="store-button-primary inline-flex items-center justify-center rounded-full px-4 py-2 text-xs font-bold shadow-md">Buscar</button>
                    </form>

                    {{-- Ícones --}}
                    <div class="flex items-center gap-2 flex-shrink-0 ml-auto lg:ml-0">
                        @if($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:-translate-y-0.5">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        @endif
                        @if($store->website_url)
                            <a href="{{ $store->website_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:-translate-y-0.5">
                                <i class="fas fa-globe"></i>
                            </a>
                        @endif
                        <a href="{{ route('seller-products.cart.show') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:-translate-y-0.5">
                            <i class="fas fa-cart-shopping"></i>
                        </a>
                    </div>
                </div>

                {{-- Busca mobile --}}
                <form method="GET" class="store-header-search flex lg:hidden items-center rounded-full px-3 py-2 mx-4 mb-3">
                    <i class="fas fa-magnifying-glass ml-1 mr-2 text-slate-400 text-xs"></i>
                    <input type="text" name="q" value="{{ $catalogSearch }}" placeholder="Buscar produtos..." class="min-w-0 flex-1 border-0 bg-transparent px-0 py-0 text-sm font-medium text-slate-900 outline-none focus:ring-0">
                    <input type="hidden" name="tipo" value="{{ $catalogType }}">
                    <input type="hidden" name="canal" value="{{ $catalogChannel }}">
                    <input type="hidden" name="preco" value="{{ $catalogPrice }}">
                    <input type="hidden" name="ordem" value="{{ $catalogSort }}">
                    <button type="submit" class="store-button-primary inline-flex items-center justify-center rounded-full px-4 py-2 text-xs font-bold shadow-md">Buscar</button>
                </form>

                <nav class="store-main-nav overflow-x-auto px-4 py-3 text-white sm:px-5">
                    <div class="flex min-w-max items-center gap-5 text-sm font-bold">
                        @foreach($navLinks as $link)
                            <a href="{{ $link['href'] }}" class="whitespace-nowrap transition">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </nav>
            </section>
            <section class="mt-5">
                {{-- Hero Banner --}}
                @if($store->banner_url)
                    <div class="store-home-card overflow-hidden rounded-[1.65rem] mb-5">
                        <div class="relative" style="min-height:200px; max-height:320px;">
                            <img src="{{ $store->banner_url }}" alt="{{ $store->brand_name }}" class="w-full h-full object-cover" style="min-height:200px; max-height:320px;">
                            <div class="absolute inset-0" style="background: linear-gradient(90deg, rgba(15,23,42,.7) 0%, rgba(15,23,42,.2) 60%, transparent 100%);"></div>
                            <div class="absolute inset-0 flex items-center px-8 md:px-12">
                                <div class="max-w-lg">
                                    <h2 class="text-2xl md:text-3xl font-black text-white tracking-tight">{{ $store->brand_name }}</h2>
                                    <p class="mt-2 text-sm md:text-base text-white/80 leading-relaxed">{{ $store->tagline ?: 'Explore nossa vitrine com produtos exclusivos e condições especiais.' }}</p>
                                    <div class="mt-4 flex flex-wrap gap-3">
                                        <a href="#produtos" class="store-button-primary inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-bold shadow-lg">
                                            <i class="fas fa-shopping-bag"></i> Ver produtos
                                        </a>
                                        @if($whatsappUrl)
                                            <a href="{{ $whatsappUrl }}" target="_blank" class="inline-flex items-center gap-2 rounded-full bg-white/20 backdrop-blur px-5 py-2.5 text-sm font-bold text-white border border-white/20 hover:bg-white/30 transition">
                                                <i class="fab fa-whatsapp"></i> Falar conosco
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Filtros rápidos --}}
                <div class="store-home-card rounded-[1.65rem] bg-white p-4 mb-5">
                    <form method="GET" class="flex flex-wrap items-center gap-3">
                        <input type="hidden" name="q" value="{{ $catalogSearch }}">
                        <select name="tipo" class="store-filter-field rounded-lg px-3 py-2 text-sm font-semibold text-slate-700" onchange="this.form.submit()">
                            <option value="">Todos os tipos</option>
                            <option value="digital" {{ $catalogType === 'digital' ? 'selected' : '' }}>Digital</option>
                            <option value="physical" {{ $catalogType === 'physical' ? 'selected' : '' }}>Físico</option>
                        </select>
                        <select name="canal" class="store-filter-field rounded-lg px-3 py-2 text-sm font-semibold text-slate-700" onchange="this.form.submit()">
                            <option value="">Todos os canais</option>
                            <option value="store" {{ $catalogChannel === 'store' ? 'selected' : '' }}>Loja virtual</option>
                            <option value="points" {{ $catalogChannel === 'points' ? 'selected' : '' }}>Troca de pontos</option>
                            <option value="external" {{ $catalogChannel === 'external' ? 'selected' : '' }}>Site externo</option>
                        </select>
                        <select name="preco" class="store-filter-field rounded-lg px-3 py-2 text-sm font-semibold text-slate-700" onchange="this.form.submit()">
                            <option value="">Qualquer preço</option>
                            <option value="under_100" {{ $catalogPrice === 'under_100' ? 'selected' : '' }}>Até R$100</option>
                            <option value="100_300" {{ $catalogPrice === '100_300' ? 'selected' : '' }}>R$100 - R$300</option>
                            <option value="300_700" {{ $catalogPrice === '300_700' ? 'selected' : '' }}>R$300 - R$700</option>
                            <option value="above_700" {{ $catalogPrice === 'above_700' ? 'selected' : '' }}>Acima de R$700</option>
                        </select>
                        <select name="ordem" class="store-filter-field rounded-lg px-3 py-2 text-sm font-semibold text-slate-700" onchange="this.form.submit()">
                            <option value="featured" {{ $catalogSort === 'featured' ? 'selected' : '' }}>Destaques</option>
                            <option value="latest" {{ $catalogSort === 'latest' ? 'selected' : '' }}>Mais recentes</option>
                            <option value="lowest" {{ $catalogSort === 'lowest' ? 'selected' : '' }}>Menor preço</option>
                            <option value="highest" {{ $catalogSort === 'highest' ? 'selected' : '' }}>Maior preço</option>
                        </select>
                        @if($catalogType || $catalogChannel || $catalogPrice || $catalogSort !== 'featured')
                            <a href="{{ route('seller-stores.show', $store->slug) }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-400 hover:text-red-500 transition">
                                <i class="fas fa-times"></i> Limpar
                            </a>
                        @endif
                        <span class="ml-auto text-xs font-bold text-slate-400">{{ $catalogCount }} resultado(s)</span>
                    </form>
                </div>

                {{-- Service highlights --}}
                <div class="grid auto-rows-fr gap-4 md:grid-cols-2 lg:grid-cols-4 mb-5">
                    @foreach($serviceHighlights as $highlight)
                        <article class="store-home-card flex h-full rounded-[1.65rem] bg-white p-5">
                            <div class="flex w-full items-start gap-4">
                                <div class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-lg" style="color: {{ $themePrimary }};">
                                    <i class="{{ $highlight['icon'] }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-base font-black leading-tight text-slate-900">{{ $highlight['title'] }}</h3>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ $highlight['text'] }}</p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
            <section id="ofertas" class="mt-8">
                <div class="mb-5 text-center">
                    <p class="text-xs font-black uppercase tracking-[0.26em] text-slate-400">Vitrine da marca</p>
                    <h2 id="produtos" class="mt-2 text-3xl font-black tracking-tight text-slate-900 md:text-4xl">Produtos publicados por {{ $store->brand_name }}</h2>
                    <p class="mt-3 text-sm font-medium text-slate-500">{{ $catalogCount > 0 ? $catalogCount : $products->count() }} item(ns) encontrados neste catalogo.</p>
                </div>

                @if($gridProducts->isNotEmpty())
                    <div class="store-product-grid">
                        @foreach($gridProducts as $product)
                            @php
                                $productDiscount = $discountPercent($product);
                                $canBuy = $product->supportsInternalCheckout();
                                $canRedeem = $product->supportsPointsRedemption() && $product->redeemableItem;
                                $canExternal = $product->supportsExternalCheckout();
                                $pointsCost = $canRedeem ? (int) $product->redeemableItem->points_cost : 0;
                            @endphp
                            <article class="store-grid-card rounded-[1.65rem] bg-white">
                                <div class="relative">
                                    <a href="{{ route('seller-stores.products.show', [$store->slug, $product->slug]) }}" class="store-card-image block overflow-hidden">
                                        @if($product->cover_url)
                                            <img src="{{ $product->cover_url }}" alt="{{ $product->title }}" class="h-full w-full object-cover transition duration-500 hover:scale-[1.03]">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-5xl text-slate-300">
                                                <i class="fas fa-box-open"></i>
                                            </div>
                                        @endif
                                    </a>

                                    <div class="absolute left-3 top-3 flex flex-col gap-2">
                                        @if($productDiscount)
                                            <span class="store-sale-badge inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em]">{{ $productDiscount }}% OFF</span>
                                        @endif
                                        @if($product->is_featured)
                                            <span class="inline-flex rounded-full bg-slate-900/85 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-white">Destaque</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-1 flex-col px-4 py-4">
                                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-slate-400">{{ $store->brand_name }}</p>
                                    <h3 class="store-line-clamp-2 mt-2 text-base font-black leading-tight text-slate-900">{{ $product->title }}</h3>
                                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ $product->salesChannelLabel() }}</p>
                                    <p class="store-line-clamp-2 mt-3 text-sm leading-6 text-slate-600">{{ $product->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 90) }}</p>

                                    <div class="mt-auto pt-4 space-y-2">
                                        <div class="flex flex-wrap items-end gap-2">
                                            <span class="text-xl font-black text-slate-900">R$ {{ number_format((float) $product->effective_price, 2, ',', '.') }}</span>
                                            @if($product->sale_price !== null && (float) $product->sale_price < (float) $product->price)
                                                <span class="text-sm font-bold text-slate-400 line-through">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</span>
                                            @endif
                                        </div>
                                        @if($product->supportsPointsRedemption() && $product->redeemableItem)
                                            <p class="text-xs font-bold text-amber-600">{{ number_format((int) $product->redeemableItem->points_cost, 0, ',', '.') }} {{ $coinName }}</p>
                                        @endif
                                    </div>

                                    <div class="mt-5 space-y-2">
                                        @if($canExternal)
                                            <a href="{{ $product->external_checkout_url }}" target="_blank" rel="noopener noreferrer" class="store-button-primary inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-black shadow-lg shadow-blue-700/20">
                                                <i class="fas fa-up-right-from-square"></i> Comprar no site externo
                                            </a>
                                        @else
                                            @if($canBuy)
                                                <form action="{{ route('seller-products.cart.add', $product) }}" method="POST" class="w-full">
                                                    @csrf
                                                    <input type="hidden" name="buy_now" value="1">
                                                    <button type="submit" class="store-button-primary inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-black shadow-lg shadow-blue-700/20">
                                                        <i class="fas fa-shopping-cart"></i> Comprar agora
                                                    </button>
                                                </form>
                                            @endif
                                            @if($canRedeem)
                                                <a href="{{ route('panel.redemptions.shop') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-amber-500/20 transition">
                                                    <i class="fas fa-coins"></i> Trocar por {{ number_format($pointsCost, 0, ',', '.') }} {{ $coinName }}
                                                </a>
                                            @endif
                                            @if(!$canBuy && !$canRedeem && !$canExternal)
                                                <a href="{{ route('seller-stores.products.show', [$store->slug, $product->slug]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 hover:bg-slate-50 transition">
                                                    Mais detalhes
                                                </a>
                                            @endif
                                        @endif
                                        <div class="flex items-center justify-between text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">
                                            <span>{{ $product->isPhysical() ? 'Produto fisico' : 'Produto digital' }}</span>
                                            @if($product->sku)
                                                <span>SKU {{ $product->sku }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @elseif($products->isNotEmpty())
                    <div class="store-home-card rounded-[1.65rem] bg-white p-8 text-center">
                        <h3 class="text-2xl font-black text-slate-900">Nenhum produto encontrado com esse filtro.</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">Ajuste a busca ou remova filtros para visualizar outras opcoes publicadas nesta loja.</p>
                        <a href="{{ route('seller-stores.show', $store->slug) }}" class="store-button-primary mt-5 inline-flex items-center justify-center rounded-full px-5 py-3 text-sm font-black shadow-lg shadow-blue-700/20">Limpar filtros</a>
                    </div>
                @else
                    <div class="store-home-card rounded-[1.65rem] bg-white p-8 text-center">
                        <h3 class="text-2xl font-black text-slate-900">Esta loja ainda esta preparando a vitrine.</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">Assim que o vendedor publicar produtos, a grade principal sera exibida aqui.</p>
                    </div>
                @endif
            </section>

            <section class="store-newsletter store-home-card mt-10 rounded-[1.85rem]">
                <div class="relative z-10 grid gap-6 px-6 py-10 lg:grid-cols-[minmax(0,1fr),auto] lg:items-center lg:px-8">
                    <div class="text-white">
                        <p class="text-xs font-black uppercase tracking-[0.26em] text-white/55">Destaque especial</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight md:text-4xl">Receba agora no seu atendimento um fluxo de compra mais profissional.</h2>
                        <p class="mt-4 max-w-3xl text-sm leading-8 text-white/74 md:text-base">A loja publica combina estrutura de ecommerce, vitrine do vendedor, conteudo agregado e canais oficiais para conversao direta.</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row lg:flex-col xl:flex-row">
                        <a href="#produtos" class="store-button-primary inline-flex items-center justify-center rounded-full px-5 py-3 text-sm font-black shadow-lg shadow-blue-900/30">Explorar produtos</a>
                        @if($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="store-button-secondary inline-flex items-center justify-center rounded-full px-5 py-3 text-sm font-black shadow-lg shadow-black/15">Falar com a loja</a>
                        @elseif($store->support_email)
                            <a href="mailto:{{ $store->support_email }}" class="store-button-secondary inline-flex items-center justify-center rounded-full px-5 py-3 text-sm font-black shadow-lg shadow-black/15">Entrar em contato</a>
                        @endif
                    </div>
                </div>
            </section>

            @if($contentCards->isNotEmpty())
                <section id="conteudos" class="mt-10">
                    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.26em] text-slate-400">Ecossistema da marca</p>
                            <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Mais do vendedor dentro da plataforma</h2>
                        </div>
                        <p class="text-sm font-medium text-slate-500">Cursos, mentorias e eventos publicados por {{ $store->brand_name }}.</p>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-3">
                        @foreach($contentCards as $contentCard)
                            <a href="{{ $contentCard['href'] }}" class="store-home-card flex h-full flex-col rounded-[1.65rem] bg-white p-6 transition hover:-translate-y-1">
                                <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-lg" style="color: {{ $themePrimary }};">
                                    <i class="{{ $contentCard['icon'] }}"></i>
                                </div>
                                <p class="mt-5 text-[11px] font-black uppercase tracking-[0.22em] text-slate-400">{{ $contentCard['type'] }}</p>
                                <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-900">{{ $contentCard['title'] }}</h3>
                                <p class="store-line-clamp-4 mt-3 flex-1 text-sm leading-7 text-slate-600">{{ $contentCard['excerpt'] }}</p>
                                <span class="mt-5 inline-flex items-center gap-2 text-sm font-black" style="color: {{ $themePrimary }};">Ver mais <i class="fas fa-arrow-right text-[11px]"></i></span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="mt-10 grid items-stretch gap-5 lg:grid-cols-3">
                <article id="sobre-loja" class="store-home-card flex flex-col rounded-[1.65rem] bg-white p-6">
                    <p class="text-xs font-black uppercase tracking-[0.26em] text-slate-400">Sobre a loja</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">{{ $store->brand_name }}</h2>
                    @if($storeBioHtml)
                        <div class="store-richtext mt-4 flex-1 text-sm">{!! $storeBioHtml !!}</div>
                    @else
                        <p class="mt-4 flex-1 text-sm leading-7 text-slate-600">A loja oficial de {{ $store->brand_name }} opera dentro do marketplace UNN com produtos proprios, identidade dedicada e os mesmos fluxos de pagamento e governanca usados pela plataforma.</p>
                    @endif
                </article>

                <article id="atendimento" class="store-home-card flex flex-col rounded-[1.65rem] bg-white p-6">
                    <p class="text-xs font-black uppercase tracking-[0.26em] text-slate-400">Atendimento</p>
                    <div class="mt-4 flex-1 space-y-4 text-sm text-slate-600">
                        @if($store->support_email)
                            <div class="flex items-center gap-3">
                                <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50" style="color: {{ $themePrimary }};">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 text-xs">E-mail</p>
                                    <p class="text-sm text-slate-600 truncate">{{ $store->support_email }}</p>
                                </div>
                            </div>
                        @endif
                        @if($store->support_phone)
                            <div class="flex items-center gap-3">
                                <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50" style="color: {{ $themePrimary }};">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 text-xs">Telefone</p>
                                    <p class="text-sm text-slate-600">{{ $store->support_phone }}</p>
                                </div>
                            </div>
                        @endif
                        @if($store->whatsapp)
                            <div class="flex items-center gap-3">
                                <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-600">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 text-xs">WhatsApp</p>
                                    <p class="text-sm text-slate-600">{{ $store->whatsapp }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2 pt-4 border-t border-slate-100">
                        @if($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="store-button-primary inline-flex items-center justify-center rounded-full px-4 py-2.5 text-xs font-bold shadow-md">
                                <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                            </a>
                        @endif
                        @if($store->website_url)
                            <a href="{{ $store->website_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700">
                                <i class="fas fa-globe mr-1"></i> Site
                            </a>
                        @endif
                    </div>
                </article>

                <article class="store-home-card flex flex-col rounded-[1.65rem] bg-white p-6">
                    <p class="text-xs font-black uppercase tracking-[0.26em] text-slate-400">Resumo da operação</p>
                    <div class="mt-4 flex-1 flex flex-col gap-3">
                        <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3">
                            <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50" style="color: {{ $themePrimary }};">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Produtos</p>
                                <p class="text-xl font-black text-slate-900">{{ $products->count() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3">
                            <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Conteúdos</p>
                                <p class="text-xl font-black text-slate-900">{{ $courses->count() + $mentorships->count() + $events->count() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3">
                            <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                <i class="fas fa-link"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Loja pública</p>
                                <p class="text-sm font-black text-slate-900">/loja/{{ $store->slug }}</p>
                            </div>
                        </div>
                    </div>
                </article>
            </section>
        </div>
    </div>
@endsection
