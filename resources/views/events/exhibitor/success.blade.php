@extends('layouts.app')

@section('title', 'Área confirmada - ' . $event->title)

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-20 pt-6 md:pt-28">
    <div class="mx-auto max-w-3xl">
        <div class="rounded-2xl bg-white p-8 shadow-lg">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                    <i class="fas fa-circle-check text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-slate-950">Área de expositor confirmada</h1>
                    <p class="mt-2 text-slate-600">Pedido #{{ $order->id }} confirmado para {{ $event->title }}.</p>
                </div>
            </div>

            <div class="mt-8 grid gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-5 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-slate-500">Marca</p>
                    <p class="mt-1 font-bold text-slate-950">{{ $registration?->brand_name ?: $registration?->company_name }}</p>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-slate-500">Quantidade</p>
                    <p class="mt-1 font-bold text-slate-950">{{ (int) ($registration?->quantity ?? 1) }} área(s)</p>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-slate-500">Lote</p>
                    <p class="mt-1 font-bold text-slate-950">{{ $registration?->batch_label }}</p>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-slate-500">Valor</p>
                    <p class="mt-1 font-bold text-slate-950">{{ 'R$ ' . number_format((float) ($registration?->total_price ?? $order->total_amount), 2, ',', '.') }}</p>
                </div>
            </div>

            @if($event->exhibitor_includes_ticket)
                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 font-bold text-emerald-800">
                    <i class="fas fa-ticket-alt mr-2"></i> Este pacote inclui ingresso do expositor como benefício.
                </div>
            @endif

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('events.show', $event) }}" class="btn-primary rounded-xl px-6 py-3 text-center font-bold text-white">
                    Ver evento
                </a>
                @auth
                    <a href="{{ route('panel.dashboard') }}" class="rounded-xl border border-slate-200 px-6 py-3 text-center font-bold text-slate-700 hover:bg-slate-50">
                        Acessar painel
                    </a>
                @else
                    <a href="{{ route('events.index') }}" class="rounded-xl border border-slate-200 px-6 py-3 text-center font-bold text-slate-700 hover:bg-slate-50">
                        Ver outros eventos
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
