@extends('layouts.app')

@php
    $primaryColor = $store->primary_color ?: '#1F5EDB';
    $accentColor = $store->accent_color ?: '#0F172A';
    $storeBioHtml = \App\Support\RichText::toHtml($store->bio);
    $heroProduct = $products->firstWhere('is_featured', true) ?? $products->first();
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
        ['label' => 'Site oficial', 'url' => $store->website_url, 'icon' => 'fas fa-globe'],
        ['label' => 'WhatsApp', 'url' => $whatsappUrl, 'icon' => 'fab fa-whatsapp'],
        ['label' => 'E-mail', 'url' => filled($store->support_email) ? 'mailto:' . $store->support_email : null, 'icon' => 'fas fa-envelope'],
    ])->filter(fn ($item) => filled($item['url']))->values();

    $offerStats = [
        ['label' => 'Produtos', 'value' => $products->count(), 'icon' => 'fas fa-box-open'],
        ['label' => 'Cursos', 'value' => $courses->count(), 'icon' => 'fas fa-graduation-cap'],
        ['label' => 'Mentorias', 'value' => $mentorships->count(), 'icon' => 'fas fa-user-tie'],
        ['label' => 'Eventos', 'value' => $events->count(), 'icon' => 'fas fa-calendar-days'],
    ];

    $hasAnyCatalog = $products->isNotEmpty() || $courses->isNotEmpty() || $mentorships->isNotEmpty() || $events->isNotEmpty();
@endphp

@push('styles')
    <style>
        .storefront-shell {
            background:
                radial-gradient(circle at top left, rgba(31, 94, 219, 0.16), transparent 28%),
                radial-gradient(circle at 100% 0%, rgba(15, 23, 42, 0.12), transparent 30%),
                linear-gradient(180deg, #f8fbff 0%, #eef4ff 24%, #ffffff 58%);
        }

        .storefront-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(135deg, rgba(2, 6, 23, 0.78) 0%, rgba(15, 23, 42, 0.54) 48%, rgba(30, 41, 59, 0.3) 100%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.04), transparent 32%);
            pointer-events: none;
        }

        .store-glass-card {
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 0 24px 60px rgba(2, 6, 23, 0.22);
            backdrop-filter: blur(18px);
        }

        .store-surface-card {
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
        }

        .store-richtext {
            color: #dbe7ff;
        }

        .store-richtext p,
        .store-richtext ul,
        .store-richtext ol,
        .store-richtext blockquote,
        .store-section-richtext p,
        .store-section-richtext ul,
        .store-section-richtext ol,
        .store-section-richtext blockquote {
            margin-top: 0.75rem;
            margin-bottom: 0;
            line-height: 1.85;
        }

        .store-richtext a {
            color: #ffffff;
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .store-richtext strong,
        .store-richtext b,
        .store-richtext h1,
        .store-richtext h2,
        .store-richtext h3,
        .store-richtext h4 {
            color: #ffffff;
        }

        .store-richtext ul,
        .store-richtext ol,
        .store-section-richtext ul,
        .store-section-richtext ol {
            padding-left: 1.2rem;
        }

        .store-section-richtext {
            color: #475569;
        }

        .store-section-richtext a {
            color: #1d4ed8;
            font-weight: 700;
        }

        .store-line-clamp-3,
        .store-line-clamp-4 {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .store-line-clamp-3 {
            -webkit-line-clamp: 3;
        }

        .store-line-clamp-4 {
            -webkit-line-clamp: 4;
        }

        .store-product-card,
        .store-content-card,
        .store-info-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        .store-product-card:hover,
        .store-content-card:hover,
        .store-info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.1);
            border-color: rgba(31, 94, 219, 0.18);
        }

        .store-anchor-chip {
            border: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.05);
            backdrop-filter: blur(14px);
        }

        .store-anchor-chip:hover {
            border-color: rgba(31, 94, 219, 0.28);
            color: #1d4ed8;
            background: rgba(255, 255, 255, 0.98);
        }
    </style>
@endpush

@section('title', ($store->brand_name ?: 'Loja') . ' - UNN')

