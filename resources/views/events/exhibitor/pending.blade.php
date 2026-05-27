@extends('layouts.app')

@section('title', 'Pagamento pendente - ' . $event->title)

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-20 pt-6 md:pt-28">
    <div class="mx-auto max-w-3xl">
        <div class="rounded-2xl bg-white p-8 shadow-lg">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-yellow-100 text-yellow-800">
                    <i class="fas fa-hourglass-half text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-slate-950">Pagamento em processamento</h1>
                    <p class="mt-2 text-slate-600">Sua área para expositor está reservada temporariamente enquanto o pagamento é confirmado.</p>
                </div>
            </div>

            <div class="mt-8 rounded-2xl border border-yellow-200 bg-yellow-50 p-5 text-yellow-900">
                <p class="font-bold">Pedido #{{ $order->id }}</p>
                @if($registration?->reserve_expires_at)
                    <p class="mt-1 text-sm">Reserva válida até {{ $registration->reserve_expires_at->format('d/m/Y H:i') }}.</p>
                @endif
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('events.show', $event) }}" class="btn-primary rounded-xl px-6 py-3 text-center font-bold text-white">
                    Ver evento
                </a>
                <a href="{{ route('events.exhibitor.show', $event) }}" class="rounded-xl border border-slate-200 px-6 py-3 text-center font-bold text-slate-700 hover:bg-slate-50">
                    Voltar ao formulário
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
