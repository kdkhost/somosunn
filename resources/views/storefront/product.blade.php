@extends('layouts.app')

@section('title', $product->title . ' - ' . $store->brand_name)

@section('content')
    <div class="min-h-screen bg-slate-50 pt-28 pb-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <a href="{{ route('seller-stores.show', $store->slug) }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-blue-700 transition"><i class="fas fa-arrow-left"></i> Voltar para a loja</a>

            <div class="mt-6 grid gap-8 lg:grid-cols-[1.05fr,0.95fr]">
                <div class="space-y-4">
                    <div class="aspect-[16/11] overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        @if($product->cover_url)
                            <img src="{{ $product->cover_url }}" alt="{{ $product->title }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-5xl text-slate-300"><i class="fas fa-box"></i></div>
                        @endif
                    </div>
                    @if($product->media->isNotEmpty())
                        <div class="grid gap-3 sm:grid-cols-3">
                            @foreach($product->media as $media)
                                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                                    @if($media->media_type === 'video')
                                        <video src="{{ $media->file_url }}" controls class="h-32 w-full object-cover"></video>
                                    @else
                                        <img src="{{ $media->file_url }}" alt="{{ $product->title }}" class="h-32 w-full object-cover">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 md:p-8 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">{{ strtoupper($product->type) }}</p>
                    <h1 class="mt-3 text-3xl font-black text-slate-900">{{ $product->title }}</h1>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $product->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 160) }}</p>

                    <div class="mt-6 rounded-3xl bg-slate-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Preco</p>
                        <p class="mt-2 text-4xl font-black text-slate-900">R$ {{ number_format((float) $product->effective_price, 2, ',', '.') }}</p>
                        @if($product->type === 'physical')
                            <p class="mt-3 text-sm text-slate-500">Frete calculado no checkout via Correios.</p>
                        @else
                            <p class="mt-3 text-sm text-slate-500">Entrega digital liberada apos a aprovacao do pagamento.</p>
                        @endif
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <form action="{{ route('seller-products.cart.add', $product) }}" method="POST" class="space-y-3">
                            @csrf
                            <input type="number" min="1" value="1" name="quantity" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900">
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition"><i class="fas fa-cart-shopping"></i> Adicionar ao carrinho</button>
                        </form>
                        <form action="{{ route('seller-products.cart.add', $product) }}" method="POST" class="space-y-3">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="buy_now" value="1">
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 hover:brightness-110 transition"><i class="fas fa-bolt"></i> Comprar agora</button>
                        </form>
                    </div>

                    <div class="mt-8 border-t border-slate-100 pt-6 space-y-3 text-sm text-slate-600">
                        <p><strong>Anunciado e vendido por:</strong> {{ $store->brand_name }}</p>
                        @if($product->sku)<p><strong>SKU:</strong> {{ $product->sku }}</p>@endif
                        @if($product->type === 'physical')<p><strong>Estoque:</strong> {{ (int) ($product->stock ?? 0) }}</p>@endif
                    </div>
                </div>
            </div>

            <div class="mt-10 rounded-[2rem] border border-slate-200 bg-white p-6 md:p-8 shadow-sm">
                <h2 class="text-2xl font-black text-slate-900">Descricao completa</h2>
                <div class="prose prose-slate max-w-none mt-4">{!! $product->description ?: '<p>Sem descricao detalhada cadastrada.</p>' !!}</div>
            </div>
        </div>
    </div>
@endsection
