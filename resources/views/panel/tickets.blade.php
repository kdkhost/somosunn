@extends('panel.layouts.app')

@section('title', 'Meus Ingressos')

@section('panel_breadcrumb')
    <a href="{{ route('panel.tickets.index') }}" class="hover:underline">Ingressos</a>
@endsection

@push('styles')
    <style>
        .member-ticket-card {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 104px;
            min-height: 245px;
            overflow: hidden;
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid rgb(226 232 240);
            box-shadow: 0 18px 45px rgb(15 23 42 / 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dark .member-ticket-card {
            background: rgb(15 23 42);
            border-color: rgb(30 41 59);
            box-shadow: 0 18px 45px rgb(0 0 0 / 0.18);
        }

        .member-ticket-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 24px 60px rgb(15 23 42 / 0.16);
        }

        .member-ticket-card::before,
        .member-ticket-card::after {
            content: "";
            position: absolute;
            right: 92px;
            z-index: 3;
            width: 24px;
            height: 24px;
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
            top: -12px;
        }

        .member-ticket-card::after {
            bottom: -12px;
        }

        .member-ticket-main {
            min-width: 0;
        }

        .member-ticket-cover {
            position: relative;
            height: 132px;
            overflow: hidden;
            background: linear-gradient(135deg, #1f5edb, #0f172a);
        }

        .member-ticket-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.45s ease;
        }

        .member-ticket-card:hover .member-ticket-cover img {
            transform: scale(1.06);
        }

        .member-ticket-cover::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgb(15 23 42 / 0.12), rgb(15 23 42 / 0.72));
        }

        .member-ticket-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .member-ticket-stub {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-left: 2px dashed rgb(148 163 184);
            background:
                repeating-linear-gradient(0deg, rgb(31 94 219 / 0.07) 0 2px, transparent 2px 9px),
                linear-gradient(180deg, #ffffff, #eef6ff);
            padding: 14px 10px;
            color: #0f172a;
            text-align: center;
        }

        .member-ticket-number {
            width: 100%;
            border: 1px solid rgb(148 163 184);
            background: #ffffff;
            padding: 7px 4px;
            font-family: "Courier New", monospace;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.08em;
        }

        .member-ticket-stub-label {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #1f5edb;
        }

        .member-ticket-status {
            font-size: 10px;
            font-weight: 900;
            line-height: 1.1;
            text-transform: uppercase;
        }

        @media (max-width: 420px) {
            .member-ticket-card {
                grid-template-columns: minmax(0, 1fr) 88px;
            }

            .member-ticket-card::before,
            .member-ticket-card::after {
                right: 76px;
            }
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
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
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
                            $ticketNumber = str_pad((string) $reg->id, 4, '0', STR_PAD_LEFT);
                            $statusLabel = $ticketUsed ? 'Já utilizado' : ($ticketExpired ? 'Expirado' : ($hasQrTicket ? 'QR disponível' : 'Confirmado'));
                        @endphp

                        @if(!$event)
                            @continue
                        @endif

                        <article class="member-ticket-card">
                            <div class="member-ticket-main">
                                <div class="member-ticket-cover">
                                    @if($event->image_url)
                                        <img src="{{ $event->image_url }}" alt="{{ $event->title }}">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center">
                                            <i class="fas fa-ticket-alt text-5xl text-white/80"></i>
                                        </div>
                                    @endif

                                    <div class="absolute inset-x-4 top-4 z-[1] flex items-center justify-between gap-3">
                                        <span class="rounded bg-blue-600 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-white">
                                            {{ $hasQrTicket ? 'Ingresso digital' : 'Reserva' }}
                                        </span>
                                        @if($quantity > 1)
                                            <span class="rounded bg-white/90 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-slate-900">
                                                {{ $quantity }} acessos
                                            </span>
                                        @endif
                                    </div>

                                    <div class="absolute bottom-4 left-4 right-4 z-[1]">
                                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/75">SOMOS UNN</p>
                                        <h2 class="member-ticket-title mt-1 text-lg font-black leading-tight text-white">{{ $event->title }}</h2>
                                    </div>
                                </div>

                                <div class="space-y-4 p-4">
                                    <div class="grid gap-2 text-sm text-slate-600 dark:text-slate-300">
                                        <div class="flex items-start gap-3">
                                            <i class="far fa-calendar-alt mt-1 text-blue-500"></i>
                                            <span>{{ $startAt ? $startAt->format('d/m/Y - H:i') : 'Data a confirmar' }}</span>
                                        </div>
                                        <div class="flex items-start gap-3">
                                            <i class="fas fa-map-marker-alt mt-1 text-rose-500"></i>
                                            <span class="line-clamp-2">{{ $locationLabel }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-4 dark:border-slate-800">
                                        <span class="text-xs font-black uppercase text-emerald-600 dark:text-emerald-400">
                                            <i class="fas {{ $ticketExpired ? 'fa-ban' : ($ticketUsed ? 'fa-check-double' : 'fa-check-circle') }} mr-1"></i>
                                            {{ $statusLabel }}
                                        </span>
                                        <a href="{{ route('events.show', $event) }}" class="text-xs font-bold text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-300">
                                            Ver evento
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <aside class="member-ticket-stub">
                                <div class="member-ticket-number">{{ $ticketNumber }}</div>
                                <div class="member-ticket-stub-label">Ingresso</div>
                                <div>
                                    <p class="member-ticket-status">{{ $statusLabel }}</p>
                                    <a href="{{ route('panel.tickets.show', $reg) }}"
                                        class="mt-3 inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700"
                                        title="Ver ingresso">
                                        <i class="fas fa-ticket-alt"></i>
                                    </a>
                                </div>
                            </aside>
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
