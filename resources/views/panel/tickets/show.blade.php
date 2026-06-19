@extends('panel.layouts.app')

@php
    $event = $registration->event;
    $order = $registration->order;
    $user = auth()->user();
    $logo = \App\Models\Setting::getUrl('logo_front') ?: \App\Models\Setting::getUrl('logo_image') ?: asset('img/logo.svg');
    $startAt = $event?->start_at ? \Carbon\Carbon::parse($event->start_at) : null;
    $endAt = $event?->end_at ? \Carbon\Carbon::parse($event->end_at) : null;
    $ticketImage = $event->image_url ?: asset('img/logo.svg');
    $locationLabel = $event?->location ?: ($event?->address ?: 'Local a confirmar');
    $addressLabel = $event?->address ?: $locationLabel;
    $batchLabel = trim((string) ($event->current_batch_label ?? 'Ingresso do evento'));
    $printRegistrations = $printRegistrations ?? collect([$registration]);
    $printTicketCount = $printRegistrations->sum(fn ($item) => max(1, (int) ($item->quantity ?? 1)));
    $weekdayLabel = $startAt ? $startAt->locale('pt_BR')->translatedFormat('l') : 'Evento';
    $monthLabel = $startAt ? $startAt->locale('pt_BR')->translatedFormat('F') : 'A confirmar';
    $dayLabel = $startAt ? $startAt->format('d') : '--';
    $timeLabel = $startAt ? $startAt->format('H:i') : '--:--';
@endphp

@section('title', 'Ingresso - ' . $event->title)

@section('panel_breadcrumb')
    <a href="{{ route('panel.tickets.index') }}" class="hover:underline">Ingressos</a>
    <span>/</span>
    <span>Ver ingresso</span>
@endsection

