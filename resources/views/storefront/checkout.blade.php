@extends('layouts.app')

@section('title', 'Checkout - ' . $sellerStore->brand_name . ' - UNN')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-50 to-white pt-24 pb-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <nav class="mb-3 flex items-center gap-2 text-xs">
                    <a href="{{ route('seller-products.cart.show') }}" class="inline-flex items-center gap-1.5 text-slate-500 hover:text-blue-700 transition">
                        <i class="fas fa-arrow-left text-[10px]"></i> Voltar ao carrinho
                    </a>
                </nav>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-400">Checkout</p>
                <h1 class="mt-1 text-3xl font-black text-slate-900">Finalizar compra</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Loja: <strong class="text-slate-700">{{ $sellerStore->brand_name }}</strong>
                </p>
            </div>
        </div>

        {{-- Flash errors --}}
        @if(session('error'))
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 flex items-start gap-3">
                <i class="fas fa-triangle-exclamation text-red-500 mt-0.5"></i>
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-triangle-exclamation text-red-500 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-bold text-red-700">Corrija os erros abaixo:</p>
                        <ul class="mt-1 text-sm text-red-600 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @php
            // Montar lista de gateways disponíveis
            $availableGateways = [];

            if ($mpEnabled) {
                // Ler métodos MP ativos do banco
                $mpMethods = [];
                if ((int) \App\Models\Setting::get('mercadopago_method_credit_card', 1) === 1) {
                    $mpMethods[] = 'Cartão de crédito';
                }
                if ((int) \App\Models\Setting::get('mercadopago_method_debit_card', 0) === 1) {
                    $mpMethods[] = 'Cartão de débito';
                }
                if ((int) \App\Models\Setting::get('mercadopago_method_pix', 1) === 1) {
                    $mpMethods[] = 'Pix';
                }
                if ((int) \App\Models\Setting::get('mercadopago_method_ticket', 0) === 1) {
                    $mpMethods[] = 'Boleto';
                }
                if ((int) \App\Models\Setting::get('mercadopago_method_mercadopago', 0) === 1) {
                    $mpMethods[] = 'Carteira MP';
                }

                // Se nenhum método foi configurado, não mostrar MP
                if (!empty($mpMethods)) {
                    $availableGateways['mercadopago'] = [
                        'id' => 'mercadopago',
                        'label' => 'Mercado Pago',
                        'description' => implode(', ', $mpMethods),
                        'logo' => asset('img/gateways/mercadopago.svg'),
                        'methods' => $mpMethods,
                    ];
                }
            }

            if ($sumupAvailable) {
                // Ler métodos SumUp ativos
                $sumupMethods = [];
                if ((int) \App\Models\Setting::get('sumup_method_card', 1) === 1) {
                    $sumupMethods[] = 'Cartão';
                }
                if ((int) \App\Models\Setting::get('sumup_method_pix', 1) === 1) {
                    $sumupMethods[] = 'Pix';
                }

                if (!empty($sumupMethods)) {
                    $availableGateways['sumup'] = [
                        'id' => 'sumup',
                        'label' => 'SumUp',
                        'description' => implode(' ou ', $sumupMethods) . ' via SumUp',
                        'logo' => asset('img/gateways/sumup.svg'),
                        'methods' => $sumupMethods,
                    ];
                }
            }

            $gatewayCount = count($availableGateways);
            $defaultGateway = $gatewayCount > 0 ? array_key_first($availableGateways) : null;
        @endphp

        <form action="{{ route('seller-products.checkout.process') }}" method="POST" class="grid gap-8 lg:grid-cols-[1.15fr,0.85fr]">
            @csrf

            {{-- Coluna esquerda: dados de entrega + pagamento --}}
            <section class="space-y-6">

                {{-- Entrega (só se tiver produto físico) --}}
                @if($totals['has_physical'] ?? false)
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 md:p-7 shadow-sm">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                                <i class="fas fa-truck-fast"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-900">Endereço de entrega</h2>
                                <p class="text-xs text-slate-500">Usado para cálculo de frete dos Correios</p>
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1">Nome do destinatário</label>
                                <input type="text" name="recipient_name" value="{{ old('recipient_name', $shippingAddress['recipient_name']) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1">E-mail</label>
                                <input type="email" name="recipient_email" value="{{ old('recipient_email', $shippingAddress['recipient_email']) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1">Telefone</label>
                                <input type="text" name="recipient_phone" value="{{ old('recipient_phone', $shippingAddress['recipient_phone']) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1">CEP</label>
                                <input type="text" name="postal_code" value="{{ old('postal_code', $shippingAddress['postal_code']) }}" placeholder="00000-000"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1">Estado (UF)</label>
                                <input type="text" name="state" value="{{ old('state', $shippingAddress['state']) }}" maxlength="2" placeholder="SP"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition uppercase">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1">Endereço</label>
                                <input type="text" name="address_line" value="{{ old('address_line', $shippingAddress['address_line']) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1">Número</label>
                                <input type="text" name="number" value="{{ old('number', $shippingAddress['number']) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1">Complemento</label>
                                <input type="text" name="complement" value="{{ old('complement', $shippingAddress['complement']) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1">Bairro</label>
                                <input type="text" name="neighborhood" value="{{ old('neighborhood', $shippingAddress['neighborhood']) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1">Cidade</label>
                                <input type="text" name="city" value="{{ old('city', $shippingAddress['city']) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            </div>
                        </div>

                        @if($shippingError)
                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 flex items-start gap-2">
                                <i class="fas fa-triangle-exclamation mt-0.5"></i>
                                <span>{{ $shippingError }}</span>
                            </div>
                        @endif

                        {{-- Opções de frete --}}
                        @if(!empty($quotes))
                            <div class="mt-5 pt-5 border-t border-slate-100">
                                <h3 class="text-sm font-black text-slate-900 mb-3 flex items-center gap-2">
                                    <i class="fas fa-box text-slate-400"></i> Escolha a forma de envio
                                </h3>
                                <div class="space-y-2">
                                    @foreach($quotes as $i => $quote)
                                        <label class="flex items-center justify-between gap-4 rounded-xl border-2 {{ ($i === 0 && !old('shipping_service_code')) ? 'border-blue-400 bg-blue-50' : 'border-slate-200 bg-white' }} px-4 py-3 cursor-pointer hover:border-blue-300 transition">
                                            <div class="flex items-center gap-3">
                                                <input type="radio" name="shipping_service_code" value="{{ $quote['service_code'] }}"
                                                    {{ (old('shipping_service_code', $quotes[0]['service_code'] ?? '') === $quote['service_code']) ? 'checked' : '' }}
                                                    class="w-4 h-4 text-blue-600">
                                                <div>
                                                    <p class="font-black text-slate-900 text-sm">{{ $quote['service_name'] }}</p>
                                                    <p class="text-xs text-slate-500">Prazo: {{ $quote['delivery_days'] }} dia(s) úteis</p>
                                                </div>
                                            </div>
                                            <div class="text-base font-black text-slate-900">R$ {{ number_format((float) $quote['amount'], 2, ',', '.') }}</div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Forma de Pagamento --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-6 md:p-7 shadow-sm">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900">Forma de pagamento</h2>
                            <p class="text-xs text-slate-500">
                                @if($gatewayCount > 1)
                                    Escolha como deseja pagar ({{ $gatewayCount }} opções disponíveis)
                                @elseif($gatewayCount === 1)
                                    Gateway de pagamento do vendedor
                                @else
                                    Nenhum gateway configurado
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($gatewayCount === 0)
                        <div class="rounded-2xl bg-red-50 border border-red-200 p-5 text-center">
                            <i class="fas fa-lock text-3xl text-red-400 mb-2"></i>
                            <p class="font-black text-red-700">Nenhum gateway disponível</p>
                            <p class="text-sm text-red-600 mt-1">O vendedor não configurou um método de pagamento ainda.</p>
                        </div>
                    @elseif($gatewayCount === 1)
                        @php $g = reset($availableGateways); @endphp
                        <input type="hidden" name="gateway_provider" value="{{ $g['id'] }}">
                        <div class="rounded-2xl border-2 border-blue-500 bg-blue-50 p-4">
                            <div class="flex items-start gap-3">
                                <img src="{{ $g['logo'] }}" alt="{{ $g['label'] }}" class="h-12 w-12 flex-shrink-0 rounded-lg">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-black text-slate-900">{{ $g['label'] }}</p>
                                        <i class="fas fa-circle-check text-blue-600 text-xs"></i>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $g['description'] }}</p>
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach($g['methods'] as $method)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white border border-slate-200 text-[11px] font-bold text-slate-600">
                                                <i class="fas fa-check text-emerald-500 text-[8px]"></i>
                                                {{ $method }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Múltiplos gateways --}}
                        <div class="grid gap-3 sm:grid-cols-{{ min($gatewayCount, 2) }}">
                            @foreach($availableGateways as $g)
                                @php $isDefault = $g['id'] === $defaultGateway; @endphp
                                <label class="relative cursor-pointer block">
                                    <input type="radio" name="gateway_provider" value="{{ $g['id'] }}" class="peer sr-only"
                                        {{ old('gateway_provider', $defaultGateway) === $g['id'] ? 'checked' : '' }}>
                                    <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-100 hover:border-slate-300 transition-all h-full">
                                        <div class="flex items-start gap-3">
                                            <img src="{{ $g['logo'] }}" alt="{{ $g['label'] }}" class="h-12 w-12 flex-shrink-0 rounded-lg">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-2">
                                                    <p class="font-black text-slate-900">{{ $g['label'] }}</p>
                                                    <div class="w-4 h-4 rounded-full border-2 border-slate-300 peer-checked:border-blue-500 peer-checked:bg-blue-500 flex-shrink-0"></div>
                                                </div>
                                                <p class="text-xs text-slate-500 mt-0.5 leading-tight">{{ $g['description'] }}</p>
                                                <div class="flex flex-wrap gap-1 mt-2">
                                                    @foreach($g['methods'] as $method)
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-slate-100 text-[10px] font-bold text-slate-600">
                                                            <i class="fas fa-check text-emerald-500 text-[8px]"></i>
                                                            {{ $method }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            {{-- Coluna direita: Resumo do pedido (sticky) --}}
            <aside class="lg:sticky lg:top-24 lg:self-start space-y-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-black text-slate-900 mb-4">Resumo do pedido</h2>

                    {{-- Itens --}}
                    <div class="space-y-3 mb-5">
                        @foreach($totals['items'] as $row)
                            <div class="flex items-start gap-3">
                                <div class="w-14 h-14 rounded-xl bg-slate-100 overflow-hidden flex-shrink-0">
                                    @if($row['product']->cover_url)
                                        <img src="{{ $row['product']->cover_url }}" alt="{{ $row['product']->title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-xl text-slate-300">
                                            <i class="fas fa-box"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-900 line-clamp-2">{{ $row['product']->title }}</p>
                                    <p class="text-xs text-slate-500">Qtde: {{ $row['quantity'] }} • R$ {{ number_format((float) $row['unit_price'], 2, ',', '.') }}</p>
                                </div>
                                <div class="text-sm font-black text-slate-900">
                                    R$ {{ number_format((float) $row['subtotal'], 2, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Totais --}}
                    <div class="border-t border-slate-100 pt-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Subtotal</span>
                            <span class="font-bold text-slate-900">R$ {{ number_format((float) $totals['subtotal'], 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Frete</span>
                            <span class="font-bold text-slate-900">
                                @if($totals['has_physical'] ?? false)
                                    <span class="text-slate-500 text-xs">Selecione acima</span>
                                @else
                                    Não se aplica
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between pt-3 border-t border-slate-100">
                            <span class="font-black text-slate-900">Total</span>
                            <span class="text-xl font-black text-slate-900">R$ {{ number_format((float) $totals['subtotal'], 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <button type="submit"
                        class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 px-4 py-3.5 text-sm font-black text-white shadow-lg shadow-blue-500/20 hover:brightness-110 transition-all active:scale-[0.98] {{ $gatewayCount === 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ $gatewayCount === 0 ? 'disabled' : '' }}>
                        <i class="fas fa-lock"></i>
                        Finalizar compra
                    </button>

                    <p class="mt-3 text-center text-[11px] text-slate-400">
                        <i class="fas fa-shield-halved"></i> Pagamento seguro com split automático
                    </p>
                </div>

                <a href="{{ route('seller-products.cart.show') }}" class="block text-center text-sm font-bold text-slate-500 hover:text-blue-700 py-2 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Alterar carrinho
                </a>
            </aside>
        </form>
    </div>
</div>
@endsection
