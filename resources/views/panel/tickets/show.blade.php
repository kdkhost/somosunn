@extends('panel.layouts.app')

@php
    $event = $registration->event;
    $order = $registration->order;
    $user = auth()->user();
    $logo = \App\Models\Setting::getUrl('logo_front') ?: \App\Models\Setting::getUrl('logo_image') ?: asset('img/logo.svg');
    $startAt = $event?->start_at ? \Carbon\Carbon::parse($event->start_at) : null;
    $endAt = $event?->end_at ? \Carbon\Carbon::parse($event->end_at) : null;
    $ticketState = $registration->ticketStatusState();
    $ticketUsed = $ticketState === 'used';
    $ticketExpired = $ticketState === 'expired';
    $hasQrTicket = (bool) $event?->is_ticket_enabled && filled($registration->ticket_code);
    $ticketCode = (string) ($registration->ticket_code ?: 'REG-' . str_pad((string) $registration->id, 6, '0', STR_PAD_LEFT));
    $ticketNumber = str_pad((string) $registration->id, 4, '0', STR_PAD_LEFT);
    $quantity = max(1, (int) ($registration->quantity ?? 1));
    $locationLabel = $event?->location ?: ($event?->address ?: 'Local a confirmar');
    $addressLabel = $event?->address ?: $locationLabel;
    $ticketImage = $event->image_url ?: asset('img/logo.svg');
    $ticketLabel = $ticketUsed ? 'Já utilizado' : ($ticketExpired ? 'Expirado' : ($hasQrTicket ? 'Ingresso válido' : 'Reserva confirmada'));
    $ticketStatusClass = $ticketUsed ? 'ticket-status--used' : ($ticketExpired ? 'ticket-status--expired' : 'ticket-status--valid');
    $weekdayLabel = $startAt ? $startAt->locale('pt_BR')->translatedFormat('l') : 'Evento';
    $monthLabel = $startAt ? $startAt->locale('pt_BR')->translatedFormat('F') : 'A confirmar';
    $dayLabel = $startAt ? $startAt->format('d') : '--';
    $timeLabel = $startAt ? $startAt->format('H:i') : '--:--';
    $batchLabel = trim((string) ($event->current_batch_label ?? 'Ingresso do evento'));
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
        }

        .ticket-scroll {
            overflow-x: auto;
            padding-bottom: 0.75rem;
        }

        .event-paper-ticket {
            position: relative;
            display: grid;
            grid-template-columns: 158px minmax(0, 1fr) 210px;
            min-width: 1040px;
            min-height: 322px;
            overflow: hidden;
            border: 1px solid #a7b5c8;
            border-radius: 10px;
            background: #f8fafc;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
            font-family: Arial, Helvetica, sans-serif;
        }

        .event-paper-ticket::before,
        .event-paper-ticket::after {
            content: "";
            position: absolute;
            top: 50%;
            z-index: 9;
            width: 28px;
            height: 28px;
            border-radius: 999px;
            background: rgb(15 23 42);
            border: 1px solid rgba(255, 255, 255, 0.45);
            transform: translateY(-50%);
        }

        .event-paper-ticket::before {
            left: 144px;
        }

        .event-paper-ticket::after {
            right: 196px;
        }

        .ticket-stub,
        .ticket-qr-stub {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 12px;
            background:
                repeating-linear-gradient(0deg, rgba(31, 94, 219, 0.06) 0 2px, transparent 2px 9px),
                linear-gradient(180deg, #ffffff 0%, #edf6ff 100%);
            padding: 16px 14px;
        }

        .ticket-stub {
            border-right: 2px dashed #6b7280;
        }

        .ticket-qr-stub {
            border-left: 2px dashed #6b7280;
        }

        .ticket-stub-title {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 13px;
            font-weight: 900;
            line-height: 1.12;
            text-transform: uppercase;
        }

        .ticket-number {
            border: 1px solid #94a3b8;
            background: #ffffff;
            color: #0f172a;
            font-family: "Courier New", monospace;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 0.08em;
            line-height: 1;
            padding: 8px 10px;
            text-align: center;
        }

        .ticket-side-meta {
            border-top: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
            padding: 10px 0;
            text-align: center;
            text-transform: uppercase;
        }

        .ticket-main {
            position: relative;
            isolation: isolate;
            min-height: 322px;
            overflow: hidden;
            background: #0f172a;
        }

        .ticket-main::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -2;
            background-image: var(--ticket-bg);
            background-position: center;
            background-size: cover;
            filter: saturate(1.15) contrast(1.03);
        }

        .ticket-main::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                linear-gradient(90deg, rgba(248, 250, 252, 0.92) 0%, rgba(248, 250, 252, 0.68) 34%, rgba(15, 23, 42, 0.18) 58%, rgba(15, 23, 42, 0.62) 100%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.28), rgba(255, 255, 255, 0.08));
        }

        .ticket-main-inner {
            display: grid;
            grid-template-rows: auto 1fr auto;
            min-height: 322px;
            padding: 22px 26px 18px;
        }

        .ticket-main-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 22px;
        }

        .ticket-date-block {
            min-width: 166px;
            border: 2px solid #0f172a;
            background: rgba(255, 255, 255, 0.92);
            color: #0f172a;
            padding: 10px 14px;
            text-align: center;
            text-transform: uppercase;
            box-shadow: 8px 8px 0 rgba(31, 94, 219, 0.9);
        }

        .ticket-date-block strong {
            display: block;
            font-size: 56px;
            font-weight: 900;
            line-height: 0.9;
        }

        .ticket-title-area {
            max-width: 560px;
            color: #0f172a;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.35);
        }

        .ticket-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(15, 23, 42, 0.18);
            background: rgba(255, 255, 255, 0.78);
            padding: 7px 12px;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .ticket-event-title {
            margin-top: 14px;
            font-size: clamp(28px, 4vw, 46px);
            font-weight: 900;
            line-height: 0.95;
            text-transform: uppercase;
        }

        .ticket-event-subtitle {
            margin-top: 12px;
            max-width: 520px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
        }

        .ticket-ribbon {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 2px solid #ffffff;
            background: linear-gradient(90deg, #1f5edb, #1539b4);
            color: #ffffff;
            padding: 10px 14px;
            text-transform: uppercase;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.24);
        }

        .ticket-ribbon span {
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 0.08em;
        }

        .ticket-main-bottom {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: end;
            gap: 18px;
        }

        .ticket-location-strip {
            border: 1px solid rgba(15, 23, 42, 0.2);
            background: rgba(255, 255, 255, 0.88);
            color: #111827;
            padding: 11px 14px;
        }

        .ticket-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 142px;
            border: 2px solid currentColor;
            background: rgba(255, 255, 255, 0.92);
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 900;
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

        .ticket-qr-box {
            display: flex;
            justify-content: center;
        }

        .ticket-qr-frame {
            border: 1px solid #94a3b8;
            background: #ffffff;
            padding: 8px;
        }

        .qr-holder canvas,
        .qr-holder img {
            width: 126px !important;
            height: 126px !important;
            display: block;
        }

        .ticket-code {
            word-break: break-all;
            font-family: "Courier New", monospace;
            font-size: 11px;
            font-weight: 900;
            line-height: 1.2;
            text-align: center;
        }

        .ticket-details-after {
            color: inherit;
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
            .ticket-print-actions {
                border-radius: 1.25rem;
            }

            .event-paper-ticket {
                min-width: 960px;
            }
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }

            html,
            body {
                background: #ffffff !important;
            }

            body * {
                visibility: hidden !important;
            }

            .ticket-print-area,
            .ticket-print-area * {
                visibility: visible !important;
            }

            .ticket-print-area {
                position: fixed !important;
                inset: 0 !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .ticket-scroll {
                overflow: visible !important;
                padding: 0 !important;
            }

            .ticket-print-actions,
            .ticket-details-after {
                display: none !important;
            }

            .event-paper-ticket {
                width: 100% !important;
                min-width: 0 !important;
                min-height: 178mm !important;
                box-shadow: none !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .event-paper-ticket::before,
            .event-paper-ticket::after {
                background: #ffffff !important;
                border-color: #94a3b8;
            }
        }
    </style>
@endpush

@section('panel_content')
    <div class="space-y-6">
        <div class="ticket-print-actions rounded-3xl border border-slate-100 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">INGRESSO DIGITAL</p>
                    <h1 class="mt-2 text-2xl font-black text-slate-900 dark:text-white md:text-3xl">{{ $event->title }}</h1>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Formato de ingresso para imprimir ou apresentar no check-in do evento.</p>
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
                        Imprimir ingresso
                    </button>
                </div>
            </div>
        </div>

        <section class="ticket-print-area">
            <div class="ticket-scroll">
                <div class="event-paper-ticket" style="--ticket-bg: url('{{ $ticketImage }}');">
                    <aside class="ticket-stub">
                        <div>
                            <img src="{{ $logo }}" alt="SOMOS UNN" class="mb-3 h-10 max-w-full object-contain">
                            <div class="ticket-stub-title">{{ $event->title }}</div>
                        </div>

                        <div class="ticket-side-meta">
                            <p class="text-[11px] font-black tracking-wide text-slate-500">{{ $weekdayLabel }}</p>
                            <p class="text-3xl font-black leading-none text-blue-700">{{ $dayLabel }}</p>
                            <p class="text-xs font-black tracking-wide text-slate-900">{{ $monthLabel }}</p>
                            <p class="mt-1 text-xs font-black text-slate-600">{{ $timeLabel }}</p>
                        </div>

                        <div class="space-y-2">
                            <div class="ticket-number">{{ $ticketNumber }}</div>
                            <p class="text-center text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">Canhoto</p>
                            <p class="line-clamp-2 text-center text-[11px] font-bold text-slate-700">{{ $locationLabel }}</p>
                        </div>
                    </aside>

                    <main class="ticket-main">
                        <div class="ticket-main-inner">
                            <div class="ticket-main-top">
                                <div class="ticket-date-block">
                                    <span class="text-xs font-black tracking-[0.16em]">{{ $weekdayLabel }}</span>
                                    <strong>{{ $dayLabel }}</strong>
                                    <span class="text-sm font-black tracking-[0.18em]">{{ $monthLabel }}</span>
                                </div>

                                <div class="ticket-title-area">
                                    <div class="ticket-brand">
                                        <img src="{{ $logo }}" alt="SOMOS UNN" class="h-8 max-w-[110px] object-contain">
                                        <span>Universidade de Negócios e Networking</span>
                                    </div>
                                    <h2 class="ticket-event-title">{{ $event->title }}</h2>
                                    <p class="ticket-event-subtitle">
                                        {{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $event->description)), 150) ?: 'Apresente este ingresso no check-in do evento.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end">
                                <div class="ticket-ribbon">
                                    <span>{{ $batchLabel }}</span>
                                    <span>{{ $hasQrTicket ? 'QR Code' : 'Reserva' }}</span>
                                    <span>#{{ $ticketNumber }}</span>
                                </div>
                            </div>

                            <div class="ticket-main-bottom">
                                <div class="ticket-location-strip">
                                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-blue-700">Local do evento</p>
                                    <p class="mt-1 text-base font-black">{{ $locationLabel }}</p>
                                    @if($addressLabel && $addressLabel !== $locationLabel)
                                        <p class="mt-1 text-xs font-bold text-slate-700">{{ $addressLabel }}</p>
                                    @endif
                                </div>

                                <div class="ticket-status {{ $ticketStatusClass }}">{{ $ticketLabel }}</div>
                            </div>
                        </div>
                    </main>

                    <aside class="ticket-qr-stub">
                        <div>
                            <div class="ticket-number">{{ $ticketNumber }}</div>
                            <p class="mt-2 text-center text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">Ingresso</p>
                        </div>

                        <div class="ticket-qr-box">
                            @if($hasQrTicket)
                                <div class="ticket-qr-frame">
                                    <div id="ticket-qrcode" class="qr-holder" data-code="{{ $ticketCode }}"></div>
                                </div>
                            @else
                                <div class="flex h-[144px] w-[144px] flex-col items-center justify-center border border-slate-300 bg-white p-3 text-center">
                                    <i class="fas fa-ticket-alt text-3xl text-blue-600"></i>
                                    <p class="mt-2 text-xs font-black text-slate-900">Reserva confirmada</p>
                                    <p class="mt-1 text-[10px] text-slate-500">Check-in manual</p>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <p class="ticket-code">{{ $ticketCode }}</p>
                            <div class="grid grid-cols-2 gap-2 text-center text-[10px] font-black uppercase text-slate-600">
                                <div class="border border-slate-300 bg-white p-2">
                                    <span class="block text-slate-400">Pedido</span>
                                    #{{ $order?->id ?: '-' }}
                                </div>
                                <div class="border border-slate-300 bg-white p-2">
                                    <span class="block text-slate-400">Qtd.</span>
                                    {{ $quantity }}
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        <section class="ticket-details-after grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="ticket-info-box rounded-3xl p-5">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">Participante</p>
                <p class="mt-2 font-black text-slate-900 dark:text-white">{{ $user?->name ?: 'Participante' }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $user?->email }}</p>
            </div>
            <div class="ticket-info-box rounded-3xl p-5">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">Status do ingresso</p>
                <p class="mt-2 font-black text-slate-900 dark:text-white">{{ $ticketLabel }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $registration->ticketStatusMessage() }}</p>
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
    @if($hasQrTicket)
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const target = document.getElementById('ticket-qrcode');

                if (!target || typeof QRCode === 'undefined') {
                    return;
                }

                new QRCode(target, {
                    text: target.dataset.code,
                    width: 126,
                    height: 126,
                    colorDark: '#0f172a',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
            });
        </script>
    @endif
@endpush
