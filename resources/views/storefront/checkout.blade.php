@extends('layouts.app')

@section('title', 'Checkout da loja - UNN')

@section('content')
    <div class="min-h-screen bg-slate-50 pt-28 pb-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Checkout</p>
                    <h1 class="mt-2 text-3xl font-black text-slate-900">Finalizar compra na loja {{ $sellerStore->brand_name }}</h1>
                    <p class="mt-2 text-sm text-slate-500">Pagamento com split do marketplace e entrega vinculada ao vendedor responsavel.</p>
                </div>
                <a href="{{ route('seller-products.cart.show') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-blue-700 transition"><i class="fas fa-arrow-left"></i> Voltar ao carrinho</a>
            </div>

            <form action="{{ route('seller-products.checkout.process') }}" method="POST" class="mt-6 grid gap-8 lg:grid-cols-[1.1fr,0.9fr]">
                @csrf
                <section class="space-y-6">
                    @if($has_physical = ($totals['has_physical'] ?? false))
                        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="text-xl font-black text-slate-900">Entrega e frete</h2>
                            <div class="mt-5 grid gap-4 md:grid-cols-2">
                                <input type="text" name="recipient_name" value="{{ old('recipient_name', $shippingAddress['recipient_name']) }}" placeholder="Nome de quem recebe" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900">
                                <input type="email" name="recipient_email" value="{{ old('recipient_email', $shippingAddress['recipient_email']) }}" placeholder="E-mail" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900">
                                <input type="text" name="recipient_phone" value="{{ old('recipient_phone', $shippingAddress['recipient_phone']) }}" placeholder="Telefone" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900">
                                <input type="text" name="postal_code" value="{{ old('postal_code', $shippingAddress['postal_code']) }}" placeholder="CEP" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900">
                                <input type="text" name="address_line" value="{{ old('address_line', $shippingAddress['address_line']) }}" placeholder="Endereco" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 md:col-span-2">
                                <input type="text" name="number" value="{{ old('number', $shippingAddress['number']) }}" placeholder="Numero" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900">
                                <input type="text" name="complement" value="{{ old('complement', $shippingAddress['complement']) }}" placeholder="Complemento" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900">
                                <input type="text" name="neighborhood" value="{{ old('neighborhood', $shippingAddress['neighborhood']) }}" placeholder="Bairro" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900">
                                <input type="text" name="city" value="{{ old('city', $shippingAddress['city']) }}" placeholder="Cidade" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900">
                                <input type="text" name="state" value="{{ old('state', $shippingAddress['state']) }}" placeholder="UF" class="w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900">
                            </div>

                            @if($shippingError)
                                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">{{ $shippingError }}</div>
                            @endif

                            @if($quotes)
                                <div class="mt-5 space-y-3">
                                    @foreach($quotes as $quote)
                                        <label class="flex items-center justify-between gap-4 rounded-3xl border border-slate-200 px-4 py-4 cursor-pointer hover:border-blue-300 transition">
                                            <div class="flex items-center gap-3">
                                                <input type="radio" name="shipping_service_code" value="{{ $quote['service_code'] }}" {{ old('shipping_service_code') === $quote['service_code'] ? 'checked' : '' }}>
                                                <div>
                                                    <p class="font-black text-slate-900">{{ $quote['service_name'] }}</p>
                                                    <p class="text-sm text-slate-500">Prazo estimado: {{ $quote['delivery_days'] }} dia(s)</p>
                                                </div>
                                            </div>
                                            <div class="text-lg font-black text-slate-900">R$ {{ number_format((float) $quote['amount'], 2, ',', '.') }}</div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-xl font-black text-slate-900">Forma de pagamento</h2>
                        <input type="hidden" name="gateway_provider" value="mercadopago">
                        <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                            <p class="font-black text-slate-900">Mercado Pago</p>
                            <p class="mt-2 text-sm text-slate-500">Checkout com cartao e Pix, reaproveitando a mesma logica financeira do marketplace.</p>
                            @unless($mpEnabled)
                                <p class="mt-3 text-sm font-semibold text-red-600">O vendedor ainda nao configurou o gateway de pagamento.</p>
                            @endunless
                        </div>
                    </div>
                </section>

                <aside class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm h-fit lg:sticky lg:top-28">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Resumo do pedido</p>
                    <div class="mt-5 space-y-3">
                        @foreach($totals['items'] as $row)
                            <div class="flex items-center justify-between gap-4 text-sm text-slate-600">
                                <span>{{ $row['product']->title }} x{{ $row['quantity'] }}</span>
                                <strong class="text-slate-900">R$ {{ number_format((float) $row['subtotal'], 2, ',', '.') }}</strong>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-5 border-t border-slate-100 pt-5 space-y-3 text-sm text-slate-600">
                        <div class="flex items-center justify-between"><span>Subtotal</span><strong class="text-slate-900">R$ {{ number_format((float) $totals['subtotal'], 2, ',', '.') }}</strong></div>
                        <div class="flex items-center justify-between"><span>Frete</span><strong class="text-slate-900">{{ $totals['has_physical'] ? 'Definido na selecao acima' : 'Nao se aplica' }}</strong></div>
                    </div>
                    <button type="submit" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 hover:brightness-110 transition">
                        <i class="fas fa-lock"></i> Continuar para pagamento
                    </button>
                </aside>
            </form>
        </div>
    </div>
@endsection
