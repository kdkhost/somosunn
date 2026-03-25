@extends('panel.layouts.app')

@section('title', 'Produtos da loja - UNN')

@section('panel_content')
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Catalogo proprio</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">Produtos da minha loja</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Cadastre produtos fisicos e digitais para vender na sua storefront e no marketplace.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('panel.marketplace.store.edit') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 transition">
                    <i class="fas fa-store text-slate-400"></i> Minha loja
                </a>
                <a href="{{ route('panel.marketplace.products.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 hover:brightness-110 transition">
                    <i class="fas fa-plus"></i> Novo produto
                </a>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse($products as $product)
                <article class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm">
                    <div class="aspect-[16/10] rounded-3xl overflow-hidden bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                        @if($product->cover_url)
                            <img src="{{ $product->cover_url }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-image text-4xl text-slate-300"></i>
                        @endif
                    </div>
                    <div class="mt-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ $product->title }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ ucfirst($product->type) }} ? {{ strtoupper($product->status) }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-black {{ $product->status === 'published' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">{{ $product->status === 'published' ? 'Publicado' : 'Rascunho' }}</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300 line-clamp-3">{{ $product->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 110) }}</p>
                    <div class="mt-4 flex items-end justify-between gap-3 border-t border-slate-100 dark:border-slate-800 pt-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Preco</p>
                            <p class="text-xl font-black text-slate-900 dark:text-white">R$ {{ number_format((float) $product->effective_price, 2, ',', '.') }}</p>
                        </div>
                        <div class="flex gap-2">
                            @if($store->slug)
                                <a href="{{ route('seller-stores.products.show', [$store->slug, $product->slug]) }}" target="_blank" class="inline-flex items-center justify-center w-11 h-11 rounded-2xl border border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 transition"><i class="fas fa-eye"></i></a>
                            @endif
                            <a href="{{ route('panel.marketplace.products.edit', $product) }}" class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/20 hover:brightness-110 transition"><i class="fas fa-pen"></i></a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="md:col-span-2 xl:col-span-3 bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-700 p-10 text-center">
                    <i class="fas fa-box-open text-4xl text-slate-300"></i>
                    <h2 class="mt-4 text-xl font-black text-slate-900 dark:text-white">Nenhum produto cadastrado</h2>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Comece criando o primeiro produto fisico ou digital da sua loja.</p>
                </div>
            @endforelse
        </div>

        <div>
            {{ $products->links() }}
        </div>
    </div>
@endsection
