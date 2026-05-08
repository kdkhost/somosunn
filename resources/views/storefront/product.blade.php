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
        'external_url' => 'Acesso digital salvo no pedido apos a aprovacao do pagamento.',
        'file' => 'Download protegido liberado na area do comprador apos a aprovacao do pagamento.',
        default => 'Entrega digital liberada apos a aprovacao do pagamento.',
    };

    $whatsappUrl = null;
    if (filled($store->whatsapp)) {
        $whatsappDigits = preg_replace('/\D+/', '', (string) $store->whatsapp);
        if ($whatsappDigits) {
            $whatsappUrl = 'https://wa.me/' . $whatsappDigits;
        }
    }
@endphp

@push('styles')
    <style>
        [x-cloak] { display: none !important; }

        .store-product-shell {
            background:
                radial-gradient(circle at top left, rgba(31, 94, 219, 0.16), transparent 24%),
                radial-gradient(circle at 100% 0%, rgba(15, 23, 42, 0.12), transparent 28%),
                linear-gradient(180deg, #f8fbff 0%, #eef4ff 22%, #ffffff 58%);
        }

        .store-product-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(135deg, rgba(2, 6, 23, 0.76) 0%, rgba(15, 23, 42, 0.52) 52%, rgba(30, 41, 59, 0.24) 100%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.05), transparent 32%);
            pointer-events: none;
        }

        .product-surface-card {
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
        }

        .product-glass-card {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 0 24px 60px rgba(2, 6, 23, 0.2);
            backdrop-filter: blur(18px);
        }

        .product-richtext p,
        .product-richtext ul,
        .product-richtext ol,
        .product-richtext blockquote {
            margin-top: 0.8rem;
            margin-bottom: 0;
            line-height: 1.85;
        }

        .product-richtext ul,
        .product-richtext ol {
            padding-left: 1.2rem;
        }

        .product-richtext a {
            color: #1d4ed8;
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 3px;
        }
    </style>
@endpush

@section('title', $product->title . ' - ' . $store->brand_name)