@push('styles')
    <style>
        .ticket-print-area {
            color: #111827;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }

        .ticket-print-sheet {
            display: grid;
            grid-template-columns: 15.25cm;
            justify-content: center;
            gap: 0.18cm;
            min-width: 15.25cm;
        }

        .ticket-print-slot {
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 15.25cm;
            height: 5.25cm;
            border: 0.03cm dashed #94a3b8;
            border-radius: 0.08cm;
            background: #f8fafc;
            padding: 0.08cm;
        }

        .event-paper-ticket {
            position: relative;
            display: grid;
            grid-template-columns: 2.3cm minmax(0, 1fr) 3.1cm;
            width: 15cm;
            height: 5cm;
            overflow: hidden;
            border: 0.035cm solid #64748b;
            border-radius: 0.12cm;
            background: #ffffff;
            box-shadow: 0 0.45cm 1.3cm rgba(15, 23, 42, 0.2);
            font-family: Arial, Helvetica, sans-serif;
        }

        .event-paper-ticket::before,
        .event-paper-ticket::after {
            content: "";
            position: absolute;
            top: 50%;
            z-index: 8;
            width: 0.34cm;
            height: 0.34cm;
            border: 0.02cm solid #94a3b8;
            border-radius: 999px;
            background: #f8fafc;
            transform: translateY(-50%);
        }

        .event-paper-ticket::before {
            left: 2.13cm;
        }

        .event-paper-ticket::after {
            right: 2.93cm;
        }

        .ticket-stub,
        .ticket-qr-stub {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.12cm;
            background:
                repeating-linear-gradient(0deg, rgba(31, 94, 219, 0.07) 0 0.035cm, transparent 0.035cm 0.18cm),
                linear-gradient(180deg, #ffffff 0%, #eef6ff 100%);
            padding: 0.18cm 0.16cm;
        }

        .ticket-stub {
            border-right: 0.04cm dashed #64748b;
        }

        .ticket-qr-stub {
            border-left: 0.04cm dashed #64748b;
        }

        .ticket-logo {
            max-width: 1.55cm;
            max-height: 0.48cm;
            object-fit: contain;
        }

        .ticket-stub-title {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            color: #0f172a;
            font-size: 0.16cm;
            font-weight: 900;
            line-height: 1.08;
            text-transform: uppercase;
        }

        .ticket-number {
            border: 0.02cm solid #94a3b8;
            background: #ffffff;
            color: #0f172a;
            font-family: "Courier New", monospace;
            font-size: 0.2cm;
            font-weight: 900;
            letter-spacing: 0.04cm;
            line-height: 1;
            padding: 0.09cm 0.05cm;
            text-align: center;
        }

        .ticket-side-meta {
            border-top: 0.02cm solid #d1d5db;
            border-bottom: 0.02cm solid #d1d5db;
            color: #0f172a;
            padding: 0.12cm 0;
            text-align: center;
            text-transform: uppercase;
        }

        .ticket-side-meta-day {
            display: block;
            color: #1f5edb;
            font-size: 0.48cm;
            font-weight: 900;
            line-height: 0.9;
        }

        .ticket-main {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            background: #ffffff;
        }

        .ticket-watermark-image {
            position: absolute;
            inset: -0.18cm;
            z-index: 0;
            width: calc(100% + 0.36cm);
            height: calc(100% + 0.36cm);
            object-fit: cover;
            opacity: 0.24;
            filter: grayscale(0.1) saturate(0.85) contrast(1.05);
            pointer-events: none;
        }

        .ticket-main::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 1;
            background:
                linear-gradient(90deg, rgba(255, 255, 255, 0.82), rgba(255, 255, 255, 0.62)),
                radial-gradient(circle at 70% 24%, rgba(31, 94, 219, 0.1), transparent 42%);
        }

        .ticket-main-inner {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-rows: auto 1fr auto;
            height: 100%;
            padding: 0.22cm 0.26cm 0.18cm;
        }

        .ticket-main-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.24cm;
        }

        .ticket-date-block {
            min-width: 1.45cm;
            border: 0.035cm solid #0f172a;
            background: rgba(255, 255, 255, 0.88);
            color: #0f172a;
            padding: 0.09cm 0.1cm;
            text-align: center;
            text-transform: uppercase;
            box-shadow: 0.13cm 0.13cm 0 rgba(31, 94, 219, 0.88);
        }

        .ticket-date-block strong {
            display: block;
            font-size: 0.72cm;
            font-weight: 900;
            line-height: 0.88;
        }

        .ticket-title-area {
            min-width: 0;
            flex: 1;
            color: #0f172a;
        }

        .ticket-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.12cm;
            border: 0.02cm solid rgba(15, 23, 42, 0.2);
            background: rgba(255, 255, 255, 0.7);
            padding: 0.06cm 0.1cm;
            font-size: 0.13cm;
            font-weight: 900;
            letter-spacing: 0.02cm;
            text-transform: uppercase;
        }

        .ticket-event-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-top: 0.12cm;
            font-size: 0.45cm;
            font-weight: 900;
            line-height: 0.96;
            text-transform: uppercase;
        }

        .ticket-event-subtitle {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-top: 0.08cm;
            max-width: 6.2cm;
            font-size: 0.18cm;
            font-weight: 700;
            line-height: 1.2;
            color: #334155;
        }

        .ticket-ribbon {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.18cm;
            border: 0.035cm solid #ffffff;
            background: linear-gradient(90deg, #1f5edb, #1539b4);
            color: #ffffff;
            padding: 0.11cm 0.16cm;
            text-transform: uppercase;
            box-shadow: 0 0.12cm 0.28cm rgba(15, 23, 42, 0.2);
        }

        .ticket-ribbon span {
            font-size: 0.18cm;
            font-weight: 900;
            letter-spacing: 0.02cm;
        }

        .ticket-main-bottom {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: end;
            gap: 0.14cm;
        }

        .ticket-location-strip {
            border: 0.02cm solid rgba(15, 23, 42, 0.18);
            background: rgba(255, 255, 255, 0.76);
            color: #111827;
            padding: 0.11cm 0.14cm;
        }

        .ticket-location-strip p {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .ticket-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.55cm;
            border: 0.035cm solid currentColor;
            background: rgba(255, 255, 255, 0.86);
            padding: 0.11cm 0.08cm;
            font-size: 0.16cm;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
            transform: rotate(-2deg);
        }

        .ticket-status--valid {
            color: #047857;
        }

        .ticket-status--used {
            color: #0369a1;
        }

        .ticket-status--expired {
            color: #b91c1c;
        }

        .ticket-exhibitor-stamp {
            position: absolute;
            right: 0.42cm;
            top: 0.32cm;
            z-index: 5;
            border: 0.045cm solid #b45309;
            color: #92400e;
            background: rgba(255, 251, 235, 0.82);
            padding: 0.08cm 0.18cm;
            font-size: 0.26cm;
            font-weight: 900;
            letter-spacing: 0.04cm;
            text-transform: uppercase;
            transform: rotate(-8deg);
        }

        .ticket-qr-box {
            display: flex;
            justify-content: center;
        }

        .ticket-qr-frame {
            border: 0.02cm solid #94a3b8;
            background: #ffffff;
            padding: 0.08cm;
        }

        .qr-holder canvas,
        .qr-holder img {
            display: block;
            width: 1.72cm !important;
            height: 1.72cm !important;
        }

        .ticket-code {
            word-break: break-all;
            color: #0f172a;
            font-family: "Courier New", monospace;
            font-size: 0.13cm;
            font-weight: 900;
            line-height: 1.18;
            text-align: center;
        }

        .ticket-info-box {
            border: 1px solid rgb(226 232 240);
            background: rgb(255 255 255 / 0.9);
            backdrop-filter: blur(10px);
        }

        .dark .ticket-info-box {
            border-color: rgb(30 41 59);
            background: rgb(15 23 42 / 0.9);
        }

        @media (max-width: 768px) {
            .ticket-print-area {
                margin-inline: -1rem;
                padding-inline: 1rem;
            }
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            html,
            body {
                width: 21cm;
                min-height: 29.7cm;
                margin: 0 !important;
                background: #ffffff !important;
            }

            body > *:not(main):not(script):not(style) {
                display: none !important;
            }

            main {
                min-height: 0 !important;
                padding: 0 !important;
            }

            .panel-theme-shell {
                min-height: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
            }

            .unn-panel-container {
                width: 21cm !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .panel-theme-shell > .unn-panel-container > nav,
            .panel-theme-shell > .unn-panel-container > .flex > aside,
            .ticket-page-shell > :not(.ticket-print-area) {
                display: none !important;
            }

            .panel-theme-shell > .unn-panel-container > .flex {
                display: block !important;
                margin: 0 !important;
            }

            .panel-theme-shell > .unn-panel-container > .flex > .flex-1 {
                display: block !important;
                width: 21cm !important;
                min-width: 0 !important;
            }

            .ticket-page-shell {
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .ticket-print-area {
                position: static !important;
                width: 15.25cm !important;
                margin: 0 auto !important;
                padding: 0 !important;
                overflow: visible !important;
            }

            .ticket-print-sheet {
                display: block !important;
                width: 15.25cm !important;
                min-width: 15.25cm !important;
                gap: 0 !important;
            }

            .ticket-print-slot {
                box-sizing: border-box !important;
                width: 15.25cm !important;
                height: 5.25cm !important;
                margin: 0 !important;
                padding: 0.08cm !important;
                border: 0.03cm dashed #94a3b8 !important;
                border-radius: 0 !important;
                background: #ffffff !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            .ticket-print-slot:nth-child(4n+1) {
                margin-top: 0.85cm !important;
            }

            .ticket-print-slot:nth-child(4n) {
                break-after: page !important;
                page-break-after: always !important;
            }

            .ticket-print-slot:last-child {
                break-after: auto !important;
                page-break-after: auto !important;
            }

            .ticket-print-actions,
            .ticket-details-after {
                display: none !important;
            }

            .event-paper-ticket {
                width: 15cm !important;
                height: 5cm !important;
                box-shadow: none !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .ticket-watermark-image {
                opacity: 0.22 !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .event-paper-ticket::before,
            .event-paper-ticket::after {
                background: #ffffff !important;
            }
        }
    </style>
@endpush

@section('panel_content')
    <div class="ticket-page-shell space-y-6">
        <div class="ticket-print-actions rounded-3xl border border-slate-100 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">INGRESSO DIGITAL</p>
                    <h1 class="mt-2 text-2xl font-black text-slate-900 dark:text-white md:text-3xl">{{ $event->title }}</h1>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Tamanho final de cada ingresso: 15 x 5 cm. A impressão usa A4 retrato com até 4 ingressos por folha.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('panel.tickets.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        <i class="fas fa-arrow-left"></i>
                        Meus ingressos
                    </a>
                    <button type="button" onclick="window.print()"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                        <i class="fas fa-print"></i>
                        Imprimir {{ $printTicketCount }} {{ $printTicketCount === 1 ? 'ingresso' : 'ingressos' }}
                    </button>
                </div>
            </div>
        </div>

        <section class="ticket-print-area">
            <div class="ticket-print-sheet">
                @php $printedCopy = 0; @endphp
                @foreach($printRegistrations as $ticketRegistration)
                    @php
                        $copies = max(1, (int) ($ticketRegistration->quantity ?? 1));
                        $itemState = $ticketRegistration->ticketStatusState();
                        $itemUsed = $itemState === 'used';
                        $itemExpired = $itemState === 'expired';
                        $itemHasQrTicket = (bool) $event?->is_ticket_enabled && filled($ticketRegistration->ticket_code);
                        $itemIsExhibitorTicket = data_get($ticketRegistration->order?->metadata, 'context') === 'event_exhibitor';
                        $itemTicketCode = (string) ($ticketRegistration->ticket_code ?: 'REG-' . str_pad((string) $ticketRegistration->id, 6, '0', STR_PAD_LEFT));
                        $itemTicketLabel = $itemUsed ? 'Já utilizado' : ($itemExpired ? 'Expirado' : ($itemHasQrTicket ? 'Ingresso válido' : 'Reserva confirmada'));
                        $itemTicketStatusClass = $itemUsed ? 'ticket-status--used' : ($itemExpired ? 'ticket-status--expired' : 'ticket-status--valid');
                    @endphp

                    @for($copy = 1; $copy <= $copies; $copy++)
                        @php
                            $printedCopy++;
                            $copySuffix = $copies > 1 ? '-' . str_pad((string) $copy, 2, '0', STR_PAD_LEFT) : '';
                            $ticketNumber = str_pad((string) $ticketRegistration->id, 4, '0', STR_PAD_LEFT) . $copySuffix;
                            $qrId = 'ticket-qrcode-' . $ticketRegistration->id . '-' . $copy;
                        @endphp

                        <div class="ticket-print-slot">
                            <div class="event-paper-ticket">
                                <aside class="ticket-stub">
                                    <div>
                                        <img src="{{ $logo }}" alt="SOMOS UNN" class="ticket-logo mb-1">
                                        <div class="ticket-stub-title">{{ $event->title }}</div>
                                    </div>

                                    <div class="ticket-side-meta">
                                        <p class="text-[0.12cm] font-black tracking-wide text-slate-500">{{ $weekdayLabel }}</p>
                                        <span class="ticket-side-meta-day">{{ $dayLabel }}</span>
                                        <p class="text-[0.13cm] font-black tracking-wide text-slate-900">{{ $monthLabel }}</p>
                                        <p class="mt-[0.02cm] text-[0.13cm] font-black text-slate-600">{{ $timeLabel }}</p>
                                    </div>

                                    <div class="space-y-[0.06cm]">
                                        <div class="ticket-number">{{ $ticketNumber }}</div>
                                        <p class="text-center text-[0.1cm] font-black uppercase tracking-[0.04cm] text-slate-500">Canhoto</p>
                                        <p class="line-clamp-2 text-center text-[0.12cm] font-bold text-slate-700">{{ $locationLabel }}</p>
                                    </div>
                                </aside>

                                <main class="ticket-main">
                                    <img src="{{ $ticketImage }}" alt="" class="ticket-watermark-image" aria-hidden="true">
                                    @if($itemIsExhibitorTicket)
                                        <div class="ticket-exhibitor-stamp">Expositor</div>
                                    @endif
                                    <div class="ticket-main-inner">
                                        <div class="ticket-main-top">
                                            <div class="ticket-date-block">
                                                <span class="text-[0.12cm] font-black tracking-[0.04cm]">{{ $weekdayLabel }}</span>
                                                <strong>{{ $dayLabel }}</strong>
                                                <span class="text-[0.14cm] font-black tracking-[0.04cm]">{{ $monthLabel }}</span>
                                            </div>

                                            <div class="ticket-title-area">
                                                <div class="ticket-brand">
                                                    <img src="{{ $logo }}" alt="SOMOS UNN" class="ticket-logo">
                                                    <span>Universidade de Negócios</span>
                                                </div>
                                                <h2 class="ticket-event-title">{{ $event->title }}</h2>
                                                <p class="ticket-event-subtitle">
                                                    {{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $event->description)), 115) ?: 'Apresente este ingresso no check-in do evento.' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-end">
                                            <div class="ticket-ribbon">
                                                <span>{{ $itemIsExhibitorTicket ? 'Expositor' : $batchLabel }}</span>
                                                <span>{{ $itemHasQrTicket ? 'QR Code' : 'Reserva' }}</span>
                                                <span>#{{ $ticketNumber }}</span>
                                            </div>
                                        </div>

                                        <div class="ticket-main-bottom">
                                            <div class="ticket-location-strip">
                                                <p class="text-[0.11cm] font-black uppercase tracking-[0.04cm] text-blue-700">Local do evento</p>
                                                <p class="mt-[0.03cm] text-[0.18cm] font-black">{{ $locationLabel }}</p>
                                                @if($addressLabel && $addressLabel !== $locationLabel)
                                                    <p class="mt-[0.02cm] text-[0.12cm] font-bold text-slate-700">{{ $addressLabel }}</p>
                                                @endif
                                            </div>

                                            <div class="ticket-status {{ $itemTicketStatusClass }}">{{ $itemTicketLabel }}</div>
                                        </div>
                                    </div>
                                </main>

                                <aside class="ticket-qr-stub">
                                    <div>
                                        <div class="ticket-number">{{ $ticketNumber }}</div>
                                        <p class="mt-[0.06cm] text-center text-[0.1cm] font-black uppercase tracking-[0.04cm] text-slate-500">Ingresso</p>
                                    </div>

                                    <div class="ticket-qr-box">
                                        @if($itemHasQrTicket)
                                            <div class="ticket-qr-frame">
                                                <div id="{{ $qrId }}" class="qr-holder ticket-qrcode" data-code="{{ $itemTicketCode }}"></div>
                                            </div>
                                        @else
                                            <div class="flex h-[1.9cm] w-[1.9cm] flex-col items-center justify-center border border-slate-300 bg-white p-[0.1cm] text-center">
                                                <i class="fas fa-ticket-alt text-[0.42cm] text-blue-600"></i>
                                                <p class="mt-[0.08cm] text-[0.12cm] font-black text-slate-900">Reserva</p>
                                                <p class="mt-[0.02cm] text-[0.1cm] text-slate-500">Check-in manual</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="space-y-[0.07cm]">
                                        <p class="ticket-code">{{ $itemTicketCode }}</p>
                                        <div class="grid grid-cols-2 gap-[0.06cm] text-center text-[0.1cm] font-black uppercase text-slate-600">
                                            <div class="border border-slate-300 bg-white p-[0.06cm]">
                                                <span class="block text-slate-400">Pedido</span>
                                                #{{ $ticketRegistration->order?->id ?: $order?->id ?: '-' }}
                                            </div>
                                            <div class="border border-slate-300 bg-white p-[0.06cm]">
                                                <span class="block text-slate-400">Via</span>
                                                {{ $printedCopy }}
                                            </div>
                                        </div>
                                    </div>
                                </aside>
                            </div>
                        </div>
                    @endfor
                @endforeach
            </div>
        </section>

        <section class="ticket-details-after grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="ticket-info-box rounded-3xl p-5">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">Participante</p>
                <p class="mt-2 font-black text-slate-900 dark:text-white">{{ $user?->name ?: 'Participante' }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $user?->email }}</p>
            </div>
            <div class="ticket-info-box rounded-3xl p-5">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">Impressão</p>
                <p class="mt-2 font-black text-slate-900 dark:text-white">{{ $printTicketCount }} {{ $printTicketCount === 1 ? 'ingresso' : 'ingressos' }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Até 4 por folha A4 em orientação retrato.</p>
            </div>
            <div class="ticket-info-box rounded-3xl p-5">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">Data do evento</p>
                <p class="mt-2 font-black text-slate-900 dark:text-white">{{ $startAt ? $startAt->format('d/m/Y H:i') : 'A confirmar' }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $endAt ? 'Encerramento: ' . $endAt->format('d/m/Y H:i') : 'Horário de encerramento não informado' }}</p>
            </div>
            <div class="ticket-info-box rounded-3xl p-5">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">Local do evento</p>
                <p class="mt-2 font-black text-slate-900 dark:text-white">{{ $locationLabel }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $addressLabel }}</p>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof QRCode === 'undefined') {
                return;
            }

            document.querySelectorAll('.ticket-qrcode[data-code]').forEach(function (target) {
                target.innerHTML = '';

                new QRCode(target, {
                    text: target.dataset.code,
                    width: 65,
                    height: 65,
                    colorDark: '#0f172a',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
            });
        });
    </script>
@endpush
