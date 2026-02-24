@extends('layouts.app')

@section('title', 'Marketplace - UNN')

@section('content')
@php
    $courses = $courses ?? collect();
    $mentorships = $mentorships ?? collect();
    $events = $events ?? collect();
    $testimonials = $testimonials ?? collect();
    $paymentsConfigured = (bool) ($paymentsConfigured ?? false);
    $canSellByUserId = $canSellByUserId ?? [];

    $user = auth()->user();
    $isAdmin = $user ? $user->isAdmin() : false;
    $canSell = $user ? $user->canSellOnMarketplace() : false;
    $canBuy = true;

    $q = trim((string) request()->query('q', ''));

    $resolveAssetUrl = function (?string $path): string {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        // Pastas públicas
        if (str_starts_with($path, 'uploads/') || str_starts_with($path, 'img/') || str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        // Arquivos salvos no disk "public" (storage/app/public -> /storage)
        return asset('storage/' . $path);
    };

    $resolveLinkUrl = function (?string $url): string {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '#')) {
            return $url;
        }
        if (str_starts_with($url, '/')) {
            return url($url);
        }
        return $url;
    };

    $sellerCanSell = function (?int $userId) use ($canSellByUserId): bool {
        if (!$userId) {
            return false;
        }
        return (bool) ($canSellByUserId[$userId] ?? false);
    };

    $totalResults = $courses->count() + $mentorships->count() + $events->count();

    $chips = [
        ['label' => 'Cursos', 'icon' => 'fas fa-book-open', 'href' => '#marketplace-courses'],
        ['label' => 'Mentorias', 'icon' => 'fas fa-user-tie', 'href' => '#marketplace-mentorships'],
        ['label' => 'Eventos', 'icon' => 'fas fa-calendar-alt', 'href' => '#marketplace-events'],
    ];

    $storeCategories = [
        [
            'label' => 'Cursos',
            'icon' => 'fas fa-book-open',
            'href' => '#marketplace-courses',
            'count' => (int) $courses->count(),
        ],
        [
            'label' => 'Mentorias',
            'icon' => 'fas fa-user-tie',
            'href' => '#marketplace-mentorships',
            'count' => (int) $mentorships->count(),
        ],
        [
            'label' => 'Eventos',
            'icon' => 'fas fa-calendar-alt',
            'href' => '#marketplace-events',
            'count' => (int) $events->count(),
        ],
    ];

    // Marketplace: Hero (banner rotativo)
    $marketplaceHeroEnabled = (string) \App\Models\Setting::get('marketplace_hero_enabled', '1') === '1';
    $marketplaceHeroAutoplay = (string) \App\Models\Setting::get('marketplace_hero_autoplay', '1') === '1';
    $marketplaceHeroAnimation = trim((string) (\App\Models\Setting::get('marketplace_hero_animation') ?: 'slide'));
    if (!in_array($marketplaceHeroAnimation, ['slide', 'fade'], true)) {
        $marketplaceHeroAnimation = 'slide';
    }
    $marketplaceHeroIntervalSecondsRaw = trim((string) (\App\Models\Setting::get('marketplace_hero_interval_seconds') ?: '6'));
    $marketplaceHeroIntervalSeconds = (int) ($marketplaceHeroIntervalSecondsRaw !== '' ? $marketplaceHeroIntervalSecondsRaw : 6);
    $marketplaceHeroIntervalSeconds = max(2, min(20, $marketplaceHeroIntervalSeconds));
    $marketplaceHeroIntervalMs = $marketplaceHeroIntervalSeconds * 1000;

    $marketplaceHeroSlides = [];
    foreach ([1, 2, 3] as $i) {
        $image = $resolveAssetUrl(\App\Models\Setting::get("marketplace_hero_slide_{$i}_image"));
        $imageMobile = $resolveAssetUrl(\App\Models\Setting::get("marketplace_hero_slide_{$i}_image_mobile"));
        $title = trim((string) (\App\Models\Setting::get("marketplace_hero_slide_{$i}_title") ?: ''));
        $subtitle = trim((string) (\App\Models\Setting::get("marketplace_hero_slide_{$i}_subtitle") ?: ''));
        $buttonText = trim((string) (\App\Models\Setting::get("marketplace_hero_slide_{$i}_button_text") ?: ''));
        $buttonUrl = $resolveLinkUrl(\App\Models\Setting::get("marketplace_hero_slide_{$i}_button_url"));

        if ($image !== '' || $imageMobile !== '' || $title !== '' || $subtitle !== '' || $buttonText !== '' || $buttonUrl !== '') {
            $marketplaceHeroSlides[] = [
                'image' => $image,
                'image_mobile' => $imageMobile,
                'title' => $title,
                'subtitle' => $subtitle,
                'button_text' => $buttonText,
                'button_url' => $buttonUrl,
            ];
        }
    }
    if (count($marketplaceHeroSlides) === 0) {
        $marketplaceHeroEnabled = false;
    }

    // Marketplace: Exit offer (banner de saída)
    $marketplaceExitEnabled = (string) \App\Models\Setting::get('marketplace_exit_enabled', '0') === '1';
    $marketplaceExitDelaySecondsRaw = trim((string) (\App\Models\Setting::get('marketplace_exit_delay_seconds') ?: '15'));
    $marketplaceExitDelaySeconds = (int) ($marketplaceExitDelaySecondsRaw !== '' ? $marketplaceExitDelaySecondsRaw : 15);
    $marketplaceExitDelaySeconds = max(0, min(120, $marketplaceExitDelaySeconds));

    $marketplaceExitTitle = trim((string) (\App\Models\Setting::get('marketplace_exit_title') ?: 'Espere! Temos uma oferta pra você'));
    $marketplaceExitText = trim((string) (\App\Models\Setting::get('marketplace_exit_text') ?: 'Use um cupom e ganhe desconto agora mesmo.'));
    $marketplaceExitCoupon = trim((string) (\App\Models\Setting::get('marketplace_exit_coupon_code') ?: ''));
    $marketplaceExitButtonText = trim((string) (\App\Models\Setting::get('marketplace_exit_button_text') ?: 'Ver ofertas'));
    $marketplaceExitButtonUrl = $resolveLinkUrl(\App\Models\Setting::get('marketplace_exit_button_url') ?: '/marketplace');
    $marketplaceExitImage = $resolveAssetUrl(\App\Models\Setting::get('marketplace_exit_banner_image'));

    // Marketplace: Pop-up de eventos (para visitantes e compradores)
    $marketplaceEventsPopupEnabled = (string) \App\Models\Setting::get('marketplace_events_popup_enabled', '1') === '1';
    $marketplaceEventsPopupIntervalSecondsRaw = trim((string) (\App\Models\Setting::get('marketplace_events_popup_interval_seconds') ?: '60'));
    $marketplaceEventsPopupIntervalSeconds = (int) ($marketplaceEventsPopupIntervalSecondsRaw !== '' ? $marketplaceEventsPopupIntervalSecondsRaw : 60);
    $marketplaceEventsPopupIntervalSeconds = max(20, min(300, $marketplaceEventsPopupIntervalSeconds));
    $marketplaceEventsPopupIntervalMs = $marketplaceEventsPopupIntervalSeconds * 1000;
    $marketplaceEventsPopupMaxRaw = trim((string) (\App\Models\Setting::get('marketplace_events_popup_max_per_session') ?: '3'));
    $marketplaceEventsPopupMaxPerSession = (int) ($marketplaceEventsPopupMaxRaw !== '' ? $marketplaceEventsPopupMaxRaw : 3);
    $marketplaceEventsPopupMaxPerSession = max(0, min(10, $marketplaceEventsPopupMaxPerSession));
@endphp

