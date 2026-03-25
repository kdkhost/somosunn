@extends('layouts.app')

@section('title', ($store->brand_name ?: 'Loja') . ' - UNN')

@section('content')
    <div class="min-h-screen bg-slate-50 pt-28 pb-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="h-56 w-full" style="background: linear-gradient(135deg, {{ $store->primary_color ?: '#1F5EDB' }}, {{ $store->accent_color ?: '#0F172A' }});">
                    @if($store->banner_url)
                        <img src="{{ $store->banner_url }}" alt="{{ $store->brand_name }}" class="h-full w-full object-cover">
                    @endif
                </div>
                <div class="px-6 pb-8 pt-6 md:px-10 md:pt-8">
                    <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-start gap-5">
                            <div class="h-24 w-24 overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                                @if($store->logo_url)
                                    <img src="{{ $store->logo_url }}" alt="{{ $store->brand_name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-3xl text-slate-300"><i class="fas fa-store"></i></div>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Loja oficial</p>
                                <h1 class="mt-2 text-3xl font-black text-slate-900">{{ $store->brand_name }}</h1>
                                @if($store->tagline)
                                    <p class="mt-2 text-sm font-semibold text-slate-500">{{ $store->tagline }}</p>
                                @endif
                                @if($store->bio)
                                    <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-600">{{ $store->bio }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('seller-products.cart.show') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition"><i class="fas fa-cart-shopping text-slate-400"></i> Carrinho</a>
                            @if($store->instagram_url)
                                <a href="{{ $store->instagram_url }}" target="_blank" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition"><i class="fab fa-instagram text-pink-500"></i> Instagram</a>
                            @endif
                            @if($store->website_url)
                                <a href="{{ $store->website_url }}" target="_blank" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 hover:brightness-110 transition"><i class="fas fa-globe"></i> Site</a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            @if($products->isNotEmpty())
                <section class="mt-10">
                    <div class="mb-5 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Produtos proprios</p>
                            <h2 class="mt-2 text-3xl font-black text-slate-900">Produtos da loja</h2>
                        </div>
                        <a href="{{ route('seller-products.cart.show') }}" class="text-sm font-black text-blue-700 hover:text-blue-800">Ver carrinho</a>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($products as $product)
                            <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                                <a href="{{ route('seller-stores.products.show', [$store->slug, $product->slug]) }}" class="block overflow-hidden rounded-[1.5rem] bg-slate-100 aspect-[16/10]">
                                    @if($product->cover_url)
                                        <img src="{{ $product->cover_url }}" alt="{{ $product->title }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-4xl text-slate-300"><i class="fas fa-box"></i></div>
                                    @endif
                                </a>
                                <div class="mt-4">
                                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">{{ strtoupper($product->type) }}</p>
                                    <h3 class="mt-2 text-xl font-black text-slate-900">{{ $product->title }}</h3>
                                    <p class="mt-2 text-sm text-slate-600 line-clamp-3">{{ $product->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 110) }}</p>
                                </div>
                                <div class="mt-5 flex items-center justify-between gap-4 border-t border-slate-100 pt-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Preco</p>
                                        <p class="text-2xl font-black text-slate-900">R$ {{ number_format((float) $product->effective_price, 2, ',', '.') }}</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <form action="{{ route('seller-products.cart.add', $product) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">Carrinho</button>
                                        </form>
                                        <form action="{{ route('seller-products.cart.add', $product) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="buy_now" value="1">
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 hover:brightness-110 transition">Comprar</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="mt-10 grid gap-8 lg:grid-cols-3">
                <section class="lg:col-span-2 space-y-8">
                    @if($courses->isNotEmpty())
                        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="text-2xl font-black text-slate-900">Cursos</h2>
                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                @foreach($courses as $course)
                                    <a href="{{ route('courses.show', $course->slug ?: $course->id) }}" class="rounded-3xl border border-slate-100 bg-slate-50 p-5 hover:border-blue-200 hover:bg-blue-50 transition">
                                        <p class="text-lg font-black text-slate-900">{{ $course->title }}</p>
                                        <p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags((string) $course->short_description), 110) }}</p>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($mentorships->isNotEmpty())
                        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="text-2xl font-black text-slate-900">Mentorias</h2>
                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                @foreach($mentorships as $mentorship)
                                    <a href="{{ route('mentorships.show', $mentorship) }}" class="rounded-3xl border border-slate-100 bg-slate-50 p-5 hover:border-blue-200 hover:bg-blue-50 transition">
                                        <p class="text-lg font-black text-slate-900">{{ $mentorship->title }}</p>
                                        <p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags((string) $mentorship->description), 110) }}</p>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($events->isNotEmpty())
                        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="text-2xl font-black text-slate-900">Eventos</h2>
                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                @foreach($events as $event)
                                    <a href="{{ route('events.show', $event) }}" class="rounded-3xl border border-slate-100 bg-slate-50 p-5 hover:border-blue-200 hover:bg-blue-50 transition">
                                        <p class="text-lg font-black text-slate-900">{{ $event->title }}</p>
                                        <p class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags((string) $event->description), 110) }}</p>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>

                <aside class="space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Atendimento</p>
                        <div class="mt-4 space-y-3 text-sm text-slate-600">
                            @if($store->support_email)<p><strong>E-mail:</strong> {{ $store->support_email }}</p>@endif
                            @if($store->support_phone)<p><strong>Telefone:</strong> {{ $store->support_phone }}</p>@endif
                            @if($store->whatsapp)<p><strong>WhatsApp:</strong> {{ $store->whatsapp }}</p>@endif
                        </div>
                    </div>
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Sobre a loja</p>
                        <p class="mt-4 text-sm leading-7 text-slate-600">Todos os produtos desta vitrine usam o mesmo fluxo de split, pagamento e controle do marketplace da plataforma.</p>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection
