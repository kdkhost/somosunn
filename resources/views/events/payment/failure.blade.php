@extends('layouts.app')

@section('title', 'Pagamento não concluído - ' . ($event?->title ?? 'Evento'))

@section('content')
@php
    $retryUrl = data_get($order->metadata, 'mercadopago_init_point') ?? data_get($order->metadata, 'mercadopago_sandbox_init_point');
@endphp

<div class="min-h-screen bg-slate-50 pt-6 md:pt-28 pb-20 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-red-100 text-red-700">
                    <i class="fas fa-circle-xmark text-xl"></i>
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl font-black text-gray-900">Pagamento não concluído</h1>
                    <p class="text-gray-600 mt-1">
                        Pedido #{{ $order->id }} • Status: <span class="font-semibold">{{ $order->status }}</span>
                    </p>
                </div>
            </div>

            @if($event)
                <div class="border-t border-gray-100 mt-6 pt-6">
                    <p class="font-bold text-gray-900">{{ $event->title }}</p>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-calendar-alt mr-1"></i> {{ optional($event->start_at)->format('d/m/Y H:i') }}
                    </p>
                </div>
            @endif

            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mt-6">
                <i class="fas fa-triangle-exclamation mr-2"></i>
                O provedor informou falha ou cancelamento. Você pode tentar novamente.
            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                @if($retryUrl)
                    <a href="{{ $retryUrl }}" class="btn-primary px-6 py-3 rounded-xl font-bold text-center">
                        Tentar novamente
                    </a>
                @endif
                @if($event)
                    <a href="{{ route('events.checkout', $event) }}" class="px-6 py-3 rounded-xl font-bold text-center border border-gray-200 text-gray-700 hover:bg-gray-50">
                        Voltar ao checkout
                    </a>
                @endif
                <a href="{{ route('events.index') }}" class="px-6 py-3 rounded-xl font-bold text-center border border-gray-200 text-gray-700 hover:bg-gray-50">
                    Ver outros eventos
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