@push('styles')
    <style>
        .mp-hscroll::-webkit-scrollbar { display: none; }
        .mp-hscroll { -ms-overflow-style: none; scrollbar-width: none; }

        /* Liquid Background Animation */
        @keyframes liquid-bg {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .liquid-bg-animate {
            background: linear-gradient(-45deg, #0f172a, #1e293b, #1e40af, #0f172a);
            background-size: 400% 400%;
            animation: liquid-bg 15s ease infinite;
        }

        /* Glassmorphism Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
        }

        /* Float Animation */
        @keyframes float-img {
            0% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-10px) scale(1.02); }
            100% { transform: translateY(0px) scale(1); }
        }
        .float-animate {
            animation: float-img 6s ease-in-out infinite;
        }
    </style>
@endpush

<div class="min-h-screen bg-slate-50 pt-24 pb-16 px-4 md:px-6 lg:px-10">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5 md:p-6">
            <div class="flex flex-col lg:flex-row gap-4 lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl btn-primary text-white flex items-center justify-center shadow-md">
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <div class="text-xl md:text-2xl font-black text-slate-900 leading-tight">Marketplace UNN</div>
                        <div class="text-xs md:text-sm text-slate-500 leading-tight">Produtos digitais de membros e criadores</div>
                    </div>
                </div>

                <form method="GET" action="{{ route('marketplace.index') }}" class="flex-1 max-w-3xl">
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar cursos, mentorias e eventos..."
                            class="w-full pl-11 pr-28 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:border-blue-500 focus:ring-blue-500">
                        <button type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 btn-primary text-white px-5 py-2 rounded-xl font-bold text-sm shadow-md">
                            Buscar
                        </button>
                    </div>
                    @if($q !== '')
                        <div class="mt-2 flex items-center justify-between gap-3 text-xs text-slate-500">
                            <span>{{ $totalResults }} resultado(s) para "{{ $q }}"</span>
                            <a href="{{ route('marketplace.index') }}" class="font-bold text-blue-700 hover:text-blue-800">Limpar busca</a>
                        </div>
                    @endif
                </form>

                <div class="flex items-center gap-2">
                    @if($canSell)
                        <a href="{{ route('panel.marketplace.index') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 font-bold text-slate-700 hover:bg-slate-50 transition">
                            <i class="fas fa-chart-line text-slate-400"></i> Painel
                        </a>
                        <a href="{{ route('panel.marketplace.sales') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 font-bold text-slate-700 hover:bg-slate-50 transition">
                            <i class="fas fa-receipt text-slate-400"></i> Vendas
                        </a>
                    @endif
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($chips as $chip)
                    <a href="{{ $chip['href'] }}"
                        class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition">
                        <i class="{{ $chip['icon'] }} text-slate-400"></i> {{ $chip['label'] }}
                    </a>
                @endforeach

                <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold text-white btn-primary">
                    <i class="fas fa-compass"></i> Explorar tudo
                </a>
            </div>

            @if(!$paymentsConfigured)
                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                    <div class="font-black">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Compras pagas indisponíveis no momento
                    </div>
                    <div class="text-sm text-amber-800">
                        Pix e cartão estão temporariamente indisponíveis. Itens gratuitos continuam liberados.
                    </div>

                    @if($isAdmin)
                        <div class="mt-2 text-xs text-amber-800">
                            Dica (admin): verifique as configurações em <span class="font-bold">Configurações &gt; Pagamentos</span>.
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="mt-6 -mx-4 md:mx-0">
            <div class="bg-white md:rounded-3xl md:border border-slate-100 shadow-sm overflow-hidden">
                    @if($marketplaceHeroEnabled)
                        <div class="relative" data-marketplace-hero data-animation="{{ $marketplaceHeroAnimation }}"
                            data-autoplay="{{ $marketplaceHeroAutoplay ? 1 : 0 }}" data-interval="{{ $marketplaceHeroIntervalMs }}">
                            @if($marketplaceHeroAnimation === 'fade')
                                <div class="relative min-h-[550px] md:min-h-[550px]">
                                    @foreach($marketplaceHeroSlides as $idx => $slide)
                                        <div class="mp-hero-slide absolute inset-0 transition-opacity duration-700 ease-out {{ $idx === 0 ? 'opacity-100' : 'opacity-0 pointer-events-none' }}"
                                            aria-hidden="{{ $idx === 0 ? 'false' : 'true' }}">
                                            <div class="relative h-full w-full overflow-hidden">
                                                @if(($slide['image'] ?? '') !== '' || ($slide['image_mobile'] ?? '') !== '')
                                                    <div class="absolute inset-0 liquid-bg-animate">
                                                        {{-- Environment Layer (Deep Blur) --}}
                                                        <div class="absolute inset-0 bg-cover bg-center blur-[100px] opacity-40 scale-150" 
                                                             style="background-image: url('{{ $slide['image'] }}')"></div>
                                                        
                                                        <div class="relative w-full h-full flex items-center justify-center p-6 md:p-12">
                                                            <picture class="relative z-10 w-full h-full max-h-[70%] md:max-h-[85%] flex items-center justify-center float-animate">
                                                                @if(($slide['image_mobile'] ?? '') !== '')
                                                                    <source media="(max-width: 767px)" srcset="{{ $slide['image_mobile'] }}">
                                                                @endif
                                                                <img src="{{ $slide['image'] }}" alt=""
                                                                    class="max-w-full max-h-full object-contain filter drop-shadow-[0_20px_50px_rgba(0,0,0,0.5)]">
                                                            </picture>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="absolute inset-0 opacity-60 pointer-events-none"
                                                        style="background: radial-gradient(900px circle at 20% 10%, rgba(31, 94, 219, 0.22) 0%, transparent 60%), radial-gradient(700px circle at 90% 20%, rgba(23, 127, 214, 0.16) 0%, transparent 55%);">
                                                    </div>
                                                @endif

                                                <div class="absolute inset-0 bg-slate-900/10 pointer-events-none"></div>

                                                <div class="absolute inset-0 p-6 md:p-12 flex items-center justify-center md:justify-start">
                                                    <div class="glass-card p-6 md:p-10 max-w-xl text-center md:text-left translate-y-8 md:translate-y-0 opacity-0 transition-all duration-700 delay-300 hero-content-card">
                                                        <div class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-[10px] font-black text-white uppercase tracking-widest mb-4"
                                                            style="background: linear-gradient(135deg, #3b82f6, #6366f1); border: 1px solid rgba(255,255,255,0.2);">
                                                            <i class="fas fa-bolt text-amber-300"></i> Especial pra você
                                                        </div>

                                                        @if(($slide['title'] ?? '') !== '')
                                                            <div class="text-3xl md:text-5xl font-black text-white leading-tight mb-4 text-balance">
                                                                {{ $slide['title'] }}
                                                            </div>
                                                        @endif

                                                        @if(($slide['subtitle'] ?? '') !== '')
                                                            <div class="text-slate-200 text-sm md:text-base opacity-90 mb-8 line-clamp-2 md:line-clamp-none">
                                                                {{ $slide['subtitle'] }}
                                                            </div>
                                                        @endif

                                                        @if(($slide['button_text'] ?? '') !== '' && ($slide['button_url'] ?? '') !== '')
                                                            <a href="{{ $slide['button_url'] }}"
                                                                class="inline-flex items-center justify-center gap-3 bg-white text-blue-700 hover:bg-blue-50 px-8 py-3.5 rounded-xl font-black shadow-2xl transition-all hover:scale-105 active:scale-95 group">
                                                                {{ $slide['button_text'] }}
                                                                <i class="fas fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i> 
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                @endforeach
                                </div>
                            @else
                                <div class="relative overflow-hidden min-h-[550px] md:min-h-[600px]">
                                    <div class="mp-hero-track flex transition-transform duration-700 ease-out will-change-transform h-full">
                                        @foreach($marketplaceHeroSlides as $idx => $slide)
                                            <div class="mp-hero-slide w-full shrink-0" aria-hidden="{{ $idx === 0 ? 'false' : 'true' }}">
                                                <div class="relative h-full w-full overflow-hidden">
                                                    @if(($slide['image'] ?? '') !== '' || ($slide['image_mobile'] ?? '') !== '')
                                                        <div class="absolute inset-0 liquid-bg-animate">
                                                            <div class="absolute inset-0 bg-cover bg-center blur-[100px] opacity-40 scale-150"
                                                                 style="background-image: url('{{ $slide['image'] }}')"></div>
                                                            <div class="relative w-full h-full flex items-center justify-center p-6 md:p-12">
                                                                <picture class="relative z-10 w-full h-full max-h-[70%] md:max-h-[85%] flex items-center justify-center float-animate">
                                                                    @if(($slide['image_mobile'] ?? '') !== '')
                                                                        <source media="(max-width: 767px)" srcset="{{ $slide['image_mobile'] }}">
                                                                    @endif
                                                                    <img src="{{ $slide['image'] }}" alt=""
                                                                        class="max-w-full max-h-full object-contain filter drop-shadow-[0_20px_50px_rgba(0,0,0,0.5)]">
                                                                </picture>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="absolute inset-0 opacity-60 pointer-events-none"
                                                            style="background: radial-gradient(900px circle at 20% 10%, rgba(31, 94, 219, 0.22) 0%, transparent 60%), radial-gradient(700px circle at 90% 20%, rgba(23, 127, 214, 0.16) 0%, transparent 55%);"></div>
                                                    @endif

                                                    <div class="absolute inset-0 bg-slate-900/10 pointer-events-none"></div>

                                                    <div class="absolute inset-0 p-6 md:p-12 flex items-center justify-center md:justify-start">
                                                        <div class="glass-card p-6 md:p-10 max-w-xl text-center md:text-left hero-content-card" style="opacity:0; transform:translateY(20px); transition: opacity 0.7s ease 0.3s, transform 0.7s ease 0.3s;">
                                                            <div class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-[10px] font-black text-white uppercase tracking-widest mb-4"
                                                                style="background: linear-gradient(135deg, #3b82f6, #6366f1); border: 1px solid rgba(255,255,255,0.2);">
                                                                <i class="fas fa-bolt text-amber-300"></i> Especial pra você
                                                            </div>

                                                            @if(($slide['title'] ?? '') !== '')
                                                                <div class="text-3xl md:text-5xl font-black text-white leading-tight mb-4">
                                                                    {{ $slide['title'] }}
                                                                </div>
                                                            @endif

                                                            @if(($slide['subtitle'] ?? '') !== '')
                                                                <div class="text-slate-200 text-sm md:text-base opacity-90 mb-8 line-clamp-2 md:line-clamp-none">
                                                                    {{ $slide['subtitle'] }}
                                                                </div>
                                                            @endif

                                                            @if(($slide['button_text'] ?? '') !== '' && ($slide['button_url'] ?? '') !== '')
                                                                <a href="{{ $slide['button_url'] }}"
                                                                    class="inline-flex items-center justify-center gap-3 bg-white text-blue-700 hover:bg-blue-50 px-8 py-3.5 rounded-xl font-black shadow-2xl transition-all hover:scale-105 active:scale-95 group">
                                                                    {{ $slide['button_text'] }}
                                                                    <i class="fas fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif


                            @if(count($marketplaceHeroSlides) > 1)
                                <button type="button" data-hero-prev
                                    class="hidden sm:inline-flex absolute left-6 top-1/2 -translate-y-1/2 w-12 h-12 items-center justify-center rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white hover:bg-white/40 shadow-2xl transition-all duration-300">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button type="button" data-hero-next
                                    class="hidden sm:inline-flex absolute right-6 top-1/2 -translate-y-1/2 w-12 h-12 items-center justify-center rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white hover:bg-white/40 shadow-2xl transition-all duration-300">
                                    <i class="fas fa-chevron-right"></i>
                                </button>

                                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 rounded-full bg-black/30 px-3 py-2 backdrop-blur">
                                    @foreach($marketplaceHeroSlides as $idx => $slide)
                                        <button type="button" data-hero-dot="{{ $idx }}"
                                            class="w-2.5 h-2.5 rounded-full {{ $idx === 0 ? 'bg-white' : 'bg-white/40' }}"
                                            aria-label="Ir para o slide {{ $idx + 1 }}"></button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="relative p-6 md:p-8">
                            <div class="absolute inset-0 opacity-60 pointer-events-none"
                                style="background: radial-gradient(900px circle at 20% 10%, rgba(31, 94, 219, 0.20) 0%, transparent 60%), radial-gradient(700px circle at 90% 20%, rgba(23, 127, 214, 0.14) 0%, transparent 55%);">
                            </div>
                            <div class="relative">
                                <div class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-black text-white"
                                    style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3));">
                                    <i class="fas fa-bolt"></i> Ofertas e novidades
                                </div>
                                <h1 class="mt-5 text-3xl sm:text-4xl font-black text-slate-900">
                                    Sua loja digital dentro da UNN
                                </h1>
                                <p class="mt-3 text-slate-600 text-base sm:text-lg max-w-2xl">
                                    Cursos, mentorias e eventos da comunidade para você comprar e acessar em um só lugar.
                                </p>

                                <div class="mt-6">
                                    <a href="#marketplace-categories"
                                        class="btn-primary text-white px-6 py-3 rounded-2xl font-black inline-flex items-center justify-center gap-2 shadow-md">
                                        <i class="fas fa-compass"></i> Explorar ofertas
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
            </div>
        </div>

        {{-- Top categorias (estilo loja) --}}
        <section id="marketplace-categories" class="relative mt-6 md:-mt-10 z-10">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 md:p-7">
                <div class="grid lg:grid-cols-12 gap-6 items-center">
                    <div class="lg:col-span-3">
                        <div class="text-sm font-black text-slate-500 uppercase tracking-wide">Top categorias</div>
                        <div class="mt-2 text-2xl font-black text-slate-900">Encontre o que você precisa</div>
                        <p class="mt-2 text-sm text-slate-600 mb-0">Navegue por cursos, mentorias e eventos da comunidade.</p>
                        <div class="mt-4">
                            <a href="#marketplace-courses" class="inline-flex items-center gap-2 text-sm font-black text-blue-700 hover:text-blue-800">
                                Ver produtos <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        </div>
                    </div>

                    <div class="lg:col-span-9">
                        <div class="relative" data-hscroll data-scroll-amount="420">
                            <button type="button" data-hscroll-left
                                class="hidden md:inline-flex absolute -left-4 top-1/2 -translate-y-1/2 w-10 h-10 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-700 shadow hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed"
                                aria-label="Voltar categorias">
                                <i class="fas fa-chevron-left text-sm"></i>
                            </button>

                            <div class="mp-hscroll flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-2 px-1" data-hscroll-container>
                                @foreach($storeCategories as $cat)
                                    <a href="{{ $cat['href'] }}"
                                        class="snap-start shrink-0 w-72 sm:w-80 rounded-2xl border border-slate-100 bg-slate-50 hover:bg-white hover:shadow-sm transition p-4 flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-blue-700 flex items-center justify-center">
                                            <i class="{{ $cat['icon'] }} text-lg"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-black text-slate-900 leading-tight truncate">{{ $cat['label'] }}</div>
                                            <div class="text-sm text-slate-500">{{ $cat['count'] }} item(ns)</div>
                                        </div>
                                        <div class="ml-auto text-slate-300">
                                            <i class="fas fa-arrow-right"></i>
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <button type="button" data-hscroll-right
                                class="hidden md:inline-flex absolute -right-4 top-1/2 -translate-y-1/2 w-10 h-10 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-700 shadow hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed"
                                aria-label="Avançar categorias">
                                <i class="fas fa-chevron-right text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Vantagens --}}
        <section class="mt-6">
            <div class="grid md:grid-cols-3 gap-4">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-slate-50 border border-slate-200 text-blue-700 flex items-center justify-center">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="font-black text-slate-900">Compra segura</div>
                    </div>
                    <div class="mt-3 text-sm text-slate-600">
                        Pagamento integrado para você comprar sem sair do site.
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-slate-50 border border-slate-200 text-blue-700 flex items-center justify-center">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="font-black text-slate-900">Acesso imediato</div>
                    </div>
                    <div class="mt-3 text-sm text-slate-600">
                        Após a confirmação, seu acesso aparece na sua conta.
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-slate-50 border border-slate-200 text-blue-700 flex items-center justify-center">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="font-black text-slate-900">Comunidade UNN</div>
                    </div>
                    <div class="mt-3 text-sm text-slate-600">
                        Conteúdos publicados por mentores, instrutores e membros.
                    </div>
                </div>
            </div>
        </section>

        {{-- Promoções relâmpago --}}
        @php
            $flashDeals = collect();

            foreach ($courses as $c) {
                if (!method_exists($c, 'isFlashSaleActive') || !$c->isFlashSaleActive() || !$c->flash_sale_ends_at) {
                    continue;
                }
                $flashDeals->push([
                    'type' => 'CURSO',
                    'icon' => 'fas fa-book-open',
                    'title' => (string) $c->title,
                    'url' => route('courses.show', $c->slug ?: $c->id),
                    'image_url' => $resolveAssetUrl($c->thumbnail ?? ''),
                    'original' => (float) ($c->price ?? 0),
                    'sale' => (float) ($c->effective_price ?? 0),
                    'ends_at_ms' => (int) $c->flash_sale_ends_at->timestamp * 1000,
                ]);
            }

            foreach ($mentorships as $m) {
                if (!method_exists($m, 'isFlashSaleActive') || !$m->isFlashSaleActive() || !$m->flash_sale_ends_at) {
                    continue;
                }
                $flashDeals->push([
                    'type' => 'MENTORIA',
                    'icon' => 'fas fa-user-tie',
                    'title' => (string) $m->title,
                    'url' => route('mentorships.show', $m),
                    'image_url' => $resolveAssetUrl($m->image ?? ''),
                    'original' => (float) ($m->price ?? 0),
                    'sale' => (float) ($m->effective_price ?? 0),
                    'ends_at_ms' => (int) $m->flash_sale_ends_at->timestamp * 1000,
                ]);
            }

            foreach ($events as $e) {
                if (!method_exists($e, 'isFlashSaleActive') || !$e->isFlashSaleActive() || !$e->flash_sale_ends_at) {
                    continue;
                }
                $regular = (float) ($e->current_price ?? $e->price ?? 0);
                $flashDeals->push([
                    'type' => 'EVENTO',
                    'icon' => 'fas fa-calendar-alt',
                    'title' => (string) $e->title,
                    'url' => route('events.show', $e),
                    'image_url' => $resolveAssetUrl($e->image ?? ''),
                    'original' => $regular,
                    'sale' => (float) ($e->effective_price ?? $regular),
                    'ends_at_ms' => (int) $e->flash_sale_ends_at->timestamp * 1000,
                ]);
            }

            $flashDeals = $flashDeals->sortBy('ends_at_ms')->values();
        @endphp

        @if($flashDeals->count() > 0)
            <section id="marketplace-flash-deals" class="mt-10">
                <div class="flex items-end justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900">
                            <i class="fas fa-bolt mr-2 text-slate-400"></i> Promoções relâmpago
                        </h2>
                        <p class="text-slate-600 mb-0">Descontos por tempo limitado.</p>
                    </div>
                </div>

                <div class="relative" data-hscroll data-scroll-amount="420">
                    <button type="button" data-hscroll-left
                        class="hidden md:inline-flex absolute -left-4 top-1/2 -translate-y-1/2 w-10 h-10 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-700 shadow hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed"
                        aria-label="Voltar promoções">
                        <i class="fas fa-chevron-left text-sm"></i>
                    </button>

                    <div class="mp-hscroll flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-2 px-1" data-hscroll-container>
                        @foreach($flashDeals->take(8) as $deal)
                            <div class="snap-start shrink-0 w-72 sm:w-80 group bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden" data-product-card>
                            <a href="{{ $deal['url'] }}" class="block">
                                <div class="aspect-[16/9] bg-slate-100 relative">
                                    @if(($deal['image_url'] ?? '') !== '')
                                        <img src="{{ $deal['image_url'] }}" alt="{{ $deal['title'] }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                                            <i class="fas fa-image text-4xl"></i>
                                        </div>
                                    @endif

                                    <div class="absolute top-3 left-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black text-white shadow"
                                        style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3));">
                                        {{ $deal['type'] }}
                                    </div>
                                </div>
                            </a>

                            <div class="p-4">
                                <div class="font-black text-slate-900 leading-snug line-clamp-2">
                                    {{ $deal['title'] }}
                                </div>

                                <div class="mt-2 flex items-center justify-between gap-2">
                                    <div class="text-lg font-black text-slate-900" data-price-current>
                                        {{ $deal['sale'] > 0 ? 'R$ ' . number_format($deal['sale'], 2, ',', '.') : 'Gratuito' }}
                                    </div>
                                    @if(($deal['original'] ?? 0) > ($deal['sale'] ?? 0))
                                        <div class="text-sm font-bold text-slate-400 line-through" data-price-original>
                                            R$ {{ number_format((float) $deal['original'], 2, ',', '.') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-slate-900 text-white px-3 py-1 text-xs font-black"
                                    data-flash-sale data-ends-at-ms="{{ (int) ($deal['ends_at_ms'] ?? 0) }}"
                                    data-original="{{ (float) ($deal['original'] ?? 0) }}"
                                    data-sale="{{ (float) ($deal['sale'] ?? 0) }}">
                                    <i class="{{ $deal['icon'] }}"></i>
                                    <span data-flash-countdown>00:00:00</span>
                                </div>
                            </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" data-hscroll-right
                        class="hidden md:inline-flex absolute -right-4 top-1/2 -translate-y-1/2 w-10 h-10 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-700 shadow hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed"
                        aria-label="Avançar promoções">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </button>
                </div>
            </section>
        @endif

        {{-- Cursos --}}
        <section id="marketplace-courses" class="mt-10">
            <div class="flex items-end justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900">
                        <i class="fas fa-book-open mr-2 text-slate-400"></i> Cursos
                    </h2>
                    <p class="text-slate-600 mb-0">Aprenda com conteúdos publicados por membros habilitados.</p>
                </div>
                <a href="{{ route('courses.index') }}" class="text-sm font-black text-blue-700 hover:text-blue-800">Ver todos</a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @forelse($courses as $course)
                    @php
                        $showParam = $course->slug ?: $course->id;
                        $sellerId = (int) ($course->user_id ?? 0);
                        $regularPrice = (float) ($course->price ?? 0);
                        $price = (float) ($course->effective_price ?? $regularPrice);
                        $flashActive = method_exists($course, 'isFlashSaleActive') ? (bool) $course->isFlashSaleActive() : false;
                        $flashEndsAtMs = ($flashActive && $course->flash_sale_ends_at) ? ((int) $course->flash_sale_ends_at->timestamp * 1000) : 0;
                        $thumb = trim((string) ($course->thumbnail ?? ''));
                        $thumbUrl = $resolveAssetUrl($thumb);
                        $hasAccess = $user && ($course instanceof \App\Models\Course) ? $user->hasCourseAccess($course) : false;
                        $buyEnabled = $canBuy && $paymentsConfigured && $price > 0 && $sellerCanSell($sellerId);
                        $sellerName = optional($course->creator)->name ?? 'Criador';
                        $badge = ($course->is_featured ?? false) ? 'DESTAQUE' : 'CURSO';
                        $shareCode = \App\Support\ShortLink::encodeProduct('course', (int) $course->id);
                        $shareUrl = $shareCode ? route('share.product', ['code' => $shareCode]) : '';
                    @endphp

                    <div class="group bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden" data-product-card>
                        <a href="{{ route('courses.show', $showParam) }}" class="block">
                            <div class="aspect-[16/9] bg-slate-100 relative">
                                @if($thumbUrl !== '')
                                    <img src="{{ $thumbUrl }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                                        <i class="fas fa-image text-4xl"></i>
                                    </div>
                                @endif

                                <div class="absolute top-3 left-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black text-white shadow"
                                    style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3));">
                                    {{ $badge }}
                                </div>

                                @if($shareUrl !== '')
                                    <button type="button"
                                        class="absolute top-3 right-3 inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/90 border border-slate-200 text-slate-700 hover:bg-white shadow"
                                        onclick="event.preventDefault(); event.stopPropagation(); navigator.clipboard.writeText(@json($shareUrl)); toastr.success('Link copiado!');">
                                        <i class="fas fa-link text-sm"></i>
                                    </button>
                                @endif
                            </div>

                            <div class="p-4">
                                <div class="font-black text-slate-900 leading-snug truncate">
                                    {{ $course->title }}
                                </div>
                                <div class="text-xs text-slate-500 truncate mt-1">
                                    Produzido e comercializado por: {{ $sellerName }}
                                </div>

                                @if(!empty($course->short_description))
                                    <div class="text-sm text-slate-600 mt-2">
                                        {{ \Illuminate\Support\Str::limit(strip_tags((string) $course->short_description), 90) }}
                                    </div>
                                @endif

                                <div class="mt-3 flex items-end justify-between gap-3">
                                    <div>
                                        <div class="text-xs text-slate-500">Preço</div>
                                        <div class="flex items-end gap-2">
                                            <div class="text-lg font-black text-slate-900" data-price-current>
                                                {{ $price > 0 ? 'R$ '.number_format($price, 2, ',', '.') : 'Gratuito' }}
                                            </div>
                                            @if($flashActive && $regularPrice > 0)
                                                <div class="text-xs text-slate-400 line-through" data-price-original>
                                                    {{ 'R$ '.number_format($regularPrice, 2, ',', '.') }}
                                                </div>
                                            @endif
                                        </div>

                                        @if($flashActive && $flashEndsAtMs > 0)
                                            <div class="mt-2 inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-black text-rose-800"
                                                data-flash-sale
                                                data-ends-at-ms="{{ $flashEndsAtMs }}"
                                                data-original="{{ number_format($regularPrice, 2, '.', '') }}"
                                                data-sale="{{ number_format($price, 2, '.', '') }}">
                                                <i class="fas fa-bolt"></i>
                                                <span>Promoção relâmpago</span>
                                                <span class="font-mono" data-flash-countdown>--:--:--</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>

                        <div class="px-4 pb-4 flex gap-2">
                            <a href="{{ route('courses.show', $showParam) }}"
                                class="flex-1 inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50 transition">
                                Ver
                            </a>

                            @if($hasAccess)
                                <a href="{{ route('courses.show', $showParam) }}"
                                    class="flex-1 inline-flex items-center justify-center rounded-2xl btn-primary px-4 py-2 text-sm font-black text-white shadow-md">
                                    Acessar
                                </a>
                            @elseif($buyEnabled)
                                <a href="{{ route('checkout.show', $course->id) }}"
                                    class="flex-1 inline-flex items-center justify-center rounded-2xl btn-primary px-4 py-2 text-sm font-black text-white shadow-md">
                                    Comprar
                                </a>
                            @else
                                <span class="flex-1 inline-flex items-center justify-center rounded-2xl bg-slate-100 px-4 py-2 text-sm font-black text-slate-400 cursor-not-allowed">
                                    Indisponível
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-3xl border border-slate-100 p-6 text-slate-600">
                        Nenhum curso encontrado no momento.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Mentorias --}}
        <section id="marketplace-mentorships" class="mt-12">
            <div class="flex items-end justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900">
                        <i class="fas fa-user-tie mr-2 text-slate-400"></i> Mentorias
                    </h2>
                    <p class="text-slate-600 mb-0">Atendimento e acompanhamento com mentores da comunidade.</p>
                </div>
                <a href="{{ route('mentorships.index') }}" class="text-sm font-black text-blue-700 hover:text-blue-800">Ver todos</a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @forelse($mentorships as $mentorship)
                    @php
                        $sellerId = (int) ($mentorship->mentor_id ?? 0);
                        $regularPrice = (float) ($mentorship->price ?? 0);
                        $price = (float) ($mentorship->effective_price ?? $regularPrice);
                        $flashActive = method_exists($mentorship, 'isFlashSaleActive') ? (bool) $mentorship->isFlashSaleActive() : false;
                        $flashEndsAtMs = ($flashActive && $mentorship->flash_sale_ends_at) ? ((int) $mentorship->flash_sale_ends_at->timestamp * 1000) : 0;
                        $buyEnabled = $canBuy && $paymentsConfigured && $price > 0 && $sellerCanSell($sellerId);
                        $mentorName = optional($mentorship->mentor)->name ?? 'Mentor';
                        $desc = \Illuminate\Support\Str::limit(strip_tags((string) ($mentorship->description ?? '')), 90);
                        $image = trim((string) ($mentorship->image ?? ''));
                        $imageUrl = $resolveAssetUrl($image);
                        $shareCode = \App\Support\ShortLink::encodeProduct('mentorship', (int) $mentorship->id);
                        $shareUrl = $shareCode ? route('share.product', ['code' => $shareCode]) : '';
                    @endphp

                    <div class="group bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden" data-product-card>
                        <a href="{{ route('mentorships.show', $mentorship) }}" class="block">
                            <div class="aspect-[16/9] bg-slate-100 relative">
                                @if($imageUrl !== '')
                                    <img src="{{ $imageUrl }}" alt="{{ $mentorship->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="absolute inset-0 opacity-80"
                                        style="background: radial-gradient(900px circle at 20% 10%, rgba(31, 94, 219, 0.18) 0%, transparent 60%), radial-gradient(700px circle at 90% 20%, rgba(23, 127, 214, 0.12) 0%, transparent 55%);">
                                    </div>
                                    <div class="relative w-full h-full flex items-center justify-center">
                                        <div class="w-16 h-16 rounded-2xl bg-white shadow flex items-center justify-center text-slate-400">
                                            <i class="fas fa-user-tie text-2xl"></i>
                                        </div>
                                    </div>
                                @endif

                                <div class="absolute top-3 left-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black text-white shadow"
                                    style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3));">
                                    MENTORIA
                                </div>

                                @if($shareUrl !== '')
                                    <button type="button"
                                        class="absolute top-3 right-3 inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/90 border border-slate-200 text-slate-700 hover:bg-white shadow"
                                        onclick="event.preventDefault(); event.stopPropagation(); navigator.clipboard.writeText(@json($shareUrl)); toastr.success('Link copiado!');">
                                        <i class="fas fa-link text-sm"></i>
                                    </button>
                                @endif
                            </div>

                            <div class="p-4">
                                <div class="font-black text-slate-900 leading-snug truncate">
                                    {{ $mentorship->title }}
                                </div>
                                <div class="text-xs text-slate-500 truncate mt-1">
                                    Produzido e comercializado por: {{ $mentorName }}
                                </div>

                                @if($desc !== '')
                                    <div class="text-sm text-slate-600 mt-2">
                                        {{ $desc }}
                                    </div>
                                @endif

                                <div class="mt-3 flex items-end justify-between gap-3">
                                    <div>
                                        <div class="text-xs text-slate-500">Preço</div>
                                        <div class="flex items-end gap-2">
                                            <div class="text-lg font-black text-slate-900" data-price-current>
                                                {{ $price > 0 ? 'R$ '.number_format($price, 2, ',', '.') : 'Gratuito' }}
                                            </div>
                                            @if($flashActive && $regularPrice > 0)
                                                <div class="text-xs text-slate-400 line-through" data-price-original>
                                                    {{ 'R$ '.number_format($regularPrice, 2, ',', '.') }}
                                                </div>
                                            @endif
                                        </div>

                                        @if($flashActive && $flashEndsAtMs > 0)
                                            <div class="mt-2 inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-black text-rose-800"
                                                data-flash-sale
                                                data-ends-at-ms="{{ $flashEndsAtMs }}"
                                                data-original="{{ number_format($regularPrice, 2, '.', '') }}"
                                                data-sale="{{ number_format($price, 2, '.', '') }}">
                                                <i class="fas fa-bolt"></i>
                                                <span>Promoção relâmpago</span>
                                                <span class="font-mono" data-flash-countdown>--:--:--</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>

                        <div class="px-4 pb-4 flex gap-2">
                            <a href="{{ route('mentorships.show', $mentorship) }}"
                                class="flex-1 inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50 transition">
                                Ver
                            </a>

                            @if($buyEnabled)
                                <a href="{{ route('mentorships.checkout.show', $mentorship) }}"
                                    class="flex-1 inline-flex items-center justify-center rounded-2xl btn-primary px-4 py-2 text-sm font-black text-white shadow-md">
                                    Comprar
                                </a>
                            @else
                                <span class="flex-1 inline-flex items-center justify-center rounded-2xl bg-slate-100 px-4 py-2 text-sm font-black text-slate-400 cursor-not-allowed">
                                    Indisponível
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-3xl border border-slate-100 p-6 text-slate-600">
                        Nenhuma mentoria encontrada no momento.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Eventos --}}
        <section id="marketplace-events" class="mt-12">
            <div class="flex items-end justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900">
                        <i class="fas fa-calendar-alt mr-2 text-slate-400"></i> Eventos
                    </h2>
                    <p class="text-slate-600 mb-0">Experiências, palestras e encontros digitais e presenciais.</p>
                </div>
                <a href="{{ route('events.index') }}" class="text-sm font-black text-blue-700 hover:text-blue-800">Ver todos</a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @forelse($events as $event)
                    @php
                        $sellerId = (int) ($event->user_id ?? 0);
                        $regularPrice = (float) ($event->current_price ?? $event->price ?? 0);
                        $price = (float) ($event->effective_price ?? $regularPrice);
                        $flashActive = method_exists($event, 'isFlashSaleActive') ? (bool) $event->isFlashSaleActive() : false;
                        $flashEndsAtMs = ($flashActive && $event->flash_sale_ends_at) ? ((int) $event->flash_sale_ends_at->timestamp * 1000) : 0;
                        $dateLabel = $event->start_at ? (is_string($event->start_at) ? \Carbon\Carbon::parse($event->start_at) : $event->start_at)->format('d/m/Y') : null;
                        $image = trim((string) ($event->image ?? ''));
                        $imageUrl = $resolveAssetUrl($image);
                        $buyEnabled = $price <= 0 ? true : ($canBuy && $paymentsConfigured && $sellerCanSell($sellerId));
                        $sellerName = optional($event->user)->name ?? 'Organizador';
                        $shareCode = \App\Support\ShortLink::encodeProduct('event', (int) $event->id);
                        $shareUrl = $shareCode ? route('share.product', ['code' => $shareCode]) : '';
                    @endphp

                    <div class="group bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden" data-product-card>
                        <a href="{{ route('events.show', $event) }}" class="block">
                            <div class="aspect-[16/9] bg-slate-100 relative">
                                @if($imageUrl !== '')
                                    <img src="{{ $imageUrl }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                                        <i class="fas fa-image text-4xl"></i>
                                    </div>
                                @endif

                                <div class="absolute top-3 left-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black text-white shadow"
                                    style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3));">
                                    EVENTO
                                </div>

                                @if($shareUrl !== '')
                                    <button type="button"
                                        class="absolute top-3 right-3 inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/90 border border-slate-200 text-slate-700 hover:bg-white shadow"
                                        onclick="event.preventDefault(); event.stopPropagation(); navigator.clipboard.writeText(@json($shareUrl)); toastr.success('Link copiado!');">
                                        <i class="fas fa-link text-sm"></i>
                                    </button>
                                @endif
                            </div>

                            <div class="p-4">
                                <div class="font-black text-slate-900 leading-snug truncate">
                                    {{ $event->title }}
                                </div>
                                <div class="text-xs text-slate-500 truncate mt-1">
                                    {{ $dateLabel ? ('Data: ' . $dateLabel) : ($event->location ?? 'Evento') }}
                                </div>
                                <div class="text-xs text-slate-500 truncate mt-1">
                                    Produzido e comercializado por: {{ $sellerName }}
                                </div>

                                <div class="mt-3 flex items-end justify-between gap-3">
                                    <div>
                                        <div class="text-xs text-slate-500">Preço</div>
                                        <div class="flex items-end gap-2">
                                            <div class="text-lg font-black text-slate-900" data-price-current>
                                                {{ $price > 0 ? 'R$ '.number_format($price, 2, ',', '.') : 'Gratuito' }}
                                            </div>
                                            @if($flashActive && $regularPrice > 0)
                                                <div class="text-xs text-slate-400 line-through" data-price-original>
                                                    {{ 'R$ '.number_format($regularPrice, 2, ',', '.') }}
                                                </div>
                                            @endif
                                        </div>

                                        @if($flashActive && $flashEndsAtMs > 0)
                                            <div class="mt-2 inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-black text-rose-800"
                                                data-flash-sale
                                                data-ends-at-ms="{{ $flashEndsAtMs }}"
                                                data-original="{{ number_format($regularPrice, 2, '.', '') }}"
                                                data-sale="{{ number_format($price, 2, '.', '') }}">
                                                <i class="fas fa-bolt"></i>
                                                <span>Promoção relâmpago</span>
                                                <span class="font-mono" data-flash-countdown>--:--:--</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>

                        <div class="px-4 pb-4 flex gap-2">
                            <a href="{{ route('events.show', $event) }}"
                                class="flex-1 inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50 transition">
                                Ver
                            </a>

                            @if($buyEnabled)
                                <a href="{{ route('events.checkout', $event) }}"
                                    class="flex-1 inline-flex items-center justify-center rounded-2xl btn-primary px-4 py-2 text-sm font-black text-white shadow-md">
                                    {{ $price > 0 ? 'Comprar' : 'Reservar' }}
                                </a>
                            @else
                                <span class="flex-1 inline-flex items-center justify-center rounded-2xl bg-slate-100 px-4 py-2 text-sm font-black text-slate-400 cursor-not-allowed">
                                    Indisponível
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-3xl border border-slate-100 p-6 text-slate-600">
                        Nenhum evento encontrado no momento.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Depoimentos (compradores) --}}
        @if($testimonials->count() > 0)
            <section id="marketplace-testimonials" class="mt-12">
                <div class="flex items-end justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900">
                            <i class="fas fa-comment-dots mr-2 text-slate-400"></i> Depoimentos
                        </h2>
                        <p class="text-slate-600 mb-0">Quem já comprou recomenda.</p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($testimonials->take(6) as $t)
                        @php
                            $rating = (int) ($t->rating ?? 0);
                            $rating = max(0, min(5, $rating));
                            $createdAt = $t->created_at ? (is_string($t->created_at) ? \Carbon\Carbon::parse($t->created_at) : $t->created_at)->format('d/m/Y') : null;
                            $featured = (bool) ($t->is_featured ?? false);
                        @endphp

                        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-black text-slate-900 truncate">
                                        {{ $t->author_name ?: 'Cliente' }}
                                    </div>
                                    @if(!empty($t->author_title))
                                        <div class="text-sm text-slate-500 truncate">{{ $t->author_title }}</div>
                                    @endif
                                </div>

                                <div class="shrink-0 flex items-center gap-1 text-amber-400" aria-label="Avaliação">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="{{ $i <= $rating ? 'fas' : 'far' }} fa-star text-sm"></i>
                                    @endfor
                                </div>
                            </div>

                            @if($featured)
                                <div class="mt-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black text-white shadow"
                                    style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3));">
                                    DESTAQUE
                                </div>
                            @endif

                            <div class="mt-3 text-sm text-slate-700 leading-relaxed">
                                {{ $t->content }}
                            </div>

                            @if($createdAt)
                                <div class="mt-4 text-xs text-slate-400">
                                    {{ $createdAt }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>

@if($marketplaceExitEnabled)
    <div id="mp-exit-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
        data-delay-ms="{{ $marketplaceExitDelaySeconds * 1000 }}">
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden border border-slate-100 relative">
            <button type="button" data-exit-close
                class="absolute top-3 right-3 w-10 h-10 inline-flex items-center justify-center rounded-full bg-white/90 border border-slate-200 text-slate-700 hover:bg-white shadow">
                <i class="fas fa-times"></i>
            </button>

            <div class="grid md:grid-cols-2">
                <div class="p-6 md:p-8">
                    <div class="text-sm font-black text-slate-500 uppercase tracking-wide">
                        Oferta exclusiva
                    </div>
                    <div class="mt-2 text-2xl md:text-3xl font-black text-slate-900 leading-tight">
                        {{ $marketplaceExitTitle }}
                    </div>
                    <div class="mt-3 text-slate-600">
                        {{ $marketplaceExitText }}
                    </div>

                    @if($marketplaceExitCoupon !== '')
                        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                            <div class="text-sm font-black text-emerald-900">Cupom</div>
                            <div class="mt-2 flex items-center gap-2">
                                <div class="flex-1 font-black text-emerald-900 bg-white rounded-xl border border-emerald-200 px-4 py-2">
                                    {{ $marketplaceExitCoupon }}
                                </div>
                                <button type="button" data-exit-copy data-code="{{ $marketplaceExitCoupon }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl btn-primary px-4 py-2 text-sm font-black text-white shadow-md">
                                    <i class="fas fa-copy"></i> Copiar
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 flex flex-col sm:flex-row gap-2">
                        @if($marketplaceExitButtonUrl !== '')
                            <a href="{{ $marketplaceExitButtonUrl }}"
                                class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl btn-primary px-6 py-3 text-sm font-black text-white shadow-md">
                                <i class="fas fa-tags"></i> {{ $marketplaceExitButtonText !== '' ? $marketplaceExitButtonText : 'Ver ofertas' }}
                            </a>
                        @endif
                        <button type="button" data-exit-close
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-black text-slate-700 hover:bg-slate-50 transition">
                            Agora não
                        </button>
                    </div>
                </div>

                <div class="bg-slate-50 border-t md:border-t-0 md:border-l border-slate-100">
                    @if($marketplaceExitImage !== '')
                        <img src="{{ $marketplaceExitImage }}" alt="Oferta" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full min-h-[240px] flex items-center justify-center text-slate-300">
                            <i class="fas fa-gift text-6xl"></i>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@php
    $eventsPopupItems = $events
        ->take(10)
        ->map(function ($event) use ($resolveAssetUrl) {
            $date = $event->start_at
                ? (is_string($event->start_at) ? \Carbon\Carbon::parse($event->start_at) : $event->start_at)->format('d/m/Y')
                : null;

            return [
                'id' => (int) $event->id,
                'title' => (string) $event->title,
                'date' => $date,
                'url' => route('events.show', $event),
                'image_url' => $resolveAssetUrl($event->image ?? ''),
            ];
        })
        ->values();
@endphp

@push('scripts')
    <script>
        (function () {
            const mpEventsPopupEnabled = @json((bool) $marketplaceEventsPopupEnabled);
            const mpEventsPopupIntervalMs = @json((int) $marketplaceEventsPopupIntervalMs);
            const mpEventsPopupMaxPerSession = @json((int) $marketplaceEventsPopupMaxPerSession);
            const mpUpcomingEvents = @json($eventsPopupItems);

            function initMarketplaceHero() {
                const hero = document.querySelector('[data-marketplace-hero]');
                if (!hero) return;

                const slides = Array.from(hero.querySelectorAll('.mp-hero-slide'));
                if (!slides.length) return;

                const track = hero.querySelector('.mp-hero-track');
                const dots = Array.from(hero.querySelectorAll('[data-hero-dot]'));
                const prevBtn = hero.querySelector('[data-hero-prev]');
                const nextBtn = hero.querySelector('[data-hero-next]');

                const animation = (hero.dataset.animation || 'slide').toLowerCase();
                const autoplay = hero.dataset.autoplay === '1';
                const intervalMs = Math.max(1500, parseInt(hero.dataset.interval || '6000', 10) || 6000);

                let index = 0;
                let timer = null;

                function setDotActive(activeIndex) {
                    dots.forEach((dot) => {
                        const dotIndex = parseInt(dot.dataset.heroDot || '0', 10) || 0;
                        const active = dotIndex === activeIndex;
                        dot.classList.toggle('bg-white', active);
                        dot.classList.toggle('bg-white/40', !active);
                        dot.setAttribute('aria-current', active ? 'true' : 'false');
                    });
                }

                function applyIndex(nextIndex) {
                    index = ((nextIndex % slides.length) + slides.length) % slides.length;

                    if (animation === 'fade') {
                        slides.forEach((slide, i) => {
                            const active = i === index;
                            slide.classList.toggle('opacity-0', !active);
                            slide.classList.toggle('opacity-100', active);
                            slide.classList.toggle('pointer-events-none', !active);
                            slide.setAttribute('aria-hidden', active ? 'false' : 'true');
                        });
                    } else if (track) {
                        track.style.transform = `translateX(-${index * 100}%)`;
                        slides.forEach((slide, i) => slide.setAttribute('aria-hidden', i === index ? 'false' : 'true'));
                    }

                    setDotActive(index);
                }

                function stop() {
                    if (!timer) return;
                    clearInterval(timer);
                    timer = null;
                }

                function start() {
                    if (!autoplay || slides.length < 2) return;
                    stop();
                    timer = setInterval(() => applyIndex(index + 1), intervalMs);
                }

                prevBtn && prevBtn.addEventListener('click', () => {
                    applyIndex(index - 1);
                    start();
                });
                nextBtn && nextBtn.addEventListener('click', () => {
                    applyIndex(index + 1);
                    start();
                });

                dots.forEach((dot) => {
                    dot.addEventListener('click', () => {
                        const dotIndex = parseInt(dot.dataset.heroDot || '0', 10) || 0;
                        applyIndex(dotIndex);
                        start();
                    });
                });

                hero.addEventListener('mouseenter', stop);
                hero.addEventListener('mouseleave', start);
                hero.addEventListener('focusin', stop);
                hero.addEventListener('focusout', start);

                applyIndex(0);
                start();

                // Animate content cards on slide change
                function animateHeroContentCards(container) {
                    if (!container) return;
                    const cards = container.querySelectorAll('.hero-content-card');
                    cards.forEach(card => {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 300);
                    });
                }

                // Initial animation for active slide
                const firstSlide = hero.querySelector('[data-hero-slide="0"], .mp-hero-slide, [aria-hidden="false"]');
                if (firstSlide) animateHeroContentCards(firstSlide);
            }

            function initMarketplaceExitOffer() {
                const modal = document.getElementById('mp-exit-modal');
                if (!modal) return;

                const delayMs = Math.max(0, parseInt(modal.dataset.delayMs || '0', 10) || 0);
                const readyAt = Date.now() + delayMs;
                const sessionKey = 'unn_marketplace_exit_offer_shown_v1';

                function isShown() {
                    try {
                        return sessionStorage.getItem(sessionKey) === '1';
                    } catch (e) {
                        return false;
                    }
                }

                function markShown() {
                    try {
                        sessionStorage.setItem(sessionKey, '1');
                    } catch (e) { /* ignore */ }
                }

                function open() {
                    if (isShown()) return;
                    if (Date.now() < readyAt) return;
                    modal.classList.remove('hidden', 'pointer-events-none');
                    modal.classList.add('flex', 'pointer-events-auto');
                    document.documentElement.classList.add('overflow-hidden');
                    markShown();
                }

                function close() {
                    modal.classList.add('hidden', 'pointer-events-none');
                    modal.classList.remove('flex', 'pointer-events-auto');
                    document.documentElement.classList.remove('overflow-hidden');
                }

                modal.querySelectorAll('[data-exit-close]').forEach((el) => el.addEventListener('click', close));
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) close();
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') close();
                });

                const copyBtn = modal.querySelector('[data-exit-copy]');
                if (copyBtn) {
                    copyBtn.addEventListener('click', () => {
                        const code = copyBtn.dataset.code || '';
                        if (!code) return;
                        navigator.clipboard.writeText(code).then(() => {
                            if (window.toastr) toastr.success('Cupom copiado!');
                        });
                    });
                }

                // Exit intent (desktop): mouse leaving the top area.
                document.addEventListener('mouseout', (event) => {
                    if (event.relatedTarget !== null) return;
                    if (event.clientY > 0) return;
                    open();
                });

                // Fallback: when user hides the tab/app.
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) open();
                });
            }

            function initHorizontalScrollers() {
                const wrappers = Array.from(document.querySelectorAll('[data-hscroll]'));
                if (!wrappers.length) return;

                wrappers.forEach((wrapper) => {
                    const container = wrapper.querySelector('[data-hscroll-container]');
                    if (!container) return;

                    const leftBtn = wrapper.querySelector('[data-hscroll-left]');
                    const rightBtn = wrapper.querySelector('[data-hscroll-right]');
                    const scrollAmount = Math.max(120, parseInt(wrapper.dataset.scrollAmount || '360', 10) || 360);

                    function update() {
                        const maxScrollLeft = Math.max(0, container.scrollWidth - container.clientWidth);
                        if (leftBtn) leftBtn.disabled = container.scrollLeft <= 0;
                        if (rightBtn) rightBtn.disabled = container.scrollLeft >= (maxScrollLeft - 1);
                    }

                    if (leftBtn) {
                        leftBtn.addEventListener('click', () => {
                            container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                        });
                    }

                    if (rightBtn) {
                        rightBtn.addEventListener('click', () => {
                            container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                        });
                    }

                    let raf = null;
                    container.addEventListener('scroll', () => {
                        if (raf) cancelAnimationFrame(raf);
                        raf = requestAnimationFrame(update);
                    });
                    window.addEventListener('resize', update);
                    update();
                });
            }

            function initFlashSales() {
                const badges = Array.from(document.querySelectorAll('[data-flash-sale]'));
                if (!badges.length) return;

                const formatter = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

                function pad(value) {
                    return String(value).padStart(2, '0');
                }

                function formatCountdown(ms) {
                    const totalSeconds = Math.max(0, Math.floor(ms / 1000));
                    const days = Math.floor(totalSeconds / 86400);
                    const hours = Math.floor((totalSeconds % 86400) / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    const clock = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
                    return days > 0 ? `${days}d ${clock}` : clock;
                }

                let timer = null;

                function tick() {
                    const now = Date.now();
                    let alive = 0;

                    badges.forEach((badge) => {
                        if (!badge.isConnected) return;

                        const endsAtMs = parseInt(badge.dataset.endsAtMs || '0', 10) || 0;
                        if (!endsAtMs) return;

                        const remaining = endsAtMs - now;
                        const card = badge.closest('[data-product-card]');
                        const priceCurrent = card ? card.querySelector('[data-price-current]') : null;
                        const priceOriginal = card ? card.querySelector('[data-price-original]') : null;

                        const original = parseFloat(badge.dataset.original || '0') || 0;
                        const sale = parseFloat(badge.dataset.sale || '0') || 0;

                        if (remaining <= 0) {
                            if (priceCurrent) {
                                priceCurrent.textContent = original > 0 ? formatter.format(original) : 'Gratuito';
                            }
                            if (priceOriginal) {
                                priceOriginal.remove();
                            }
                            badge.remove();
                            return;
                        }

                        alive++;

                        const countdown = badge.querySelector('[data-flash-countdown]');
                        if (countdown) {
                            countdown.textContent = formatCountdown(remaining);
                        }
                        if (priceCurrent) {
                            priceCurrent.textContent = sale > 0 ? formatter.format(sale) : 'Gratuito';
                        }
                    });

                    if (alive === 0 && timer) {
                        clearInterval(timer);
                        timer = null;
                    }
                }

                tick();
                timer = setInterval(tick, 1000);
            }

            function initMarketplaceEventsPopup() {
                if (!mpEventsPopupEnabled) return;
                if (!mpEventsPopupMaxPerSession) return;
                if (!Array.isArray(mpUpcomingEvents) || mpUpcomingEvents.length === 0) return;
                if (!window.Swal || typeof window.Swal.fire !== 'function') return;

                const sessionKey = 'unn_marketplace_events_popup_v1';

                function escapeHtml(value) {
                    return String(value || '').replace(/[&<>"']/g, (char) => {
                        switch (char) {
                            case '&':
                                return '&amp;';
                            case '<':
                                return '&lt;';
                            case '>':
                                return '&gt;';
                            case '"':
                                return '&quot;';
                            case "'":
                                return '&#039;';
                            default:
                                return char;
                        }
                    });
                }

                function loadState() {
                    try {
                        const raw = sessionStorage.getItem(sessionKey);
                        if (!raw) return { shownIds: [], shownCount: 0 };
                        const parsed = JSON.parse(raw);
                        return {
                            shownIds: Array.isArray(parsed.shownIds) ? parsed.shownIds : [],
                            shownCount: parseInt(parsed.shownCount || 0, 10) || 0,
                        };
                    } catch (e) {
                        return { shownIds: [], shownCount: 0 };
                    }
                }

                function saveState(state) {
                    try {
                        sessionStorage.setItem(sessionKey, JSON.stringify(state));
                    } catch (e) {
                        // ignore
                    }
                }

                const toast = window.Swal.mixin({
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: true,
                    confirmButtonText: 'Ver',
                    showCloseButton: true,
                    timer: 12000,
                    timerProgressBar: true,
                    didOpen: (el) => {
                        el.addEventListener('mouseenter', window.Swal.stopTimer);
                        el.addEventListener('mouseleave', window.Swal.resumeTimer);
                    },
                });

                let state = loadState();
                let shownIds = new Set((state.shownIds || []).map((id) => parseInt(id, 10) || 0).filter(Boolean));
                let shownCount = parseInt(state.shownCount || 0, 10) || 0;
                let timer = null;

                function pickEvent() {
                    const available = mpUpcomingEvents.filter((ev) => ev && ev.id && !shownIds.has(ev.id));
                    if (available.length === 0) return null;
                    return available[Math.floor(Math.random() * available.length)];
                }

                function showPopup() {
                    if (document.hidden) return;
                    if (typeof window.Swal.isVisible === 'function' && window.Swal.isVisible()) return;
                    if (shownCount >= mpEventsPopupMaxPerSession) {
                        if (timer) clearInterval(timer);
                        timer = null;
                        return;
                    }

                    const ev = pickEvent();
                    if (!ev) {
                        if (timer) clearInterval(timer);
                        timer = null;
                        return;
                    }

                    const title = escapeHtml(ev.title || 'Novo evento');
                    const date = escapeHtml(ev.date || '');
                    const image = String(ev.image_url || '').trim();

                    shownIds.add(ev.id);
                    shownCount += 1;

                    state = { shownIds: Array.from(shownIds), shownCount };
                    saveState(state);

                    const html = `
                        <div class="flex gap-3 items-center">
                            ${image ? `<img src="${escapeHtml(image)}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:12px;border:1px solid rgba(148,163,184,.35)" />` : ''}
                            <div style="min-width:0">
                                <div style="font-weight:900;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px">${title}</div>
                                ${date ? `<div style="margin-top:2px;font-size:12px;opacity:.85">Data: ${date}</div>` : ''}
                            </div>
                        </div>
                    `;

                    toast.fire({
                        icon: 'info',
                        title: 'Novo evento na comunidade',
                        html,
                    }).then((result) => {
                        if (result.isConfirmed && ev.url) {
                            window.location.href = ev.url;
                        }
                    });
                }

                setTimeout(() => {
                    showPopup();
                    if (mpEventsPopupIntervalMs > 0) {
                        timer = setInterval(showPopup, mpEventsPopupIntervalMs);
                    }
                }, 12000);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    initMarketplaceHero();
                    initHorizontalScrollers();
                    initMarketplaceExitOffer();
                    initFlashSales();
                    initMarketplaceEventsPopup();
                });
            } else {
                initMarketplaceHero();
                initHorizontalScrollers();
                initMarketplaceExitOffer();
                initFlashSales();
                initMarketplaceEventsPopup();
            }
        })();
    </script>
@endpush
@endsection
