@extends('panel.layouts.app')

@section('title', 'Meus Ingressos')

@section('panel_breadcrumb')
    <a href="{{ route('panel.tickets.index') }}" class="hover:underline">Ingressos</a>
@endsection

@push('styles')
    <style>
        .member-ticket-card {
            position: relative;
            isolation: isolate;
            overflow: hidden;
        }

        .member-ticket-card::before,
        .member-ticket-card::after {
            content: "";
            position: absolute;
            top: 10rem;
            z-index: 2;
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 999px;
            background: rgb(248 250 252);
            border: 1px solid rgb(226 232 240);
        }

        .dark .member-ticket-card::before,
        .dark .member-ticket-card::after {
            background: rgb(2 6 23);
            border-color: rgb(30 41 59);
        }

        .member-ticket-card::before {
            left: -0.75rem;
        }

        .member-ticket-card::after {
            right: -0.75rem;
        }

        .member-ticket-image {
            min-height: 10rem;
            border-bottom: 1px dashed rgb(203 213 225);
        }

        .dark .member-ticket-image {
            border-bottom-color: rgb(51 65 85);
        }

        .member-ticket-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .ticket-status-pill {
            box-shadow: 0 0 0 1px rgb(255 255 255 / 0.18) inset;
        }
    </style>
@endpush

@section('panel_content')
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">EVENTOS</p>
                    <h1 class="mt-2 text-2xl font-black text-slate-900 dark:text-white md:text-3xl">Meus Ingressos</h1>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Visualize seus ingressos, QR Code e detalhes para apresentar no evento.</p>
                </div>

                <a href="{{ route('events.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                    <i class="fas fa-calendar-alt"></i>
                    Ver eventos
                </a>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-8">
            @if($registrations->isEmpty())
                <div class="py-12 text-center">
                    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 dark:bg-slate-800">
                        <i class="fas fa-ticket-alt text-3xl text-slate-300 dark:text-slate-600"></i>
                    </div>
                    <h3 class="mb-2 text-lg font-bold text-slate-800 dark:text-white">Você ainda não possui ingressos</h3>
                    <p class="mx-auto mb-6 max-w-sm text-slate-500 dark:text-slate-400">Explore nossos eventos e garanta sua vaga agora mesmo.</p>
                    <a href="{{ route('events.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-700">
                        <i class="fas fa-calendar-alt"></i>
                        Ver eventos
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($registrations as $reg)
                        @php
                            $event = $reg->event;
                            $startAt = $event?->start_at ? \Carbon\Carbon::parse($event->start_at) : null;
                            $ticketState = $reg->ticketStatusState();
                            $ticketUsed = $ticketState === 'used';
                            $ticketExpired = $ticketState === 'expired';
                            $hasQrTicket = (bool) ($event?->is_ticket_enabled) && filled($reg->ticket_code);
                            $quantity = max(1, (int) ($reg->quantity ?? 1));
                            $locationLabel = $event?->location ?: ($event?->address ?: 'Local a confirmar');
                        @endphp

                        @if(!$event)
                            @continue
                        @endif

                        <article class="member-ticket-card group rounded-3xl border border-slate-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10 dark:border-slate-800 dark:bg-slate-950 dark:hover:shadow-black/20">
                            <div class="member-ticket-image relative overflow-hidden bg-slate-100 dark:bg-slate-800">
                                @if($event->image_url)
                                    <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="h-40 w-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex h-40 w-full items-center justify-center bg-gradient-to-br from-blue-600 via-cyan-500 to-slate-900">
                                        <i class="fas fa-ticket-alt text-5xl text-white/80"></i>
                                    </div>
                                @endif

                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
                                <div class="absolute left-4 top-4 flex items-center gap-2">
                                    <span class="ticket-status-pill rounded-full bg-blue-600 px-3 py-1 text-[0.65rem] font-black uppercase tracking-wide text-white">
                                        {{ $hasQrTicket ? 'Ingresso digital' : 'Reserva' }}
                                    </span>
                                    @if($quantity > 1)
                                        <span class="rounded-full bg-white/90 px-3 py-1 text-[0.65rem] font-black uppercase tracking-wide text-slate-900">
                                            {{ $quantity }} acessos
                                        </span>
                                    @endif
                                </div>
                                <div class="absolute bottom-4 left-4 right-4">
                                    <p class="text-xs font-black uppercase tracking-[0.25em] text-white/70">SOMOS UNN</p>
                                    <h2 class="member-ticket-title mt-1 text-xl font-black leading-tight text-white">{{ $event->title }}</h2>
                                </div>
                            </div>

                            <div class="space-y-5 p-5">
                                <div class="grid gap-3 text-sm text-slate-600 dark:text-slate-300">
                                    <div class="flex items-start gap-3">
                                        <i class="far fa-calendar-alt mt-1 text-blue-500"></i>
                                        <span>{{ $startAt ? $startAt->format('d/m/Y - H:i') : 'Data a confirmar' }}</span>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <i class="fas fa-map-marker-alt mt-1 text-rose-500"></i>
                                        <span class="line-clamp-2">{{ $locationLabel }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-4 border-t border-slate-100 pt-5 dark:border-slate-800">
                                    <div class="min-w-0">
                                        <span class="block text-[0.65rem] font-black uppercase tracking-wide text-slate-400">Status</span>
                                        @if($ticketUsed)
                                            <span class="mt-1 flex items-center gap-1 text-xs font-black text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-check-double"></i>
                                                Já utilizado
                                            </span>
                                        @elseif($ticketExpired)
                                            <span class="mt-1 flex items-center gap-1 text-xs font-black text-red-600 dark:text-red-400">
                                                <i class="fas fa-ban"></i>
                                                Expirado
                                            </span>
                                        @elseif($hasQrTicket)
                                            <span class="mt-1 flex items-center gap-1 text-xs font-black text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-qrcode"></i>
                                                QR disponível
                                            </span>
                                        @else
                                            <span class="mt-1 flex items-center gap-1 text-xs font-black text-emerald-600 dark:text-emerald-400">
                                                <i class="fas fa-check-circle"></i>
                                                Confirmado
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <a href="{{ route('panel.tickets.show', $reg) }}"
                                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-xs font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                                            <i class="fas fa-ticket-alt"></i>
                                            Ver ingresso
                                        </a>
                                    </div>
                                </div>

                                <a href="{{ route('events.show', $event) }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-300">
                                    <i class="fas fa-calendar-check"></i>
                                    Ver página do evento
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($registrations->hasPages())
                    <div class="mt-8 border-t border-slate-100 pt-6 dark:border-slate-800">
                        {{ $registrations->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
