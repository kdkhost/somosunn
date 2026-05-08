@extends('layouts.app')

@php
    $primaryColor = $store->primary_color ?: '#1F5EDB';
    $accentColor = $store->accent_color ?: '#0F172A';
    $descriptionHtml = \App\Support\RichText::toHtml($product->description);
    $excerpt = $product->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 180);
    $coinName = (string) (app(\App\Services\PointsExchangeService::class)->settings()['coin_name'] ?? 'UNNBIT');
    $canBuyInStore = $product->supportsInternalCheckout();
    $canRedeemWithPoints = $product->supportsPointsRedemption() && $product->redeemableItem;
    $canBuyExternally = $product->supportsExternalCheckout();
    $pointsCost = $canRedeemWithPoints ? (int) $product->redeemableItem->points_cost : 0;
    $isPlatformStore = $store->isPlatformStore();

    $galleryItems = collect();

    if ($product->cover_url) {
        $galleryItems->push([
            'type' => 'image',
            'url' => $product->cover_url,
            'alt' => $product->title,
        ]);
    }

    foreach ($product->media as $media) {
        $galleryItems->push([
            'type' => $media->media_type === 'video' ? 'video' : 'image',
            'url' => $media->file_url,
            'alt' => $media->alt_text ?: $product->title,
        ]);
    }

    $galleryItems = $galleryItems
        ->unique(fn ($item) => $item['type'] . '|' . $item['url'])
        ->values();

    $digitalDeliveryText = match ($product->digital_delivery_type) {
        'external_url' => 'Acesso digital liberado após a aprovação do pagamento.',
        'file' => 'Download protegido liberado na área do comprador após a aprovação do pagamento.',
        default => 'Entrega digital liberada após a aprovação do pagamento.',
    };

    $whatsappUrl = null;
    if (filled($store->whatsapp)) {
        $whatsappDigits = preg_replace('/\D+/', '', (string) $store->whatsapp);
        if ($whatsappDigits) {
            $whatsappUrl = 'https://wa.me/' . $whatsappDigits;
        }
    }

    // Calcular desconto
    $hasDiscount = $product->sale_price !== null && (float) $product->sale_price < (float) $product->price;
    $discountPercent = 0;
    if ($hasDiscount && (float) $product->price > 0) {
        $discountPercent = (int) round((((float) $product->price - (float) $product->sale_price) / (float) $product->price) * 100);
    }
