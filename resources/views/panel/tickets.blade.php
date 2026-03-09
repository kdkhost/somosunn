@extends('panel.layouts.app')

@section('title', 'Meus Ingressos')

@section('panel_breadcrumb')
    <a href="{{ route('panel.tickets.index') }}" class="hover:underline">Ingressos</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white transition-colors">Meus Ingressos</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm transition-colors">Visualize e gerencie seus ingressos para
                eventos.</p>
        </div>

        @if($registrations->isEmpty())
            <div
                class="bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-ticket-alt text-3xl text-slate-300 dark:text-slate-600"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2">Você ainda não possui ingressos</h3>
                <p class="text-slate-500 dark:text-slate-400 max-w-sm mx-auto mb-6">Explore nossos eventos e garanta sua vaga
                    agora mesmo!</p>
                <a href="{{ route('events.index') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-all inline-flex items-center gap-2 shadow-lg shadow-blue-500/20">
                    <i class="fas fa-calendar-alt"></i> Ver Eventos
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($registrations as $reg)
                    <div
                        class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow group">
                        <div class="relative h-40 overflow-hidden">
                            @if($reg->event->image)
                                <img src="{{ $reg->event->image_url }}" alt="{{ $reg->event->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-4xl text-slate-300 dark:text-slate-600"></i>
                                </div>
                            @endif
                            <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/60 to-transparent">
                                <span
                                    class="px-2 py-1 rounded bg-blue-600 text-[10px] font-bold text-white uppercase tracking-wider">
                                    {{ $reg->event->is_ticket_enabled ? 'Digital' : 'Reserva' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-5 space-y-4">
                            <div>
                                <h4
                                    class="font-bold text-slate-800 dark:text-white line-clamp-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ $reg->event->title }}</h4>
                                <div class="flex items-center gap-2 mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    <i class="far fa-calendar-alt"></i>
                                    <span>{{ \Carbon\Carbon::parse($reg->event->start_at)->format('d/m/Y - H:i') }}</span>
                                </div>
                            </div>

                            @php
                                $ticketExpired = $reg->event && $reg->event->isScannerExpired();
                                $ticketStatusMessage = $reg->event ? $reg->event->scannerStatusMessage() : 'QR Code expirado.';
                                $ticketPayload = [
                                    'code' => $reg->ticket_code,
                                    'title' => $reg->event->title,
                                    'date' => \Carbon\Carbon::parse($reg->event->start_at)->format('d/m/Y - H:i'),
                                    'expired' => $ticketExpired,
                                    'statusMessage' => $ticketStatusMessage,
                                ];
                            @endphp
                            <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Status</span>
                                    @if($reg->check_in_at)
                                        <span class="text-xs font-bold text-green-600 flex items-center gap-1">
                                            <i class="fas fa-check-circle"></i> Lido
                                        </span>
                                    @elseif($ticketExpired)
                                        <span class="text-xs font-bold text-red-600 flex items-center gap-1">
                                            <i class="fas fa-ban"></i> Expirado
                                        </span>
                                    @else
                                        <span class="text-xs font-bold text-blue-600 flex items-center gap-1">
                                            <i class="fas fa-clock"></i> Pendente
                                        </span>
                                    @endif
                                </div>

                                <button
                                    onclick='showTicketModal({!! json_encode($ticketPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!})'
                                    class="bg-slate-100 dark:bg-slate-800 hover:bg-blue-600 dark:hover:bg-blue-600 hover:text-white text-slate-700 dark:text-slate-300 p-2 rounded-xl transition-all shadow-sm flex items-center gap-2 text-xs font-bold group/btn">
                                    <i class="fas fa-qrcode"></i> Ver QR Code
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>

    <!-- Ticket Modal -->
    <div id="ticketModal"
        class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm"
        aria-labelledby="ticketModalTitle" role="dialog" aria-modal="true">
        <div class="bg-white dark:bg-slate-900 rounded-[3rem] p-4 max-w-sm w-full shadow-2xl overflow-hidden relative"
            onclick="event.stopPropagation()">

            <button onclick="closeTicketModal()"
                class="absolute top-6 right-6 w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-red-500 transition-colors">
                <i class="fas fa-times"></i>
            </button>

            <div class="p-6 text-center">
                <div
                    class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/20">
                    <i class="fas fa-ticket-alt text-2xl text-white"></i>
                </div>

                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2 leading-tight" id="modalTicketTitle">Título
                    do Evento</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-8" id="modalTicketDate">00/00/0000 - 00:00</p>

                <div id="ticketExpiredAlert"
                    class="hidden mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                </div>

                <div class="relative inline-flex mb-8">
                    <div class="bg-white p-6 rounded-3xl shadow-inner border-4 border-dashed border-slate-100 dark:border-slate-800 flex justify-center qrcode-wrapper"
                        id="qrcode-container">
                        <!-- QR Code vai aqui via JS -->
                    </div>
                    <div id="ticketExpiredOverlay"
                        class="hidden absolute inset-0 rounded-3xl bg-white/90 dark:bg-slate-950/90 backdrop-blur-[1px] border-2 border-red-300 dark:border-red-700 flex items-center justify-center">
                        <span
                            class="rotate-[-10deg] rounded-xl border-2 border-red-600 bg-red-600/10 px-4 py-2 text-xl font-black uppercase tracking-[0.2em] text-red-700 dark:text-red-300">
                            Expirado
                        </span>
                    </div>
                </div>

                <div class="space-y-4">
                    <p class="font-mono text-sm text-slate-600 dark:text-slate-400 tracking-widest bg-slate-50 dark:bg-slate-950 py-3 px-6 rounded-2xl border border-slate-100 dark:border-slate-800 select-all"
                        id="modalTicketCodeString">
                        XXXX-XXXX-XXXX
                    </p>
                    <p class="text-[10px] text-slate-400 uppercase font-black tracking-[0.2em]">Apresente no check-in</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        const modal = document.getElementById('ticketModal');
        let qrcodeInstance = null;

        window.showTicketModal = function (payload) {
            const expiredAlert = document.getElementById('ticketExpiredAlert');
            const expiredOverlay = document.getElementById('ticketExpiredOverlay');

            document.getElementById('modalTicketTitle').innerText = payload.title;
            document.getElementById('modalTicketDate').innerText = payload.date;
            document.getElementById('modalTicketCodeString').innerText = payload.code;

            const qrContainer = document.getElementById('qrcode-container');
            qrContainer.innerHTML = '';

            // Generate QR Code
            qrcodeInstance = new QRCode(qrContainer, {
                text: payload.code,
                width: 220,
                height: 220,
                colorDark: "#0f172a", // slate-900
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

            if (payload.expired) {
                expiredAlert.textContent = payload.statusMessage || 'QR Code expirado.';
                expiredAlert.classList.remove('hidden');
                expiredOverlay.classList.remove('hidden');
            } else {
                expiredAlert.textContent = '';
                expiredAlert.classList.add('hidden');
                expiredOverlay.classList.add('hidden');
            }

            modal.setAttribute('aria-hidden', 'false');
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        };

        window.closeTicketModal = function () {
            modal.setAttribute('aria-hidden', 'true');
            modal.style.display = 'none';
            modal.classList.add('hidden');
        };

        // Hide modal on backdrop click
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeTicketModal();
            }
        });
    </script>
@endpush