@section('content')
    <div class="storefront-shell min-h-screen pt-24 pb-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <section class="storefront-hero relative overflow-hidden rounded-[2.75rem] border border-slate-200/70 bg-slate-950 shadow-[0_35px_100px_-35px_rgba(15,23,42,0.55)]" style="background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $accentColor }} 88%);">
                @if($store->banner_url)
                    <img src="{{ $store->banner_url }}" alt="{{ $store->brand_name }}" class="absolute inset-0 h-full w-full object-cover opacity-35">
                @endif

                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.24),transparent_34%)] pointer-events-none"></div>
                <div class="absolute -left-20 top-24 h-56 w-56 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-cyan-300/10 blur-3xl pointer-events-none"></div>

                <div class="relative z-10 p-6 md:p-8 xl:p-10">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-[0.28em] text-white/80">
                                <i class="fas fa-gem text-[11px]"></i> Loja premium
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-slate-950/25 px-4 py-2 text-xs font-bold text-white/70">
                                <i class="fas fa-link text-[11px]"></i> /loja/{{ $store->slug }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('seller-products.cart.show') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/15">
                                <i class="fas fa-cart-shopping text-white/75"></i> Carrinho
                            </a>
                            @if($store->website_url)
                                <a href="{{ $store->website_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-black text-slate-950 shadow-lg shadow-slate-950/20 transition hover:brightness-110">
                                    <i class="fas fa-globe text-[13px]" style="color: {{ $primaryColor }};"></i> Visitar site
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="mt-8 grid gap-8 xl:grid-cols-[1.2fr,0.8fr] xl:items-start">
                        <div>
                            <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                                <div class="h-28 w-28 shrink-0 overflow-hidden rounded-[2rem] border border-white/15 bg-white/95 shadow-2xl shadow-slate-950/20">
                                    @if($store->logo_url)
                                        <img src="{{ $store->logo_url }}" alt="{{ $store->brand_name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-4xl text-slate-300">
                                            <i class="fas fa-store"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-black uppercase tracking-[0.3em] text-white/55">Anunciado e vendido por</p>
                                    <h1 class="mt-3 text-4xl font-black tracking-tight text-white md:text-5xl">{{ $store->brand_name }}</h1>
                                    @if($store->tagline)
                                        <p class="mt-4 max-w-3xl text-lg font-semibold text-white/80 md:text-xl">{{ $store->tagline }}</p>
                                    @endif
                                </div>
                            </div>

                            @if($storeBioHtml)
                                <div class="store-richtext mt-8 max-w-4xl text-sm md:text-base">{!! $storeBioHtml !!}</div>
                            @else
                                <p class="mt-8 max-w-3xl text-sm leading-8 text-white/72 md:text-base">Conheca a vitrine oficial deste vendedor dentro do ecossistema UNN, com produtos proprios, conteudos e ofertas publicadas diretamente na plataforma.</p>
                            @endif

                            @if($socialLinks->isNotEmpty())
                                <div class="mt-8 flex flex-wrap gap-3">
                                    @foreach($socialLinks as $link)
                                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl border border-white/12 bg-white/10 px-4 py-3 text-sm font-bold text-white/85 transition hover:bg-white/15">
                                            <i class="{{ $link['icon'] }} text-white/65"></i> {{ $link['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                @foreach($offerStats as $stat)
                                    <div class="store-glass-card rounded-[1.75rem] p-4 text-white">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-[11px] font-black uppercase tracking-[0.22em] text-white/55">{{ $stat['label'] }}</p>
                                                <p class="mt-2 text-3xl font-black">{{ $stat['value'] }}</p>
                                            </div>
                                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-lg text-white/75">
                                                <i class="{{ $stat['icon'] }}"></i>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="space-y-4">
                            @if($heroProduct)
                                <div class="store-glass-card overflow-hidden rounded-[2rem] text-white">
                                    <div class="aspect-[16/10] overflow-hidden bg-slate-950/20">
                                        @if($heroProduct->cover_url)
                                            <img src="{{ $heroProduct->cover_url }}" alt="{{ $heroProduct->title }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-5xl text-white/30">
                                                <i class="fas fa-box-open"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-5">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.22em] text-white/75">
                                                <i class="fas fa-star text-[10px]"></i> Produto destaque
                                            </span>
                                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-950/25 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.22em] text-white/65">{{ strtoupper($heroProduct->type) }}</span>
                                        </div>
                                        <h2 class="mt-4 text-2xl font-black">{{ $heroProduct->title }}</h2>
                                        <p class="store-line-clamp-3 mt-3 text-sm leading-7 text-white/72">{{ $heroProduct->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $heroProduct->description), 150) }}</p>
                                        <div class="mt-5 flex items-end justify-between gap-4">
                                            <div>
                                                <p class="text-[11px] font-black uppercase tracking-[0.22em] text-white/55">Preco atual</p>
                                                <p class="mt-2 text-3xl font-black">R$ {{ number_format((float) $heroProduct->effective_price, 2, ',', '.') }}</p>
                                            </div>
                                            <a href="{{ route('seller-stores.products.show', [$store->slug, $heroProduct->slug]) }}" class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-3 text-sm font-black text-slate-950 transition hover:brightness-110">
                                                Ver produto <i class="fas fa-arrow-right text-[11px]" style="color: {{ $primaryColor }};"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="store-glass-card rounded-[2rem] p-5 text-white">
                                <p class="text-[11px] font-black uppercase tracking-[0.24em] text-white/55">Experiencia de compra</p>
                                <div class="mt-4 space-y-4 text-sm text-white/74">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white/75"><i class="fas fa-shield-halved"></i></div>
                                        <div>
                                            <p class="font-black text-white">Pagamento integrado</p>
                                            <p class="mt-1 leading-7">Checkout alinhado ao marketplace, com split e registro centralizado dos pedidos.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white/75"><i class="fas fa-truck-fast"></i></div>
                                        <div>
                                            <p class="font-black text-white">Frete e entrega</p>
                                            <p class="mt-1 leading-7">Produtos fisicos calculam frete no checkout. Itens digitais liberam acesso apos confirmacao do pagamento.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white/75"><i class="fas fa-headset"></i></div>
                                        <div>
                                            <p class="font-black text-white">Atendimento direto da marca</p>
                                            <p class="mt-1 leading-7">Os canais oficiais da loja ficam disponiveis para relacionamento e suporte ao cliente.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            @if($hasAnyCatalog)
                <section class="mt-8 flex flex-wrap gap-3">
                    @if($products->isNotEmpty())
                        <a href="#produtos" class="store-anchor-chip inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 transition">
                            <i class="fas fa-box-open text-slate-400"></i> Produtos
                        </a>
                    @endif
                    @if($courses->isNotEmpty())
                        <a href="#cursos" class="store-anchor-chip inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 transition">
                            <i class="fas fa-graduation-cap text-slate-400"></i> Cursos
                        </a>
                    @endif
                    @if($mentorships->isNotEmpty())
                        <a href="#mentorias" class="store-anchor-chip inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 transition">
                            <i class="fas fa-user-tie text-slate-400"></i> Mentorias
                        </a>
                    @endif
                    @if($events->isNotEmpty())
                        <a href="#eventos" class="store-anchor-chip inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 transition">
                            <i class="fas fa-calendar-days text-slate-400"></i> Eventos
                        </a>
                    @endif
                </section>
            @endif

            @if($products->isNotEmpty())
                <section id="produtos" class="mt-10">
                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Catalogo premium</p>
                            <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900 md:text-[2.15rem]">Produtos proprios da marca</h2>
                            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600 md:text-base">Uma vitrine pensada para vender de verdade, com compra direta, identidade da marca e experiencia de checkout integrada ao marketplace.</p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('seller-products.cart.show') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700">
                                <i class="fas fa-cart-shopping text-slate-400"></i> Ver carrinho
                            </a>
                            @if($heroProduct)
                                <a href="{{ route('seller-stores.products.show', [$store->slug, $heroProduct->slug]) }}" class="inline-flex items-center gap-2 rounded-2xl px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 transition hover:brightness-110" style="background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $accentColor }} 100%);">
                                    Destaque da semana <i class="fas fa-arrow-right text-[11px]"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($products as $product)
                            <article class="store-product-card store-surface-card group flex h-full flex-col overflow-hidden rounded-[2rem]">
                                <div class="relative">
                                    <a href="{{ route('seller-stores.products.show', [$store->slug, $product->slug]) }}" class="block aspect-[16/11] overflow-hidden bg-slate-100">
                                        @if($product->cover_url)
                                            <img src="{{ $product->cover_url }}" alt="{{ $product->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-5xl text-slate-300">
                                                <i class="fas fa-box-open"></i>
                                            </div>
                                        @endif
                                    </a>

                                    <div class="pointer-events-none absolute inset-x-0 top-0 flex items-start justify-between gap-2 p-4">
                                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-950/72 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.22em] text-white">
                                            <i class="fas {{ $product->isPhysical() ? 'fa-truck-fast' : 'fa-bolt' }} text-[10px]"></i>
                                            {{ $product->isPhysical() ? 'Fisico' : 'Digital' }}
                                        </span>
                                        @if($product->is_featured)
                                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.22em] text-white shadow-lg" style="background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $accentColor }} 100%);">
                                                <i class="fas fa-star text-[10px]"></i> Destaque
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-1 flex-col p-5">
                                    <div class="flex items-center justify-between gap-4">
                                        <p class="text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Anunciado e vendido por</p>
                                        @if($product->sku)
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-500">SKU {{ $product->sku }}</span>
                                        @endif
                                    </div>

                                    <p class="mt-2 text-sm font-bold text-slate-600">{{ $store->brand_name }}</p>
                                    <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-900">{{ $product->title }}</h3>
                                    <p class="store-line-clamp-4 mt-3 text-sm leading-7 text-slate-600">{{ $product->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 140) }}</p>

                                    <div class="mt-5 grid gap-3 rounded-[1.5rem] bg-slate-50/90 p-4 text-sm text-slate-600">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="font-semibold text-slate-500">Entrega</span>
                                            <span class="font-bold text-slate-900">{{ $product->isPhysical() ? 'Frete calculado no checkout' : 'Liberacao digital apos pagamento' }}</span>
                                        </div>
                                        @if($product->isPhysical())
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="font-semibold text-slate-500">Estoque</span>
                                                <span class="font-bold text-slate-900">{{ max(0, (int) ($product->stock ?? 0)) }} unidade(s)</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-6 flex items-end justify-between gap-4">
                                        <div>
                                            <p class="text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Preco</p>
                                            <div class="mt-2 flex flex-wrap items-end gap-3">
                                                <p class="text-3xl font-black text-slate-900">R$ {{ number_format((float) $product->effective_price, 2, ',', '.') }}</p>
                                                @if($product->sale_price !== null && (float) $product->sale_price < (float) $product->price)
                                                    <span class="text-sm font-bold text-slate-400 line-through">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                        <a href="{{ route('seller-stores.products.show', [$store->slug, $product->slug]) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">
                                            Ver detalhes
                                        </a>
                                        <form action="{{ route('seller-products.cart.add', $product) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="buy_now" value="1">
                                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 transition hover:brightness-110" style="background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $accentColor }} 100%);">
                                                Comprar agora
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @elseif(!$hasAnyCatalog)
                <section class="mt-10">
                    <div class="store-surface-card rounded-[2.25rem] p-8 md:p-10">
                        <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                            <div class="max-w-3xl">
                                <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Em preparacao</p>
                                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">Esta loja ainda esta montando a sua vitrine.</h2>
                                <p class="mt-4 text-sm leading-7 text-slate-600 md:text-base">Os canais da marca ja estao ativos e novos produtos, cursos, mentorias ou eventos podem ser publicados a qualquer momento.</p>
                            </div>

                            @if($socialLinks->isNotEmpty())
                                <div class="flex flex-wrap gap-3">
                                    @foreach($socialLinks->take(2) as $link)
                                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">
                                            <i class="{{ $link['icon'] }} text-slate-400"></i> {{ $link['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1.15fr),360px]">
                <section class="space-y-8">
                    @if($courses->isNotEmpty())
                        <section id="cursos" class="store-surface-card rounded-[2.25rem] p-6 md:p-8">
                            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Educacao premium</p>
                                    <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Cursos publicados por {{ $store->brand_name }}</h2>
                                </div>
                                <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-bold text-blue-700">
                                    <i class="fas fa-graduation-cap"></i> {{ $courses->count() }} opcao(oes)
                                </div>
                            </div>
                            <div class="mt-6 grid gap-4 md:grid-cols-2">
                                @foreach($courses as $course)
                                    <a href="{{ route('courses.show', $course->slug ?: $course->id) }}" class="store-content-card rounded-[1.75rem] border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-5 transition">
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-xl text-blue-700">
                                                <i class="fas fa-graduation-cap"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Curso</p>
                                                <h3 class="mt-2 text-xl font-black tracking-tight text-slate-900">{{ $course->title }}</h3>
                                                <p class="store-line-clamp-4 mt-3 text-sm leading-7 text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags((string) $course->short_description), 150) }}</p>
                                                <span class="mt-4 inline-flex items-center gap-2 text-sm font-black text-blue-700">Ver curso <i class="fas fa-arrow-right text-[11px]"></i></span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($mentorships->isNotEmpty())
                        <section id="mentorias" class="store-surface-card rounded-[2.25rem] p-6 md:p-8">
                            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Consultoria e mentoria</p>
                                    <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Mentorias disponiveis</h2>
                                </div>
                                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700">
                                    <i class="fas fa-user-tie"></i> Atendimento especializado
                                </div>
                            </div>
                            <div class="mt-6 grid gap-4 md:grid-cols-2">
                                @foreach($mentorships as $mentorship)
                                    <a href="{{ route('mentorships.show', $mentorship) }}" class="store-content-card rounded-[1.75rem] border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-5 transition">
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-xl text-emerald-700">
                                                <i class="fas fa-user-tie"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Mentoria</p>
                                                <h3 class="mt-2 text-xl font-black tracking-tight text-slate-900">{{ $mentorship->title }}</h3>
                                                <p class="store-line-clamp-4 mt-3 text-sm leading-7 text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags((string) $mentorship->description), 150) }}</p>
                                                <span class="mt-4 inline-flex items-center gap-2 text-sm font-black text-emerald-700">Ver mentoria <i class="fas fa-arrow-right text-[11px]"></i></span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($events->isNotEmpty())
                        <section id="eventos" class="store-surface-card rounded-[2.25rem] p-6 md:p-8">
                            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Eventos e experiencias</p>
                                    <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Agenda da loja</h2>
                                </div>
                                <div class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-4 py-2 text-sm font-bold text-amber-700">
                                    <i class="fas fa-calendar-days"></i> Programacao ativa
                                </div>
                            </div>
                            <div class="mt-6 grid gap-4 md:grid-cols-2">
                                @foreach($events as $event)
                                    <a href="{{ route('events.show', $event) }}" class="store-content-card rounded-[1.75rem] border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-5 transition">
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-xl text-amber-700">
                                                <i class="fas fa-calendar-days"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Evento</p>
                                                <h3 class="mt-2 text-xl font-black tracking-tight text-slate-900">{{ $event->title }}</h3>
                                                <p class="store-line-clamp-4 mt-3 text-sm leading-7 text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags((string) $event->description), 150) }}</p>
                                                <span class="mt-4 inline-flex items-center gap-2 text-sm font-black text-amber-700">Ver evento <i class="fas fa-arrow-right text-[11px]"></i></span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </section>

                <aside class="space-y-6">
                    <div class="store-info-card store-surface-card rounded-[2rem] p-6">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Atendimento da marca</p>
                        <div class="mt-5 space-y-4 text-sm text-slate-600">
                            @if($store->support_email)
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-900">E-mail</p>
                                        <p class="mt-1 break-all leading-6">{{ $store->support_email }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($store->support_phone)
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-900">Telefone</p>
                                        <p class="mt-1 leading-6">{{ $store->support_phone }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($store->whatsapp)
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                        <i class="fab fa-whatsapp"></i>
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-900">WhatsApp</p>
                                        <p class="mt-1 leading-6">{{ $store->whatsapp }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            @if($whatsappUrl)
                                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:brightness-110">
                                    <i class="fab fa-whatsapp"></i> Falar no WhatsApp
                                </a>
                            @endif
                            @if($store->support_email)
                                <a href="mailto:{{ $store->support_email }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">
                                    <i class="fas fa-envelope text-slate-400"></i> Enviar e-mail
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="store-info-card store-surface-card rounded-[2rem] p-6">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Vitrine oficial</p>
                        <div class="mt-5 space-y-4 text-sm leading-7 text-slate-600">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                                    <i class="fas fa-shield-halved"></i>
                                </div>
                                <p><span class="font-black text-slate-900">Split integrado:</span> os pedidos seguem a mesma governanca do marketplace da plataforma.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <p><span class="font-black text-slate-900">Compra direta:</span> o cliente pode ir do catalogo ao checkout com poucos cliques.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                                    <i class="fas fa-store"></i>
                                </div>
                                <p><span class="font-black text-slate-900">Marca propria:</span> identidade visual, logo, banner e canais oficiais do vendedor em um espaco dedicado.</p>
                            </div>
                        </div>
                    </div>

                    <div class="store-info-card store-surface-card rounded-[2rem] p-6">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Sobre a marca</p>
                        @if($storeBioHtml)
                            <div class="store-section-richtext mt-5 text-sm">{!! $storeBioHtml !!}</div>
                        @else
                            <p class="mt-5 text-sm leading-7 text-slate-600">A loja de {{ $store->brand_name }} opera dentro do ecossistema UNN e usa a infraestrutura central da plataforma para publicar produtos, cursos, mentorias e eventos.</p>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection
