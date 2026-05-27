@extends('layouts.app')

@section('title', 'Pagamento não aprovado - ' . $event->title)

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-20 pt-6 md:pt-28">
    <div class="mx-auto max-w-3xl">
        <div class="rounded-2xl bg-white p-8 shadow-lg">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100 text-red-700">
                    <i class="fas fa-triangle-exclamation text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-slate-950">Pagamento não aprovado</h1>
                    <p class="mt-2 text-slate-600">Não foi possível confirmar o pagamento da área para expositor.</p>
                </div>
            </div>

            <div class="mt-8 rounded-2xl border border-red-200 bg-red-50 p-5 text-red-800">
                <p class="font-bold">Pedido #{{ $order->id }}</p>
                <p class="mt-1 text-sm">Se a cobrança aparecer no seu banco, aguarde a confirmação do gateway ou entre em contato com o suporte do evento.</p>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('events.exhibitor.show', $event) }}" class="btn-primary rounded-xl px-6 py-3 text-center font-bold text-white">
                    Tentar novamente
                </a>
                <a href="{{ route('events.show', $event) }}" class="rounded-xl border border-slate-200 px-6 py-3 text-center font-bold text-slate-700 hover:bg-slate-50">
                    Ver evento
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
