@extends('layouts.app')

@section('title', 'Escolha como pagar - ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">

        {{-- Header --}}
        <div class="text-center mb-10">
            <h1 class="text-3xl font-extrabold text-slate-900 sm:text-4xl">
                Escolha como pagar
            </h1>
            <p class="mt-3 text-lg text-slate-500">
                Selecione o método de pagamento de sua preferência para finalizar a compra.
            </p>
        </div>

        {{-- Resumo do pedido --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500 uppercase tracking-wider font-semibold">Pedido</p>
                    <h3 class="text-lg font-bold text-slate-900 mt-1">{{ $event->title }}</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ optional($event->start_at)->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-500 uppercase tracking-wider font-semibold">Total</p>
                    <p class="text-2xl font-extrabold text-slate-900">
                        R$ {{ number_format($order->total_amount, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Cards de gateway --}}
        <div class="grid grid-cols-1 sm:grid-cols-{{ count($gatewayOptions) }} gap-4 mb-8">
            @foreach($gatewayOptions as $gw)
                <form action="{{ route('events.payment.process-gateway', $order->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="gateway" value="{{ $gw['provider'] }}">
                    <button type="submit"
                        class="w-full group relative overflow-hidden rounded-2xl border-2 border-slate-200 bg-white p-6 text-left transition-all duration-200
                               hover:border-{{ $gw['color'] }}-400 hover:shadow-lg hover:shadow-{{ $gw['color'] }}-100/50 hover:-translate-y-1
                               focus:outline-none focus:ring-4 focus:ring-{{ $gw['color'] }}-200">

                        {{-- Ícone --}}
                        <div class="w-14 h-14 rounded-2xl bg-{{ $gw['color'] }}-50 flex items-center justify-center mb-4 group-hover:bg-{{ $gw['color'] }}-100 transition-colors">
                            <i class="{{ $gw['icon'] }} text-2xl text-{{ $gw['color'] }}-600"></i>
                        </div>

                        {{-- Nome --}}
                        <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $gw['name'] }}</h3>

                        {{-- Descrição --}}
                        <p class="text-sm text-slate-500 mb-4">{{ $gw['description'] }}</p>

                        {{-- Métodos disponíveis --}}
                        <div class="flex flex-wrap gap-2">
                            @foreach($gw['methods'] as $method)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                    @if(str_contains($method, 'Cartão'))
                                        <i class="fas fa-credit-card text-[10px]"></i>
                                    @elseif(str_contains($method, 'PIX'))
                                        <i class="fa-brands fa-pix text-[10px]"></i>
                                    @elseif(str_contains($method, 'Boleto'))
                                        <i class="fas fa-barcode text-[10px]"></i>
                                    @elseif(str_contains($method, 'Débito'))
                                        <i class="fas fa-money-check-alt text-[10px]"></i>
                                    @endif
                                    {{ $method }}
                                </span>
                            @endforeach
                        </div>

                        {{-- Seta --}}
                        <div class="absolute top-6 right-6 text-slate-300 group-hover:text-{{ $gw['color'] }}-500 transition-colors">
                            <i class="fas fa-arrow-right text-lg"></i>
                        </div>
                    </button>
                </form>
            @endforeach
        </div>

        {{-- Segurança --}}
        <div class="flex items-center justify-center space-x-4 text-slate-400 text-sm">
            <div class="flex items-center">
                <i class="fas fa-lock mr-2 text-green-500"></i>
                <span>Ambiente Criptografado</span>
            </div>
            <span>&bull;</span>
            <div class="flex items-center">
                <i class="fas fa-shield-alt mr-2 text-green-500"></i>
                <span>Compra Garantida</span>
            </div>
        </div>

    </div>
</div>
@endsection