@endphp

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    /* Shell da página */
    .pd-shell {
        background: linear-gradient(180deg, #f1f5f9 0%, #f8fafc 20%, #ffffff 100%);
        min-height: 100vh;
    }

    /* Breadcrumb */
    .pd-breadcrumb {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.875rem;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        color: #475569;
    }
    .pd-breadcrumb a { color: #1e40af; }
    .pd-breadcrumb a:hover { color: #1e3a8a; }

    /* Galeria */
    .pd-gallery-main {
        position: relative;
        aspect-ratio: 4/3;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-radius: 1.5rem;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .pd-gallery-main img,
    .pd-gallery-main video { width: 100%; height: 100%; object-fit: contain; background: #fff; }

    .pd-gallery-thumbs {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.5rem;
        margin-top: 0.75rem;
    }
    .pd-thumb {
        aspect-ratio: 1/1;
        border-radius: 0.875rem;
        overflow: hidden;
        background: #f1f5f9;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all .2s ease;
    }
    .pd-thumb:hover { border-color: #cbd5e1; }
    .pd-thumb.is-active { border-color: var(--pd-primary, #1F5EDB); box-shadow: 0 0 0 3px rgba(31, 94, 219, 0.1); }
    .pd-thumb img, .pd-thumb video { width: 100%; height: 100%; object-fit: cover; }

    /* Card de ação sticky */
    .pd-action-card {
        background: #fff;
        border-radius: 1.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 40px -10px rgba(15, 23, 42, 0.12);
    }

    /* Badges */
    .pd-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .pd-badge-blue { background: #dbeafe; color: #1e40af; }
    .pd-badge-green { background: #d1fae5; color: #065f46; }
    .pd-badge-amber { background: #fef3c7; color: #92400e; }
    .pd-badge-red { background: #fee2e2; color: #991b1b; }
    .pd-badge-slate { background: #f1f5f9; color: #475569; }

    /* Botões padrão */
    .pd-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.875rem 1.25rem;
        border-radius: 0.875rem;
        font-size: 0.875rem;
        font-weight: 800;
        line-height: 1.2;
        transition: all .2s ease;
        width: 100%;
        cursor: pointer;
        border: none;
    }
    .pd-btn:active { transform: scale(0.98); }

    .pd-btn-primary {
        background: linear-gradient(135deg, var(--pd-primary, #1F5EDB) 0%, var(--pd-accent, #0F172A) 100%);
        color: #fff;
        box-shadow: 0 8px 24px rgba(31, 94, 219, 0.3);
    }
    .pd-btn-primary:hover { filter: brightness(1.1); box-shadow: 0 12px 28px rgba(31, 94, 219, 0.35); }

    .pd-btn-amber {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #fff;
        box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
    }
    .pd-btn-amber:hover { filter: brightness(1.1); box-shadow: 0 12px 28px rgba(245, 158, 11, 0.35); }

    .pd-btn-outline {
        background: #fff;
        border: 1.5px solid #e2e8f0;
        color: #334155;
    }
    .pd-btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }

    .pd-btn-ghost {
        background: transparent;
        color: #64748b;
        padding: 0.625rem 0.875rem;
    }
    .pd-btn-ghost:hover { color: #1e40af; background: #f1f5f9; }

    /* Price display */
    .pd-price-current {
        font-size: 2.5rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1;
    }
    .pd-price-old {
        font-size: 1rem;
        color: #94a3b8;
        text-decoration: line-through;
        margin-left: 0.75rem;
    }
    .pd-price-discount {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.625rem;
        border-radius: 9999px;
        background: #dcfce7;
        color: #166534;
        font-size: 0.75rem;
        font-weight: 800;
        margin-left: 0.5rem;
    }

    /* Divisor OR */
    .pd-or-divider {
        display: flex;
        align-items: center;
        margin: 1rem 0;
    }
    .pd-or-divider::before,
    .pd-or-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }
    .pd-or-divider span {
        margin: 0 0.875rem;
        font-size: 0.6875rem;
        font-weight: 900;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.12em;
    }

    /* Info rows */
    .pd-info-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.625rem 0;
        border-bottom: 1px dashed #e2e8f0;
        font-size: 0.8125rem;
    }
    .pd-info-row:last-child { border-bottom: none; }
    .pd-info-label { color: #64748b; font-weight: 600; }
    .pd-info-value { color: #0f172a; font-weight: 800; }

    /* Descrição */
    .pd-richtext { color: #334155; line-height: 1.75; }
    .pd-richtext p, .pd-richtext ul, .pd-richtext ol, .pd-richtext blockquote {
        margin-top: 0.75rem;
        line-height: 1.75;
    }
    .pd-richtext ul, .pd-richtext ol { padding-left: 1.25rem; }
    .pd-richtext a { color: #2563eb; font-weight: 700; text-decoration: underline; text-underline-offset: 3px; }
    .pd-richtext h1, .pd-richtext h2, .pd-richtext h3 { color: #0f172a; font-weight: 900; margin-top: 1.25rem; margin-bottom: 0.5rem; }
    .pd-richtext h2 { font-size: 1.25rem; }
    .pd-richtext h3 { font-size: 1.125rem; }

    /* Seller card */
    .pd-seller-card {
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        border: 1px solid #e2e8f0;
    }

    /* Quantity input */
    .pd-qty-wrapper {
        display: flex;
        align-items: center;
        gap: 0;
        border: 1.5px solid #e2e8f0;
        border-radius: 0.75rem;
        overflow: hidden;
        background: #fff;
    }
    .pd-qty-btn {
        width: 2.75rem;
        height: 2.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        color: #475569;
        font-weight: 900;
        cursor: pointer;
        border: none;
        transition: background .15s ease;
    }
    .pd-qty-btn:hover:not(:disabled) { background: #f1f5f9; color: #0f172a; }
    .pd-qty-btn:disabled { opacity: 0.4; cursor: not-allowed; }
    .pd-qty-input {
        width: 3.5rem;
        height: 2.75rem;
        text-align: center;
        border: none;
        background: #fff;
        font-size: 0.9375rem;
        font-weight: 700;
        color: #0f172a;
    }
    .pd-qty-input:focus { outline: none; }

    /* Tabs */
    .pd-tabs {
        display: flex;
        gap: 0.5rem;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 1.5rem;
        overflow-x: auto;
    }
    .pd-tab {
        padding: 0.875rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 700;
        color: #64748b;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        white-space: nowrap;
        transition: all .2s ease;
    }
    .pd-tab:hover { color: #0f172a; }
    .pd-tab.is-active { color: var(--pd-primary, #1F5EDB); border-bottom-color: var(--pd-primary, #1F5EDB); }

    @media (max-width: 1023px) {
        .pd-sticky-mobile {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 40;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            box-shadow: 0 -10px 30px rgba(15, 23, 42, 0.08);
            display: flex;
            gap: 0.5rem;
        }
    }
</style>
@endpush

@section('title', $product->title . ' - ' . $store->brand_name)

@section('content')
<div class="pd-shell pt-24 pb-24 lg:pb-16" style="--pd-primary: {{ $primaryColor }}; --pd-accent: {{ $accentColor }};">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-xs">
            <a href="{{ route('marketplace.index') }}" class="pd-breadcrumb">
                <i class="fas fa-store text-[10px]"></i> Marketplace
            </a>
            <span class="text-slate-400">/</span>
            <a href="{{ route('seller-stores.show', $store->slug) }}" class="pd-breadcrumb">
                <i class="fas fa-shop text-[10px]"></i> {{ $store->brand_name }}
            </a>
            <span class="text-slate-400">/</span>
            <span class="pd-breadcrumb bg-slate-100 !text-slate-900">
                {{ \Illuminate\Support\Str::limit($product->title, 30) }}
            </span>
        </nav>

        {{-- Grid principal --}}
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.3fr),420px]">

            {{-- Coluna esquerda: Galeria + Descrição --}}
            <div class="space-y-6">

                {{-- Galeria --}}
                <div class="pd-action-card p-4 md:p-6" x-data="{ activeIndex: 0, items: @js($galleryItems) }">
                    <div class="pd-gallery-main">
                        <template x-if="items.length">
                            <template x-if="items[activeIndex].type === 'video'">
                                <video :src="items[activeIndex].url" controls></video>
                            </template>
                        </template>
                        <template x-if="items.length && items[activeIndex].type === 'image'">
                            <img :src="items[activeIndex].url" :alt="items[activeIndex].alt || '{{ addslashes($product->title) }}'">
                        </template>

                        @if($galleryItems->isEmpty())
                            <div class="flex h-full w-full items-center justify-center text-6xl text-slate-300">
                                <i class="fas fa-box-open"></i>
                            </div>
                        @endif

                        {{-- Badge de desconto flutuante --}}
                        @if($hasDiscount && $discountPercent > 0)
                            <div class="absolute top-4 left-4 px-3 py-1.5 rounded-full bg-gradient-to-r from-rose-500 to-red-600 text-white text-xs font-black shadow-lg">
                                -{{ $discountPercent }}% OFF
                            </div>
                        @endif

                        {{-- Badge destaque --}}
                        @if($product->is_featured)
                            <div class="absolute top-4 right-4 px-3 py-1.5 rounded-full bg-gradient-to-r from-amber-400 to-amber-500 text-white text-xs font-black shadow-lg">
                                <i class="fas fa-star mr-1"></i> DESTAQUE
                            </div>
                        @endif
                    </div>

                    {{-- Thumbnails --}}
                    @if($galleryItems->count() > 1)
                        <div class="pd-gallery-thumbs">
                            @foreach($galleryItems as $index => $item)
                                <button type="button" @click="activeIndex = {{ $index }}"
                                    :class="activeIndex === {{ $index }} ? 'is-active' : ''"
                                    class="pd-thumb">
                                    @if($item['type'] === 'video')
                                        <div class="relative w-full h-full">
                                            <video src="{{ $item['url'] }}"></video>
                                            <div class="absolute inset-0 flex items-center justify-center bg-slate-950/40 text-white text-xs">
                                                <i class="fas fa-play"></i>
                                            </div>
                                        </div>
                                    @else
                                        <img src="{{ $item['url'] }}" alt="{{ $item['alt'] }}">
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Tabs: Descrição, Entrega, Loja --}}
                <div class="pd-action-card p-6 md:p-8" x-data="{ tab: 'description' }">
                    <div class="pd-tabs">
                        <button type="button" @click="tab = 'description'" :class="tab === 'description' ? 'is-active' : ''" class="pd-tab">
                            <i class="fas fa-align-left mr-1.5 text-xs"></i> Descrição
                        </button>
                        <button type="button" @click="tab = 'delivery'" :class="tab === 'delivery' ? 'is-active' : ''" class="pd-tab">
                            <i class="fas fa-{{ $product->isPhysical() ? 'truck-fast' : 'cloud-arrow-down' }} mr-1.5 text-xs"></i>
                            {{ $product->isPhysical() ? 'Entrega' : 'Acesso digital' }}
                        </button>
                        <button type="button" @click="tab = 'seller'" :class="tab === 'seller' ? 'is-active' : ''" class="pd-tab">
                            <i class="fas fa-store mr-1.5 text-xs"></i> Sobre a loja
                        </button>
                    </div>

                    <div x-show="tab === 'description'" x-cloak>
                        <div class="pd-richtext">
                            {!! $descriptionHtml ?: '<p class="text-slate-500 italic">Sem descrição detalhada cadastrada.</p>' !!}
                        </div>
                    </div>

                    <div x-show="tab === 'delivery'" x-cloak class="space-y-4">
                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-blue-50 border border-blue-100">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-{{ $product->isPhysical() ? 'truck-fast' : 'cloud-arrow-down' }}"></i>
                            </div>
                            <div>
                                <p class="font-black text-slate-900">
                                    {{ $product->isPhysical() ? 'Envio pelos Correios' : 'Entrega digital automática' }}
                                </p>
                                <p class="text-sm text-slate-600 mt-1 leading-relaxed">
                                    {{ $product->isPhysical() ? 'Frete calculado no checkout baseado no seu endereço. PAC e SEDEX disponíveis. Rastreio no painel.' : $digitalDeliveryText }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-100">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-shield-halved"></i>
                            </div>
                            <div>
                                <p class="font-black text-slate-900">Compra protegida</p>
                                <p class="text-sm text-slate-600 mt-1 leading-relaxed">
                                    Pedido processado pelo marketplace da UNN com split automático. Reembolso em caso de não-entrega.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div x-show="tab === 'seller'" x-cloak class="pd-seller-card rounded-2xl p-5">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-white flex items-center justify-center overflow-hidden flex-shrink-0 border border-slate-200">
                                @if($store->logo_url)
                                    <img src="{{ $store->logo_url }}" alt="{{ $store->brand_name }}" class="h-full w-full object-cover">
                                @else
                                    <i class="fas fa-store text-xl" style="color: {{ $primaryColor }};"></i>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-black uppercase tracking-wider text-slate-400 mb-1">
                                    Vendido por {!! $isPlatformStore ? '<span class="text-emerald-600">Loja oficial da plataforma</span>' : '/loja/' . $store->slug !!}
                                </p>
                                <h4 class="text-lg font-black text-slate-900">{{ $store->brand_name }}</h4>
                                @if($store->tagline)
                                    <p class="text-sm text-slate-600 mt-1">{{ $store->tagline }}</p>
                                @endif

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <a href="{{ route('seller-stores.show', $store->slug) }}" class="pd-btn pd-btn-outline !w-auto !py-2.5 !px-4 !text-xs">
                                        <i class="fas fa-store"></i> Ver loja completa
                                    </a>
                                    @if($whatsappUrl)
                                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="pd-btn !w-auto !py-2.5 !px-4 !text-xs" style="background: #25d366; color: white;">
                                            <i class="fab fa-whatsapp"></i> WhatsApp
                                        </a>
                                    @endif
                                    @if($store->support_email)
                                        <a href="mailto:{{ $store->support_email }}" class="pd-btn pd-btn-outline !w-auto !py-2.5 !px-4 !text-xs">
                                            <i class="fas fa-envelope"></i> E-mail
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Produtos relacionados --}}
                @if($relatedProducts->isNotEmpty())
                    <div class="pd-action-card p-6 md:p-8">
                        <div class="flex items-end justify-between gap-3 mb-5">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Continuar comprando</p>
                                <h2 class="mt-1 text-2xl font-black text-slate-900">Mais desta loja</h2>
                            </div>
                            <a href="{{ route('seller-stores.show', $store->slug) }}" class="text-sm font-black text-blue-700 hover:text-blue-800">
                                Ver todos <i class="fas fa-arrow-right text-[10px] ml-1"></i>
                            </a>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach($relatedProducts as $relatedProduct)
                                <a href="{{ route('seller-stores.products.show', [$store->slug, $relatedProduct->slug]) }}"
                                    class="group block overflow-hidden rounded-2xl border border-slate-200 bg-white hover:border-blue-300 hover:shadow-lg transition-all">
                                    <div class="aspect-[4/3] overflow-hidden bg-slate-100">
                                        @if($relatedProduct->cover_url)
                                            <img src="{{ $relatedProduct->cover_url }}" alt="{{ $relatedProduct->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-4xl text-slate-300">
                                                <i class="fas fa-box-open"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-4">
                                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $relatedProduct->isPhysical() ? 'Físico' : 'Digital' }}</p>
                                        <h3 class="mt-1 text-sm font-black text-slate-900 line-clamp-2">{{ $relatedProduct->title }}</h3>
                                        <p class="mt-2 text-lg font-black text-slate-900">R$ {{ number_format((float) $relatedProduct->effective_price, 2, ',', '.') }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Coluna direita: Ações (sticky) --}}
            <aside class="lg:sticky lg:top-28 lg:self-start space-y-4">

                {{-- Título e badges --}}
                <div class="pd-action-card p-6">
                    <div class="flex flex-wrap gap-2 mb-3">
                        <span class="pd-badge pd-badge-blue">
                            <i class="fas fa-{{ $product->isPhysical() ? 'box' : 'cloud' }}"></i>
                            {{ $product->isPhysical() ? 'Físico' : 'Digital' }}
                        </span>
                        @if($product->is_featured)
                            <span class="pd-badge pd-badge-amber">
                                <i class="fas fa-star"></i> Destaque
                            </span>
                        @endif
                        @if($canRedeemWithPoints && $canBuyInStore)
                            <span class="pd-badge pd-badge-green">
                                <i class="fas fa-circle-check"></i> Dinheiro ou Pontos
                            </span>
                        @elseif($canRedeemWithPoints)
                            <span class="pd-badge pd-badge-amber">
                                <i class="fas fa-coins"></i> Troca de pontos
                            </span>
                        @elseif($canBuyExternally)
                            <span class="pd-badge pd-badge-slate">
                                <i class="fas fa-up-right-from-square"></i> Site externo
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl md:text-3xl font-black text-slate-900 leading-tight">{{ $product->title }}</h1>

                    @if($excerpt)
                        <p class="mt-3 text-sm text-slate-600 leading-relaxed">{{ $excerpt }}</p>
                    @endif

                    @if($product->sku)
                        <p class="mt-3 text-xs text-slate-400">SKU: <span class="font-mono font-bold text-slate-600">{{ $product->sku }}</span></p>
                    @endif
                </div>

                {{-- Card de ação principal (compra/troca) --}}
                <div class="pd-action-card p-6">
                    {{-- Preço --}}
                    @if($canBuyInStore || $canBuyExternally)
                        <div class="mb-5">
                            <p class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2">Preço à vista</p>
                            <div class="flex items-end flex-wrap gap-2">
                                <span class="pd-price-current">R$ {{ number_format((float) $product->effective_price, 2, ',', '.') }}</span>
                                @if($hasDiscount)
                                    <span class="pd-price-old">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</span>
                                    @if($discountPercent > 0)
                                        <span class="pd-price-discount">-{{ $discountPercent }}%</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Preço em pontos (quando só tem troca) --}}
                    @if(!$canBuyInStore && !$canBuyExternally && $canRedeemWithPoints)
                        <div class="mb-5">
                            <p class="text-xs font-black uppercase tracking-wider text-slate-400 mb-2">Custo em pontos</p>
                            <div class="flex items-end flex-wrap gap-2">
                                <span class="pd-price-current text-amber-600">{{ number_format($pointsCost, 0, ',', '.') }}</span>
                                <span class="text-lg font-black text-amber-600 pb-1">{{ $coinName }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- SITE EXTERNO --}}
                    @if($canBuyExternally)
                        <a href="{{ $product->external_checkout_url }}" target="_blank" rel="noopener noreferrer" class="pd-btn pd-btn-primary">
                            <i class="fas fa-up-right-from-square"></i>
                            <span>Comprar no site do vendedor</span>
                        </a>
                        <p class="text-[11px] text-slate-500 text-center mt-2">
                            A compra será concluída no site externo do vendedor
                        </p>
                    @else
                        {{-- COMPRA NO MARKETPLACE --}}
                        @if($canBuyInStore)
                            <form action="{{ route('seller-products.cart.add', $product) }}" method="POST" class="space-y-3" x-data="{ qty: 1, stock: {{ (int) ($product->stock ?? 999) }} }">
                                @csrf

                                {{-- Quantidade --}}
                                <div class="flex items-center gap-3">
                                    <label class="text-xs font-black uppercase tracking-wider text-slate-500">Quantidade</label>
                                    <div class="pd-qty-wrapper">
                                        <button type="button" class="pd-qty-btn" @click="qty = Math.max(1, qty - 1)" :disabled="qty <= 1">
                                            <i class="fas fa-minus text-xs"></i>
                                        </button>
                                        <input type="number" name="quantity" min="1" :max="stock" x-model="qty" class="pd-qty-input" readonly>
                                        <button type="button" class="pd-qty-btn" @click="qty = Math.min(stock, qty + 1)" :disabled="qty >= stock">
                                            <i class="fas fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                    @if($product->isPhysical())
                                        <span class="text-xs text-slate-400 ml-auto">
                                            <i class="fas fa-box text-[10px]"></i> {{ max(0, (int) ($product->stock ?? 0)) }} em estoque
                                        </span>
                                    @endif
                                </div>

                                {{-- Botão Comprar agora --}}
                                <button type="submit" name="buy_now" value="1" class="pd-btn pd-btn-primary">
                                    <i class="fas fa-bolt"></i>
                                    <span>Comprar agora com dinheiro</span>
                                </button>

                                {{-- Botão Adicionar ao carrinho --}}
                                <button type="submit" class="pd-btn pd-btn-outline">
                                    <i class="fas fa-cart-plus"></i>
                                    <span>Adicionar ao carrinho</span>
                                </button>
                            </form>
                        @endif

                        {{-- TROCA POR PONTOS --}}
                        @if($canRedeemWithPoints)
                            @if($canBuyInStore)
                                <div class="pd-or-divider">
                                    <span>Ou pague com pontos</span>
                                </div>
                            @endif

                            @if(!$canBuyInStore)
                                {{-- Só troca - botão único em destaque --}}
                                <a href="{{ route('panel.redemptions.shop') }}#item-{{ optional($product->redeemableItem)->id }}" class="pd-btn pd-btn-amber">
                                    <i class="fas fa-coins"></i>
                                    <span>Trocar por {{ number_format($pointsCost, 0, ',', '.') }} {{ $coinName }}</span>
                                </a>
                            @else
                                {{-- Tem ambos - botão secundário --}}
                                <a href="{{ route('panel.redemptions.shop') }}#item-{{ optional($product->redeemableItem)->id }}" class="pd-btn pd-btn-amber">
                                    <i class="fas fa-coins"></i>
                                    <span>Trocar por {{ number_format($pointsCost, 0, ',', '.') }} {{ $coinName }}</span>
                                </a>
                                <p class="text-[11px] text-slate-500 text-center mt-2">
                                    Você ainda pode usar seus pontos como forma de pagamento
                                </p>
                            @endif
                        @endif

                        {{-- Sem nenhum canal ativo --}}
                        @if(!$canBuyInStore && !$canRedeemWithPoints)
                            <div class="text-center py-6">
                                <i class="fas fa-lock text-3xl text-slate-300 mb-2"></i>
                                <p class="text-sm font-bold text-slate-600">Produto indisponível no momento</p>
                                <p class="text-xs text-slate-400 mt-1">O vendedor não configurou a forma de compra</p>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Informações do produto --}}
                <div class="pd-action-card p-6">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400 mb-3">
                        <i class="fas fa-circle-info mr-1"></i> Detalhes
                    </p>
                    <div class="space-y-0">
                        <div class="pd-info-row">
                            <span class="pd-info-label">Tipo</span>
                            <span class="pd-info-value">
                                <i class="fas fa-{{ $product->isPhysical() ? 'box' : 'cloud' }} mr-1 text-xs"></i>
                                {{ $product->isPhysical() ? 'Físico' : 'Digital' }}
                            </span>
                        </div>
                        <div class="pd-info-row">
                            <span class="pd-info-label">Canal</span>
                            <span class="pd-info-value">{{ $product->salesChannelLabel() }}</span>
                        </div>
                        @if($product->isPhysical())
                            <div class="pd-info-row">
                                <span class="pd-info-label">Estoque</span>
                                <span class="pd-info-value">{{ max(0, (int) ($product->stock ?? 0)) }} un.</span>
                            </div>
                            @if($product->weight_grams)
                                <div class="pd-info-row">
                                    <span class="pd-info-label">Peso</span>
                                    <span class="pd-info-value">{{ number_format((int) $product->weight_grams / 1000, 2, ',', '.') }} kg</span>
                                </div>
                            @endif
                        @endif
                        @if($canRedeemWithPoints)
                            <div class="pd-info-row">
                                <span class="pd-info-label">Em pontos</span>
                                <span class="pd-info-value text-amber-600">{{ number_format($pointsCost, 0, ',', '.') }} {{ $coinName }}</span>
                            </div>
                        @endif
                        <div class="pd-info-row">
                            <span class="pd-info-label">Entrega</span>
                            <span class="pd-info-value">
                                @if($canBuyExternally)
                                    <i class="fas fa-up-right-from-square text-xs"></i> Site externo
                                @elseif($product->isPhysical())
                                    <i class="fas fa-truck-fast text-xs"></i> Correios
                                @else
                                    <i class="fas fa-cloud-arrow-down text-xs"></i> Digital
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Call pro carrinho --}}
                <a href="{{ route('seller-products.cart.show') }}" class="block text-center text-sm font-bold text-slate-600 hover:text-blue-700 py-3">
                    <i class="fas fa-cart-shopping mr-1"></i> Ver meu carrinho
                </a>
            </aside>
        </div>
    </div>
</div>
@endsection
