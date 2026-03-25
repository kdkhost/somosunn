@extends('layouts.app')

@section('title', 'Carrinho da loja - UNN')

@section('content')
    <div class="min-h-screen bg-slate-50 pt-28 pb-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Loja virtual</p>
                    <h1 class="mt-2 text-3xl font-black text-slate-900">Carrinho do vendedor</h1>
                </div>
                <a href="{{ route('marketplace.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-blue-700 transition"><i class="fas fa-arrow-left"></i> Voltar ao marketplace</a>
            </div>

            @if(session('cart_replace_candidate'))
                <div class="mt-6 rounded-3xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
                    <p class="font-black">Seu carrinho atual pertence a outro vendedor.</p>
                    <p class="mt-2 text-sm">Se quiser trocar o carrinho atual, confirme abaixo.</p>
                    <form action="{{ session('cart_replace_candidate.add_url') }}" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="replace" value="1">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-amber-600 px-4 py-3 text-sm font-black text-white hover:brightness-110 transition">
                            <i class="fas fa-repeat"></i> Substituir carrinho pelo produto {{ session('cart_replace_candidate.title') }}
                        </button>
                    </form>
                </div>
            @endif

            <div class="mt-6 grid gap-8 lg:grid-cols-[1.25fr,0.75fr]">
                <section>
                    <form action="{{ route('seller-products.cart.update') }}" method="POST" class="space-y-4">
                        @csrf
                        @forelse($items as $row)
                            @php($product = $row['product'])
                            <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="h-24 w-24 overflow-hidden rounded-3xl bg-slate-100 flex items-center justify-center">
                                        @if($product->cover_url)
                                            <img src="{{ $product->cover_url }}" alt="{{ $product->title }}" class="h-full w-full object-cover">
                                        @else
                                            <i class="fas fa-box text-3xl text-slate-300"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h2 class="text-lg font-black text-slate-900 truncate">{{ $product->title }}</h2>
                                        <p class="mt-1 text-sm text-slate-500">{{ ucfirst($product->type) }}</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-700">R$ {{ number_format((float) $row['unit_price'], 2, ',', '.') }} cada</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <input type="number" min="0" name="quantities[{{ $product->id }}]" value="{{ $row['quantity'] }}" class="w-24 rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900">
                                    <div class="text-right">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Subtotal</p>
                                        <p class="text-lg font-black text-slate-900">R$ {{ number_format((float) $row['subtotal'], 2, ',', '.') }}</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[2rem] border border-dashed border-slate-200 bg-white p-10 text-center text-slate-500">
                                Seu carrinho esta vazio.
                            </div>
                        @endforelse

                        @if($items->isNotEmpty())
                            <div class="flex flex-wrap gap-3">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition"><i class="fas fa-arrows-rotate"></i> Atualizar carrinho</button>
                            </div>
                        @endif
                    </form>
                </section>

                <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm h-fit lg:sticky lg:top-28">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Resumo</p>
                    <div class="mt-5 space-y-3 text-sm text-slate-600">
                        <div class="flex items-center justify-between"><span>Produtos</span><strong>R$ {{ number_format((float) $subtotal, 2, ',', '.') }}</strong></div>
                        <div class="flex items-center justify-between"><span>Frete</span><strong>{{ $has_physical ? 'Calculado no checkout' : 'Nao se aplica' }}</strong></div>
                    </div>
                    <div class="mt-5 border-t border-slate-100 pt-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total parcial</p>
                        <p class="mt-2 text-3xl font-black text-slate-900">R$ {{ number_format((float) $subtotal, 2, ',', '.') }}</p>
                    </div>
                    <div class="mt-6 space-y-3">
                        <a href="{{ route('seller-products.checkout.show') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 hover:brightness-110 transition"><i class="fas fa-lock"></i> Ir para o checkout</a>
                        <form action="{{ route('seller-products.cart.clear') }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition"><i class="fas fa-trash"></i> Limpar carrinho</button>
                        </form>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection
