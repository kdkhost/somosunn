@extends('layouts.app')

@section('title', 'Pagamento - ' . ($event?->title ?? 'Evento'))

@section('content')
@php
    $isPaid = $order->status === 'paid';
@endphp

<div class="min-h-screen bg-slate-50 pt-6 md:pt-28 pb-20 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $isPaid ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800' }}">
                    <i class="fas {{ $isPaid ? 'fa-circle-check' : 'fa-hourglass-half' }} text-xl"></i>
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl font-black text-gray-900">
                        {{ $isPaid ? 'Pagamento aprovado' : 'Pagamento em processamento' }}
                    </h1>
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

            @if(!$isPaid)
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-xl mt-6">
                    <i class="fas fa-info-circle mr-2"></i>
                    Seu pagamento ainda não foi confirmado. Esta página atualiza automaticamente.
                </div>

                <script>
                    setTimeout(() => window.location.reload(), 6000);
                </script>
            @else
                <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl mt-6">
                    <i class="fas fa-ticket-alt mr-2"></i>
                    Sua vaga está confirmada. Guarde o número do pedido para referência.
                </div>
                @include('events.partials.group-access', [
                    'event' => $event,
                    'registration' => $registration,
                    'buttonClass' => 'w-full rounded-xl bg-green-600 px-6 py-3 text-center font-bold text-white hover:bg-green-700 sm:w-auto',
                    'wrapClass' => 'mt-4',
                ])
            @endif

            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                @if($event)
                    <a href="{{ route('events.show', $event) }}" class="btn-primary px-6 py-3 rounded-xl font-bold text-center">
                        Ver evento
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