@section('content')
    <div class="store-product-shell min-h-screen pt-24 pb-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <section class="store-product-hero relative overflow-hidden rounded-[2.75rem] border border-slate-200/70 bg-slate-950 shadow-[0_35px_100px_-35px_rgba(15,23,42,0.55)]" style="background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $accentColor }} 88%);">
                @if($store->banner_url)
                    <img src="{{ $store->banner_url }}" alt="{{ $store->brand_name }}" class="absolute inset-0 h-full w-full object-cover opacity-30">
                @endif

                <div class="relative z-10 p-6 md:p-8 xl:p-10">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <a href="{{ route('seller-stores.show', $store->slug) }}" class="inline-flex items-center gap-2 text-sm font-bold text-white/80 transition hover:text-white">
                            <i class="fas fa-arrow-left text-[12px]"></i> Voltar para a loja
                        </a>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('seller-products.cart.show') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/15">
                                <i class="fas fa-cart-shopping text-white/75"></i> Carrinho
                            </a>
                            <a href="{{ route('seller-stores.show', $store->slug) }}" class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-black text-slate-950 shadow-lg shadow-slate-950/20 transition hover:brightness-110">
                                Ver mais desta loja
                            </a>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-8 xl:grid-cols-[1.1fr,0.9fr] xl:items-end">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-[0.28em] text-white/80">
                                    <i class="fas {{ $product->isPhysical() ? 'fa-truck-fast' : 'fa-bolt' }} text-[11px]"></i>
                                    {{ $product->isPhysical() ? 'Produto fisico' : 'Produto digital' }}
                                </span>
                                @if($product->is_featured)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-slate-950/25 px-4 py-2 text-xs font-black uppercase tracking-[0.28em] text-white/80">
                                        <i class="fas fa-star text-[11px]"></i> Destaque
                                    </span>
                                @endif
                                @if($product->sku)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-slate-950/25 px-4 py-2 text-xs font-bold uppercase tracking-[0.24em] text-white/70">
                                        SKU {{ $product->sku }}
                                    </span>
                                @endif
                                <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-slate-950/25 px-4 py-2 text-xs font-bold uppercase tracking-[0.24em] text-white/70">
                                    {{ $product->salesChannelLabel() }}
                                </span>
                            </div>

                            <h1 class="mt-5 max-w-4xl text-4xl font-black tracking-tight text-white md:text-5xl">{{ $product->title }}</h1>
                            <p class="mt-5 max-w-3xl text-base leading-8 text-white/78 md:text-lg">{{ $excerpt }}</p>

                            <div class="mt-7 flex flex-wrap items-center gap-4">
                                <div class="product-glass-card rounded-[1.75rem] px-5 py-4 text-white">
                                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-white/55">Preco atual</p>
                                    <div class="mt-2 flex flex-wrap items-end gap-3">
                                        <p class="text-4xl font-black">R$ {{ number_format((float) $product->effective_price, 2, ',', '.') }}</p>
                                        @if($product->sale_price !== null && (float) $product->sale_price < (float) $product->price)
                                            <span class="pb-1 text-sm font-bold text-white/55 line-through">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="product-glass-card rounded-[1.75rem] px-5 py-4 text-white">
                                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-white/55">Anunciado e vendido por</p>
                                    <div class="mt-3 flex items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-white/90">
                                            @if($store->logo_url)
                                                <img src="{{ $store->logo_url }}" alt="{{ $store->brand_name }}" class="h-full w-full object-cover">
                                            @else
                                                <i class="fas fa-store text-lg" style="color: {{ $primaryColor }};"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-black">{{ $store->brand_name }}</p>
                                            <p class="text-sm text-white/65">{{ $isPlatformStore ? 'Loja oficial da plataforma' : '/loja/' . $store->slug }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="product-glass-card rounded-[2rem] p-5 text-white">
                            <p class="text-[11px] font-black uppercase tracking-[0.24em] text-white/55">Experiencia de compra</p>
                            <div class="mt-4 space-y-4 text-sm text-white/78">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white/75">
                                        <i class="fas fa-shield-halved"></i>
                                    </div>
                                    <div>
                                        <p class="font-black text-white">Compra protegida</p>
                                        <p class="mt-1 leading-7">Pedido, split e pagamento seguem a mesma logica do marketplace da plataforma.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white/75">
                                        <i class="fas {{ $product->isPhysical() ? 'fa-truck-fast' : 'fa-cloud-arrow-down' }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-black text-white">{{ $product->isPhysical() ? 'Frete Correios' : 'Entrega digital' }}</p>
                                        <p class="mt-1 leading-7">{{ $product->isPhysical() ? 'PAC e SEDEX calculados no checkout, conforme endereco do cliente.' : $digitalDeliveryText }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="mt-10 grid gap-8 xl:grid-cols-[minmax(0,1.12fr),420px]">
                <div class="space-y-8">
                    <section class="product-surface-card overflow-hidden rounded-[2.25rem] p-4 md:p-6" x-data="{ activeIndex: 0, items: @js($galleryItems) }">
                        <div class="overflow-hidden rounded-[1.75rem] bg-slate-100">
                            <template x-if="items.length">
                                <div class="aspect-[16/10]">
                                    <template x-if="items[activeIndex].type === 'video'">
                                        <video :src="items[activeIndex].url" controls class="h-full w-full bg-slate-950 object-contain"></video>
                                    </template>
                                    <template x-if="items[activeIndex].type === 'image'">
                                        <img :src="items[activeIndex].url" :alt="items[activeIndex].alt || '{{ addslashes($product->title) }}'" class="h-full w-full object-cover">
                                    </template>
                                </div>
                            </template>

                            @if($galleryItems->isEmpty())
                                <div class="flex aspect-[16/10] items-center justify-center text-5xl text-slate-300">
                                    <i class="fas fa-box-open"></i>
                                </div>
                            @endif
                        </div>

                        @if($galleryItems->count() > 1)
                            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                @foreach($galleryItems as $index => $item)
                                    <button type="button" @click="activeIndex = {{ $index }}" :class="{ 'ring-2 ring-offset-2 ring-offset-white': activeIndex === {{ $index }} }" class="overflow-hidden rounded-[1.25rem] border border-slate-200 bg-slate-100 transition hover:border-blue-200">
                                        @if($item['type'] === 'video')
                                            <div class="relative aspect-[4/3]">
                                                <video src="{{ $item['url'] }}" class="h-full w-full object-cover"></video>
                                                <div class="absolute inset-0 flex items-center justify-center bg-slate-950/30 text-white">
                                                    <i class="fas fa-play"></i>
                                                </div>
                                            </div>
                                        @else
                                            <img src="{{ $item['url'] }}" alt="{{ $item['alt'] }}" class="aspect-[4/3] h-full w-full object-cover">
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="product-surface-card rounded-[2.25rem] p-6 md:p-8">
                        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Descricao detalhada</p>
                                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Tudo sobre este produto</h2>
                            </div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm font-bold text-slate-600">
                                <i class="fas fa-circle-check"></i> Loja oficial dentro da UNN
                            </div>
                        </div>

                        <div class="product-richtext mt-6 text-sm text-slate-600 md:text-base">
                            {!! $descriptionHtml ?: '<p>Sem descricao detalhada cadastrada.</p>' !!}
                        </div>
                    </section>

                    <section class="grid gap-6 lg:grid-cols-2">
                        <div class="product-surface-card rounded-[2rem] p-6">
                            <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">{{ $product->isPhysical() ? 'Logistica' : 'Entrega digital' }}</p>
                            <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-900">{{ $product->isPhysical() ? 'Frete e envio' : 'Como voce recebe' }}</h3>
                            <p class="mt-4 text-sm leading-7 text-slate-600">
                                {{ $product->isPhysical() ? 'O frete e calculado no checkout com base no endereco de entrega. O pedido fica vinculado ao vendedor e pode receber rastreio no painel.' : $digitalDeliveryText }}
                            </p>
                        </div>

                        <div class="product-surface-card rounded-[2rem] p-6">
                            <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Marca vendedora</p>
                            <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-900">{{ $store->brand_name }}</h3>
                            <p class="mt-4 text-sm leading-7 text-slate-600">{{ $store->tagline ?: 'Loja oficial do vendedor dentro do ecossistema UNN, com atendimento e identidade proprios.' }}</p>
                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="{{ route('seller-stores.show', $store->slug) }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">
                                    <i class="fas fa-store text-slate-400"></i> Ver loja
                                </a>
                                @if($whatsappUrl)
                                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:brightness-110">
                                        <i class="fab fa-whatsapp"></i> Falar com a loja
                                    </a>
                                @endif
                            </div>
                        </div>
                    </section>

                    @if($relatedProducts->isNotEmpty())
                        <section class="product-surface-card rounded-[2.25rem] p-6 md:p-8">
                            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Continuar comprando</p>
                                    <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Mais itens desta loja</h2>
                                </div>
                                <a href="{{ route('seller-stores.show', $store->slug) }}" class="text-sm font-black text-blue-700 transition hover:text-blue-800">Ver loja completa</a>
                            </div>

                            <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                                @foreach($relatedProducts as $relatedProduct)
                                    <article class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white">
                                        <a href="{{ route('seller-stores.products.show', [$store->slug, $relatedProduct->slug]) }}" class="block aspect-[16/11] overflow-hidden bg-slate-100">
                                            @if($relatedProduct->cover_url)
                                                <img src="{{ $relatedProduct->cover_url }}" alt="{{ $relatedProduct->title }}" class="h-full w-full object-cover transition duration-500 hover:scale-[1.03]">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center text-4xl text-slate-300">
                                                    <i class="fas fa-box-open"></i>
                                                </div>
                                            @endif
                                        </a>
                                        <div class="p-4">
                                            <p class="text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">{{ $relatedProduct->isPhysical() ? 'Fisico' : 'Digital' }}</p>
                                            <h3 class="mt-2 text-lg font-black tracking-tight text-slate-900">{{ $relatedProduct->title }}</h3>
                                            <p class="mt-3 text-xl font-black text-slate-900">R$ {{ number_format((float) $relatedProduct->effective_price, 2, ',', '.') }}</p>
                                            <a href="{{ route('seller-stores.products.show', [$store->slug, $relatedProduct->slug]) }}" class="mt-4 inline-flex items-center gap-2 text-sm font-black text-blue-700">Ver detalhes <i class="fas fa-arrow-right text-[11px]"></i></a>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>

                <aside class="space-y-6 xl:sticky xl:top-28 xl:self-start">
                    <section class="product-surface-card rounded-[2.25rem] p-6 md:p-7">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Ação principal</p>
                        <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                            @if($canBuyInStore && $canRedeemWithPoints)
                                Escolha como obter
                            @elseif($canBuyExternally)
                                Comprar no site externo
                            @elseif($canRedeemWithPoints)
                                Trocar por {{ $coinName ?? 'UNNBIT' }}
                            @elseif($canBuyInStore)
                                Checkout rápido
                            @else
                                Produto indisponível
                            @endif
                        </h2>
                        <p class="mt-3 text-sm leading-7 text-slate-600">
                            @if($canBuyInStore && $canRedeemWithPoints)
                                Você pode comprar em dinheiro no checkout do marketplace ou trocar por {{ $coinName ?? 'UNNBIT' }}.
                            @elseif($canBuyExternally)
                                Este item é exibido aqui, mas a compra será concluída no site externo do vendedor.
                            @elseif($canRedeemWithPoints)
                                Este item é obtido exclusivamente por resgate com seus {{ $coinName ?? 'UNNBIT' }}.
                            @elseif($canBuyInStore)
                                Adicione ao carrinho ou siga direto para o checkout.
                            @else
                                Este produto não está disponível para compra no momento.
                            @endif
                        </p>

                        <div class="mt-6 rounded-[1.75rem] bg-slate-50 p-5">
                            <p class="text-[11px] font-black uppercase tracking-[0.22em] text-slate-400">Valor do item</p>
                            <div class="mt-2 flex flex-wrap items-end gap-3">
                                <p class="text-4xl font-black text-slate-900">R$ {{ number_format((float) $product->effective_price, 2, ',', '.') }}</p>
                                @if($product->sale_price !== null && (float) $product->sale_price < (float) $product->price)
                                    <span class="pb-1 text-sm font-bold text-slate-400 line-through">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</span>
                                @endif
                            </div>
                            @if($canRedeemWithPoints)
                                <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-amber-50 border border-amber-200 px-3 py-1">
                                    <i class="fas fa-coins text-amber-600 text-xs"></i>
                                    <span class="text-xs font-black text-amber-700">{{ number_format($pointsCost, 0, ',', '.') }} {{ $coinName ?? 'UNNBIT' }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- AÇÕES --}}
                        <div class="mt-6 space-y-3">
                            {{-- Site externo: exclusivo --}}
                            @if($canBuyExternally)
                                <a href="{{ $product->external_checkout_url }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 transition hover:brightness-110"
                                    style="background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $accentColor }} 100%);">
                                    <i class="fas fa-up-right-from-square"></i> Comprar no site externo
                                </a>
                            @else
                                {{-- Compra no marketplace --}}
                                @if($canBuyInStore)
                                    <form action="{{ route('seller-products.cart.add', $product) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label for="product-quantity" class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-2">Quantidade</label>
                                            <input id="product-quantity" type="number" min="1" value="1" name="quantity"
                                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                        </div>

                                        <button type="submit" name="buy_now" value="1"
                                            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 transition hover:brightness-110"
                                            style="background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $accentColor }} 100%);">
                                            <i class="fas fa-bolt"></i> Comprar agora &bull; R$ {{ number_format((float) $product->effective_price, 2, ',', '.') }}
                                        </button>

                                        <button type="submit"
                                            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-blue-300 hover:bg-slate-50 hover:text-blue-700">
                                            <i class="fas fa-cart-shopping"></i> Adicionar ao carrinho
                                        </button>
                                    </form>
                                @endif

                                {{-- Trocar por pontos --}}
                                @if($canRedeemWithPoints)
                                    @if($canBuyInStore)
                                        <div class="relative flex items-center py-1">
                                            <div class="flex-grow border-t border-dashed border-slate-200"></div>
                                            <span class="mx-3 text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">ou</span>
                                            <div class="flex-grow border-t border-dashed border-slate-200"></div>
                                        </div>
                                    @endif
                                    <a href="{{ route('panel.redemptions.shop') }}#item-{{ optional($product->redeemableItem)->id }}"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 px-4 py-3 text-sm font-black text-white shadow-lg shadow-amber-500/30 transition">
                                        <i class="fas fa-coins"></i>
                                        Trocar por {{ number_format($pointsCost, 0, ',', '.') }} {{ $coinName ?? 'UNNBIT' }}
                                    </a>
                                @endif

                                {{-- Nenhum canal ativo --}}
                                @if(!$canBuyInStore && !$canRedeemWithPoints)
                                    <div class="rounded-2xl bg-slate-50 border border-dashed border-slate-200 p-5 text-center">
                                        <i class="fas fa-lock text-2xl text-slate-300 mb-2"></i>
                                        <p class="text-sm font-bold text-slate-600">Este produto não está disponível no momento.</p>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="mt-6 space-y-3 border-t border-slate-100 pt-6 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-semibold text-slate-500">Tipo</span>
                                <span class="font-bold text-slate-900">{{ $product->isPhysical() ? 'Produto físico' : 'Produto digital' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-semibold text-slate-500">Canal</span>
                                <span class="font-bold text-slate-900">{{ $product->salesChannelLabel() }}</span>
                            </div>
                            @if($product->isPhysical())
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-semibold text-slate-500">Estoque</span>
                                    <span class="font-bold text-slate-900">{{ max(0, (int) ($product->stock ?? 0)) }} unidade(s)</span>
                                </div>
                            @endif
                            @if($canRedeemWithPoints)
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-semibold text-slate-500">Troca por pontos</span>
                                    <span class="font-bold text-slate-900">{{ number_format($pointsCost, 0, ',', '.') }} {{ $coinName ?? 'UNNBIT' }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-semibold text-slate-500">Entrega</span>
                                <span class="font-bold text-slate-900">
                                    @if($canBuyExternally)
                                        Site externo
                                    @elseif($product->isPhysical() && $canBuyInStore)
                                        Correios no checkout
                                    @elseif($canRedeemWithPoints)
                                        Resgate com o vendedor
                                    @else
                                        Área do comprador
                                    @endif
                                </span>
                            </div>
                        </div>
                    </section>

                    <section class="product-surface-card rounded-[2rem] p-6">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-slate-400">Atendimento</p>
                        <div class="mt-5 space-y-4 text-sm text-slate-600">
                            @if($store->support_email)
                                <div>
                                    <p class="font-black text-slate-900">E-mail</p>
                                    <p class="mt-1 break-all leading-6">{{ $store->support_email }}</p>
                                </div>
                            @endif
                            @if($store->support_phone)
                                <div>
                                    <p class="font-black text-slate-900">Telefone</p>
                                    <p class="mt-1 leading-6">{{ $store->support_phone }}</p>
                                </div>
                            @endif
                            @if($store->whatsapp)
                                <div>
                                    <p class="font-black text-slate-900">WhatsApp</p>
                                    <p class="mt-1 leading-6">{{ $store->whatsapp }}</p>
                                </div>
                            @endif
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
@endsection
