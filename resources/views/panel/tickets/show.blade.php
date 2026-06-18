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
    $quantity = max(1, (int) ($registration->quantity ?? 1));
    $locationLabel = $event?->location ?: ($event?->address ?: 'Local a confirmar');
    $addressLabel = $event?->address ?: $locationLabel;
    $ticketLabel = $ticketUsed ? 'Já utilizado' : ($ticketExpired ? 'Expirado' : ($hasQrTicket ? 'Ingresso válido' : 'Reserva confirmada'));
    $ticketTone = $ticketUsed ? 'emerald' : ($ticketExpired ? 'red' : 'blue');
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
            color: #0f172a;
        }

        .event-ticket {
            position: relative;
            display: grid;
            grid-template-columns: minmax(7rem, 0.72fr) minmax(0, 2.4fr) minmax(11rem, 0.9fr);
            min-height: 22rem;
            overflow: hidden;
            border-radius: 1.75rem;
            background: #f8fafc;
            box-shadow: 0 24px 70px rgb(15 23 42 / 0.18);
        }

        .event-ticket::before,
        .event-ticket::after {
            content: "";
            position: absolute;
            top: 50%;
            z-index: 5;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 999px;
            background: rgb(15 23 42);
            transform: translateY(-50%);
        }

        .event-ticket::before {
            left: calc(30% - 1.25rem);
        }

        .event-ticket::after {
            right: calc(24% - 1.25rem);
        }

        .event-ticket-stub,
        .event-ticket-qr {
            position: relative;
            z-index: 1;
            background: linear-gradient(180deg, #ffffff 0%, #eef6ff 100%);
        }

        .event-ticket-stub {
            border-right: 2px dashed #94a3b8;
        }

        .event-ticket-qr {
            border-left: 2px dashed #94a3b8;
        }

        .event-ticket-main {
            position: relative;
            z-index: 1;
            overflow: hidden;
            background: linear-gradient(135deg, rgb(30 64 175 / 0.95), rgb(14 116 144 / 0.94));
            color: #ffffff;
        }

        .event-ticket-main::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgb(15 23 42 / 0.8), rgb(15 23 42 / 0.22)),
                var(--event-ticket-bg);
            background-position: center;
            background-size: cover;
            opacity: var(--event-ticket-bg-opacity, 1);
        }

        .event-ticket-main::after {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 20% 20%, rgb(255 255 255 / 0.22) 0, transparent 32%),
                linear-gradient(135deg, rgb(37 99 235 / 0.3), rgb(6 182 212 / 0.18));
        }

        .event-ticket-main-content {
            position: relative;
            z-index: 2;
        }

        .ticket-side-label {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            letter-spacing: 0.16em;
        }

        .ticket-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .ticket-info-box {
            border: 1px solid rgb(226 232 240);
            background: rgb(255 255 255 / 0.86);
            backdrop-filter: blur(10px);
        }

        .qr-holder canvas,
        .qr-holder img {
            width: 9rem !important;
            height: 9rem !important;
        }

        @media (max-width: 1024px) {
            .event-ticket {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .event-ticket::before,
            .event-ticket::after {
                display: none;
            }

            .event-ticket-stub,
            .event-ticket-qr {
                border: 0;
            }

            .event-ticket-stub {
                border-bottom: 2px dashed #94a3b8;
            }

            .event-ticket-qr {
                border-top: 2px dashed #94a3b8;
            }

            .ticket-side-label {
                writing-mode: initial;
                transform: none;
            }
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
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

            .ticket-print-actions,
            .ticket-details-after {
                display: none !important;
            }

            .event-ticket {
                width: 100% !important;
                min-height: 180mm !important;
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .event-ticket::before,
            .event-ticket::after {
                background: #ffffff !important;
                border: 1px solid #cbd5e1;
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
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Confira os dados do ingresso antes de apresentar no evento.</p>
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
            <div class="event-ticket" style="--event-ticket-bg: url('{{ $event->image_url ?: asset('img/logo.svg') }}'); --event-ticket-bg-opacity: {{ $event->image_url ? '1' : '0.08' }};">
                <aside class="event-ticket-stub flex flex-col justify-between gap-6 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <img src="{{ $logo }}" alt="SOMOS UNN" class="h-12 max-w-[9rem] object-contain">
                        <span class="rounded-full bg-blue-600 px-3 py-1 text-[0.65rem] font-black uppercase tracking-wide text-white">
                            {{ $hasQrTicket ? 'QR Code' : 'Reserva' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-4 lg:flex-col lg:items-start">
                        <p class="ticket-side-label text-xs font-black uppercase text-blue-700">Ingresso digital</p>
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Código</p>
                            <p class="mt-1 break-all font-mono text-sm font-black text-slate-900">{{ $ticketCode }}</p>
                        </div>
                    </div>

                    <div class="space-y-1 text-sm">
                        <p class="font-black text-slate-900">{{ $user?->name ?: 'Participante' }}</p>
                        <p class="text-slate-500">{{ $user?->email }}</p>
                        @if($quantity > 1)
                            <p class="font-bold text-blue-700">{{ $quantity }} acessos vinculados</p>
                        @endif
                    </div>
                </aside>

                <main class="event-ticket-main p-6 md:p-8">
                    <div class="event-ticket-main-content flex h-full flex-col justify-between gap-8">
                        <div class="flex items-start justify-between gap-6">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.35em] text-blue-100">SOMOS UNN</p>
                                <h2 class="mt-4 max-w-2xl text-3xl font-black leading-tight md:text-5xl">{{ $event->title }}</h2>
                            </div>
                            <div class="hidden rounded-2xl bg-white/15 px-4 py-3 text-right backdrop-blur md:block">
                                <p class="text-xs font-black uppercase tracking-wide text-white/70">Status</p>
                                <p class="mt-1 text-sm font-black">{{ $ticketLabel }}</p>
                            </div>
                        </div>

                        <div class="max-w-3xl rounded-3xl bg-white/15 p-5 backdrop-blur">
                            <p class="text-sm font-bold leading-relaxed text-white/90">
                                {{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $event->description)), 220) ?: 'Apresente este ingresso no check-in do evento.' }}
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="rounded-2xl bg-white/15 p-4 backdrop-blur">
                                <p class="text-xs font-black uppercase tracking-wide text-white/65">Data e hora</p>
                                <p class="mt-1 text-lg font-black">{{ $startAt ? $startAt->format('d/m/Y H:i') : 'A confirmar' }}</p>
                                @if($endAt)
                                    <p class="text-xs font-bold text-white/70">até {{ $endAt->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                            <div class="rounded-2xl bg-white/15 p-4 backdrop-blur md:col-span-2">
                                <p class="text-xs font-black uppercase tracking-wide text-white/65">Local</p>
                                <p class="mt-1 text-lg font-black">{{ $locationLabel }}</p>
                                @if($addressLabel && $addressLabel !== $locationLabel)
                                    <p class="text-xs font-bold text-white/70">{{ $addressLabel }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </main>

                <aside class="event-ticket-qr flex flex-col justify-between gap-6 p-6">
                    <div class="text-center">
                        <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Acesso</p>
                        <p class="mt-2 text-lg font-black text-slate-900">{{ $ticketLabel }}</p>
                    </div>

                    <div class="flex justify-center">
                        @if($hasQrTicket)
                            <div class="rounded-3xl border-4 border-dashed border-slate-200 bg-white p-4 shadow-inner">
                                <div id="ticket-qrcode" class="qr-holder" data-code="{{ $ticketCode }}"></div>
                            </div>
                        @else
                            <div class="flex h-44 w-44 flex-col items-center justify-center rounded-3xl border-4 border-dashed border-slate-200 bg-white p-4 text-center shadow-inner">
                                <i class="fas fa-ticket-alt text-4xl text-blue-600"></i>
                                <p class="mt-3 text-sm font-black text-slate-900">Reserva confirmada</p>
                                <p class="mt-1 text-xs text-slate-500">Apresente seus dados no check-in.</p>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-3 text-center">
                        <p class="break-all rounded-2xl bg-slate-100 px-4 py-3 font-mono text-xs font-black tracking-wide text-slate-800">{{ $ticketCode }}</p>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="rounded-2xl bg-white/80 p-3">
                                <p class="font-black uppercase text-slate-400">Pedido</p>
                                <p class="mt-1 font-black text-slate-900">#{{ $order?->id ?: '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/80 p-3">
                                <p class="font-black uppercase text-slate-400">Ingresso</p>
                                <p class="mt-1 font-black text-slate-900">#{{ $registration->id }}</p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="ticket-details-after grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="ticket-info-box rounded-3xl p-5 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">Participante</p>
                <p class="mt-2 font-black text-slate-900 dark:text-white">{{ $user?->name ?: 'Participante' }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $user?->email }}</p>
            </div>
            <div class="ticket-info-box rounded-3xl p-5 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">Status do ingresso</p>
                <p class="mt-2 font-black text-{{ $ticketTone }}-600 dark:text-{{ $ticketTone }}-300">{{ $ticketLabel }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $registration->ticketStatusMessage() }}</p>
            </div>
            <div class="ticket-info-box rounded-3xl p-5 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">Data do evento</p>
                <p class="mt-2 font-black text-slate-900 dark:text-white">{{ $startAt ? $startAt->format('d/m/Y H:i') : 'A confirmar' }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $endAt ? 'Encerramento: ' . $endAt->format('d/m/Y H:i') : 'Horário de encerramento não informado' }}</p>
            </div>
            <div class="ticket-info-box rounded-3xl p-5 dark:border-slate-800 dark:bg-slate-900">
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
                    width: 144,
                    height: 144,
                    colorDark: '#0f172a',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
            });
        </script>
    @endif
@endpush
