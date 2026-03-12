@extends('layouts.app')

@section('title', $event->title . ' - Eventos UNN')

@section('content')
    @php
        $suppressFlashErrorToast = true;
        $isDemo = $event->is_demo ?? false;
        $startDate = is_string($event->start_at) ? \Carbon\Carbon::parse($event->start_at) : $event->start_at;
        $endDate = null;
        if ($event->end_at) {
            $endDate = is_string($event->end_at) ? \Carbon\Carbon::parse($event->end_at) : $event->end_at;
        }
        $eventColor = $event->color ?? '#1F5EDB';

        $hexToRgba = function (?string $hex, float $alpha): ?string {
            $hex = trim((string) $hex);
            if ($hex === '') {
                return null;
            }

            $alpha = max(0, min(1, $alpha));

            if (preg_match('/^#?[0-9a-fA-F]{3}$/', $hex)) {
                $hex = ltrim($hex, '#');
                $r = hexdec(str_repeat($hex[0], 2));
                $g = hexdec(str_repeat($hex[1], 2));
                $b = hexdec(str_repeat($hex[2], 2));
                return "rgba({$r},{$g},{$b},{$alpha})";
            }

            if (preg_match('/^#?[0-9a-fA-F]{6}$/', $hex)) {
                $hex = ltrim($hex, '#');
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                return "rgba({$r},{$g},{$b},{$alpha})";
            }

            return null;
        };

        $sitePrimary = \App\Models\Setting::get('site_color_primary') ?: '#1F5EDB';
        $siteSecondary = \App\Models\Setting::get('site_color_secondary') ?: '#1D3FC4';

        // Admin controls (Settings -> Aparência -> Eventos)
        $eventsHeroBlurPxRaw = \App\Models\Setting::get('events_hero_bg_blur_px');
        $eventsHeroBlurPx = is_numeric($eventsHeroBlurPxRaw) ? (int) $eventsHeroBlurPxRaw : 64;
        $eventsHeroBlurPx = max(0, min(140, $eventsHeroBlurPx));

        $eventsHeroFilmRaw = \App\Models\Setting::get('events_hero_film_strength_percent');
        $eventsHeroFilmPercent = is_numeric($eventsHeroFilmRaw) ? (int) $eventsHeroFilmRaw : 100;
        $eventsHeroFilmPercent = max(0, min(100, $eventsHeroFilmPercent));
        $eventsHeroFilmScale = $eventsHeroFilmPercent / 100;
        $filmAlpha = static function (float $base) use ($eventsHeroFilmScale): float {
            $value = $base * $eventsHeroFilmScale;
            return max(0.0, min(1.0, $value));
        };

        // Base background (subtle, always on)
        $sitePrimary14 = $hexToRgba($sitePrimary, 0.14) ?: 'rgba(31,94,219,0.14)';
        $siteSecondary08 = $hexToRgba($siteSecondary, 0.08) ?: 'rgba(29,63,196,0.08)';

        // Film (insulfilm) overlay scales by Admin slider
        $sitePrimary38 = $hexToRgba($sitePrimary, $filmAlpha(0.38)) ?: ('rgba(31,94,219,' . $filmAlpha(0.38) . ')');
        $sitePrimary30 = $hexToRgba($sitePrimary, $filmAlpha(0.30)) ?: ('rgba(31,94,219,' . $filmAlpha(0.30) . ')');
        $sitePrimary22 = $hexToRgba($sitePrimary, $filmAlpha(0.22)) ?: ('rgba(31,94,219,' . $filmAlpha(0.22) . ')');

        $siteSecondary28 = $hexToRgba($siteSecondary, $filmAlpha(0.28)) ?: ('rgba(29,63,196,' . $filmAlpha(0.28) . ')');
        $siteSecondary18 = $hexToRgba($siteSecondary, $filmAlpha(0.18)) ?: ('rgba(29,63,196,' . $filmAlpha(0.18) . ')');

        $eventImageUrl = $event->image_url;
        $mapQuery = urlencode($event->address);
        $confirmedSeats = $event->confirmed_seats;
        $remainingSeats = $event->remaining_seats;
        $now = now();
        $isClosed = false;
        if ($endDate) {
            $isClosed = $endDate->lt($now);
        } elseif ($startDate) {
            $isClosed = $startDate->lt($now->copy()->startOfDay());
        }
    @endphp

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
        <!-- Hero Section -->
        <section class="pt-24 md:pt-28 pb-6 md:pb-12 px-4 md:px-12 lg:px-24 relative overflow-hidden" style="background:
                radial-gradient(1200px circle at 15% 25%, {{ $sitePrimary14 }} 0%, transparent 58%),
                radial-gradient(900px circle at 85% 0%, {{ $siteSecondary08 }} 0%, transparent 55%),
                linear-gradient(135deg, {{ $sitePrimary14 }} 0%, {{ $siteSecondary08 }} 100%);">
            @if($eventImageUrl)
                <div class="absolute inset-0 pointer-events-none overflow-hidden">
                    <img src="{{ $eventImageUrl }}" alt=""
                        class="absolute inset-0 w-full h-full object-cover scale-110 saturate-[1.2] brightness-90"
                        style="filter: blur({{ $eventsHeroBlurPx }}px); opacity: 0.65;" loading="lazy" aria-hidden="true">

                    <!-- Película transparente em cor degradê -->
                    <div class="absolute inset-0" style="background: linear-gradient(135deg, 
                            {{ $hexToRgba($sitePrimary, 0.75 * $eventsHeroFilmScale) }} 0%, 
                            {{ $hexToRgba($siteSecondary, 0.65 * $eventsHeroFilmScale) }} 50%, 
                            {{ $hexToRgba($sitePrimary, 0.85 * $eventsHeroFilmScale) }} 100%);">
                    </div>
                </div>
            @endif

            <div class="max-w-7xl mx-auto relative">
                <a href="{{ route('events.index') }}"
                    class="inline-flex items-center gap-2 text-white/80 hover:text-white mb-6 transition-all duration-300 font-semibold drop-shadow-lg">
                    <i class="fas fa-arrow-left"></i> Voltar para eventos
                </a>

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6">
                        <i class="fas fa-triangle-exclamation mr-2"></i>{{ session('error') }}
                    </div>
                @endif

                <div class="flex flex-col lg:flex-row gap-6 md:gap-8 items-start">
                    <!-- Event Info -->
                    <div class="flex-1">
                        @if($isDemo)
                            <span
                                class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold mb-4">
                                <i class="fas fa-info-circle"></i> Evento de Demonstração
                            </span>
                        @endif

                        @if($isClosed)
                            <span
                                class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold mb-4">
                                <i class="fas fa-flag-checkered"></i> Evento encerrado
                            </span>
                        @endif

                        <h1
                            class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black text-white mb-3 md:mb-4 drop-shadow-lg">
                            {{ $event->title }}</h1>

                        @if($event->speaker)
                            <p class="text-lg sm:text-xl font-semibold mb-4 md:mb-6 text-white/90 drop-shadow">
                                <i class="fas fa-user-tie mr-2"></i> {{ $event->speaker }}
                            </p>
                        @endif

                        @if($event->description)
                            <p class="text-base sm:text-lg text-white/80 leading-relaxed mb-6 md:mb-8 drop-shadow">
                                {{ $event->description }}</p>
                        @endif

                        <div class="grid sm:grid-cols-2 gap-4 mb-8">
                            <div class="bg-white rounded-2xl p-5 shadow-lg">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 btn-primary rounded-xl flex items-center justify-center">
                                        <i class="fas fa-calendar-alt text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Data</p>
                                        <p class="text-lg font-bold text-gray-900">
                                            {{ $startDate->translatedFormat('d \d\e F \d\e Y') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white rounded-2xl p-5 shadow-lg">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-xl flex items-center justify-center"
                                        style="background: linear-gradient(135deg, #10B981, #14B8A6)">
                                        <i class="fas fa-clock text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Horário</p>
                                        <p class="text-lg font-bold text-gray-900">
                                            {{ $startDate->format('H:i') }}
                                            @if($endDate) às {{ $endDate->format('H:i') }} @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Card -->
                    <div class="w-full lg:w-96 shrink-0">
                        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden sticky top-28">
                            @if($eventImageUrl)
                                <div class="relative h-44">
                                    <img src="{{ $eventImageUrl }}" alt="Imagem do evento"
                                        class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                                    <div class="absolute inset-x-0 top-0 h-2" style="background-color: {{ $eventColor }}"></div>
                                </div>
                            @else
                                <div class="h-2" style="background-color: {{ $eventColor }}"></div>
                            @endif
                            <div class="p-6">
                                <div class="text-center mb-6">
                                    @if($event->current_price > 0)
                                        <span
                                            class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider mb-2">
                                            {{ $event->current_batch_label }}
                                        </span>
                                        <p class="text-sm text-gray-500 mb-1">Investimento</p>
                                        <p class="text-4xl font-black text-gray-900">R$
                                            {{ number_format($event->current_price, 2, ',', '.') }}</p>
                                        <p class="text-sm text-gray-500">por pessoa</p>
                                    @else
                                        <p class="text-sm text-gray-500 mb-1">Entrada</p>
                                        <p class="text-4xl font-black text-green-600">Gratuita</p>
                                        <p class="text-sm text-gray-500">sujeito a lotação</p>
                                    @endif
                                </div>

                                @if($event->capacity)
                                    <div class="mb-6">
                                        <div class="flex items-center justify-between text-sm mb-2">
                                            <span class="text-gray-500">Vagas disponíveis</span>
                                            <span
                                                class="font-bold {{ $remainingSeats === 0 ? 'text-red-600' : 'text-gray-900' }}">
                                                {{ $remainingSeats }} / {{ (int) $event->capacity }}
                                            </span>
                                        </div>
                                        <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                            @php
                                                $capacity = max(1, (int) $event->capacity);
                                                $percent = min(100, max(0, (int) round(($confirmedSeats / $capacity) * 100)));
                                            @endphp
                                            <div class="h-full rounded-full transition-all duration-500"
                                                style="width: {{ $percent }}%; background: linear-gradient(90deg, var(--unn-azul-1), var(--unn-azul-2))">
                                            </div>
                                        </div>
                                        @if($remainingSeats === 0)
                                            <p class="text-xs text-red-600 mt-2 font-medium">
                                                <i class="fas fa-ban mr-1"></i> Esgotado
                                            </p>
                                        @elseif($remainingSeats !== null && $remainingSeats <= 5)
                                            <p class="text-xs text-orange-600 mt-2 font-medium">
                                                <i class="fas fa-fire mr-1"></i> Últimas vagas!
                                            </p>
                                        @endif
                                    </div>
                                @endif

                                @if(isset($userRegistration) && $userRegistration)
                                    @php
                                        $ticketState = $userRegistration->ticketStatusState();
                                        $ticketExpired = $ticketState === 'expired';
                                        $ticketUsed = $ticketState === 'used';
                                        $ticketStatusMessage = $userRegistration->ticketStatusMessage();
                                        $ticketPayload = [
                                            'code' => $userRegistration->ticket_code,
                                            'title' => $event->title,
                                            'date' => $startDate->translatedFormat('d M Y, \à\s H:i'),
                                            'state' => $ticketState,
                                            'statusMessage' => $ticketStatusMessage,
                                        ];
                                    @endphp
                                    <div class="text-center">
                                        <div
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-bold mb-4 w-full justify-center {{ $ticketUsed ? 'bg-emerald-100 text-emerald-800' : 'bg-green-100 text-green-800' }}">
                                            <i class="fas {{ $ticketUsed ? 'fa-check-double' : 'fa-check-circle' }}"></i> {{ $ticketUsed ? 'Ingresso ja utilizado' : 'Vaga Confirmada' }}
                                        </div>
                                        @if($event->is_ticket_enabled && $userRegistration->ticket_code)
                                            <button type="button"
                                                onclick='showTicketModal({!! json_encode($ticketPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!})'
                                                class="w-full btn-primary text-white py-4 rounded-2xl font-bold text-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
                                                <i class="fas fa-qrcode"></i> Ver Ingresso Digital
                                            </button>
                                            @if($ticketUsed)
                                                <p class="mt-3 text-sm font-bold text-emerald-600 flex items-center justify-center gap-2">
                                                    <i class="fas fa-check-double"></i> {{ $ticketStatusMessage }}
                                                </p>
                                            @elseif($ticketExpired)
                                                <p class="mt-3 text-sm font-bold text-red-600 flex items-center justify-center gap-2">
                                                    <i class="fas fa-ban"></i> {{ $ticketStatusMessage }}
                                                </p>
                                            @endif
                                        @endif
                                    </div>
                                @elseif($isClosed)
                                    <a href="{{ route('events.index') }}"
                                        class="w-full bg-gray-200 text-gray-700 py-4 rounded-2xl font-bold text-lg hover:bg-gray-300 transition flex items-center justify-center gap-2">
                                        <i class="fas fa-calendar-check"></i> Ver próximos eventos
                                    </a>
                                @elseif($isDemo)
                                    <button type="button" data-demo="1"
                                        class="js-demo-event-alert w-full btn-primary text-white py-4 rounded-2xl font-bold text-lg opacity-75 cursor-not-allowed flex items-center justify-center gap-2">
                                        <i class="fas fa-ticket-alt"></i>
                                        {{ $event->current_price > 0 ? 'Comprar Ingresso' : 'Garantir Minha Vaga' }}
                                    </button>
                                @elseif($event->capacity && $remainingSeats === 0)
                                    <button
                                        class="w-full bg-gray-200 text-gray-700 py-4 rounded-2xl font-bold text-lg cursor-not-allowed flex items-center justify-center gap-2"
                                        disabled>
                                        <i class="fas fa-ban"></i> Esgotado
                                    </button>
                                @else
                                    <a href="{{ route('events.checkout', $event) }}"
                                        class="w-full btn-primary text-white py-4 rounded-2xl font-bold text-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
                                        <i class="fas fa-ticket-alt"></i>
                                        {{ $event->current_price > 0 ? 'Comprar Ingresso' : 'Garantir Minha Vaga' }}
                                    </a>
                                @endif

                                <div class="mt-4 flex items-center justify-center gap-4 text-sm text-gray-500">
                                    <span><i class="fas fa-lock mr-1"></i> Pagamento seguro</span>
                                    <span><i class="fas fa-undo mr-1"></i> Reembolso garantido</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Location Section -->
        <section class="py-16 px-6 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-8">
                    <i class="fas fa-map-marker-alt text-red-500 mr-2"></i> Localização
                </h2>

                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Address Card -->
                    <div class="bg-white rounded-3xl shadow-lg p-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $event->location }}</h3>
                        <p class="text-gray-600 mb-6">{{ $event->address }}</p>

                        <div class="space-y-3">
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $event->latitude }},{{ $event->longitude }}"
                                target="_blank" class="flex items-center gap-3 font-medium transition"
                                style="color: var(--unn-azul-1)">
                                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-directions"></i>
                                </div>
                                Abrir rota no Google Maps
                            </a>
                            <a href="https://waze.com/ul?ll={{ $event->latitude }},{{ $event->longitude }}&navigate=yes"
                                target="_blank"
                                class="flex items-center gap-3 text-purple-600 hover:text-purple-700 font-medium transition">
                                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                                    <i class="fab fa-waze"></i>
                                </div>
                                Abrir rota no Waze
                            </a>
                        </div>
                    </div>

                    <!-- Map -->
                    <div class="lg:col-span-2 bg-white rounded-3xl shadow-lg overflow-hidden h-[400px]">
                        @if($event->latitude && $event->longitude)
                            <iframe
                                src="https://www.openstreetmap.org/export/embed.html?bbox={{ $event->longitude - 0.005 }},{{ $event->latitude - 0.005 }},{{ $event->longitude + 0.005 }},{{ $event->latitude + 0.005 }}&layer=mapnik&marker={{ $event->latitude }},{{ $event->longitude }}"
                                class="w-full h-full border-0" loading="lazy" title="Mapa do evento"></iframe>
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                <p class="text-gray-500">Mapa não disponível</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- Event Details -->
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-8">Sobre o Evento</h2>

                <div class="prose prose-lg max-w-none">
                    <p class="text-gray-600 leading-relaxed">{{ $event->description }}</p>
                </div>

                <div class="mt-12 grid sm:grid-cols-3 gap-6">
                    <div class="bg-slate-50 rounded-2xl p-6 text-center">
                        <i class="fas fa-users text-3xl mb-3" style="color: var(--unn-azul-1)"></i>
                        <p class="text-2xl font-bold text-gray-900">{{ $event->capacity ?? '∞' }}</p>
                        <p class="text-sm text-gray-500">Participantes</p>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-6 text-center">
                        <i class="fas fa-hourglass-half text-3xl text-green-500 mb-3"></i>
                        <p class="text-2xl font-bold text-gray-900">
                            @if($endDate)
                                {{ $startDate->diffInHours($endDate) }}h
                            @else
                                —
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">Duração</p>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-6 text-center">
                        <i class="fas fa-ticket-alt text-3xl mb-3" style="color: var(--unn-azul-3)"></i>
                        <p class="text-2xl font-bold text-gray-900">
                            @if($isClosed)
                                Encerrado
                            @elseif($event->capacity && $remainingSeats === 0)
                                Esgotado
                            @else
                                Aberto
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">Inscrições</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Galeria de Fotos e Vídeos -->
        @if($event->media && $event->media->count() > 0)
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-slate-50">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-8 items-center flex gap-3">
                    <i class="fas fa-camera-retro" style="color: var(--unn-azul-1)"></i> Galeria do Evento
                </h2>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($event->media as $media)
                        <div class="group relative rounded-2xl overflow-hidden bg-gray-200 aspect-square shadow-sm hover:shadow-xl transition-all duration-300">
                            @if($media->type === 'image')
                                <a href="{{ asset('storage/' . $media->file_path) }}" data-fancybox="gallery" data-caption="{{ $event->title }} - Galeria">
                                    <img src="{{ asset('storage/' . $media->file_path) }}" alt="Foto do evento" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                </a>
                            @elseif($media->type === 'video')
                                <a href="{{ asset('storage/' . $media->file_path) }}" data-fancybox="gallery" data-caption="{{ $event->title }} - Vídeo do evento">
                                    <div class="w-full h-full bg-slate-900 flex items-center justify-center relative">
                                        <i class="fas fa-play text-white/50 text-5xl group-hover:text-white/80 transition-colors"></i>
                                    </div>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
        
        <!-- TICKET MODAL -->
<div id="ticketModal"
    class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/50 backdrop-blur-sm px-4"
    style="display: none;" aria-hidden="true">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden relative"
        onclick="event.stopPropagation()">
        <!-- Close button -->
        <button type="button" onclick="closeTicketModal()"
            class="absolute top-4 right-4 w-10 h-10 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center hover:bg-slate-200 transition-colors z-10">
            <i class="fas fa-times"></i>
        </button>

        <!-- Ticket Header (Pink/Purple vibrant gradient) -->
        <div class="h-28 px-6 pt-6 flex flex-col justify-end pb-4"
            style="background: linear-gradient(135deg, #FF6B6B 0%, #C0392B 100%);">
            <h3 class="text-white font-black text-xl leading-tight drop-shadow uppercase tracking-wider"
                id="modalTicketTitle">Meu Evento</h3>
            <p class="text-white/80 font-medium text-sm flex items-center gap-2 mt-1">
                <i class="fas fa-calendar-day"></i> <span id="modalTicketDate">00/00/0000 00:00</span>
            </p>
        </div>

        <!-- Ticket Body with QR -->
        <div class="p-6 text-center bg-zinc-50 relative">
            <!-- Cutout holes for ticket effect -->
            <div class="absolute -top-4 -left-4 w-8 h-8 rounded-full bg-black/50 backdrop-blur-sm border border-black/10 z-0 hidden lg:block"
                style="background-color: rgb(15 23 42 / 0.5);"></div>
            <div class="absolute -top-4 -right-4 w-8 h-8 rounded-full bg-black/50 backdrop-blur-sm border border-black/10 z-0 hidden lg:block"
                style="background-color: rgb(15 23 42 / 0.5);"></div>

            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Apresente este código na entrada
            </p>

            <div id="ticketStateAlert"
                class="hidden mb-5 rounded-2xl px-4 py-3 text-sm font-bold">
            </div>

            <div class="relative inline-block mb-6">
                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 inline-block"
                    id="qrcode-container">
                    <!-- QR Code vai aqui via JS -->
                </div>
                <div id="ticketStateOverlay"
                    class="hidden absolute inset-0 rounded-xl bg-white/90 backdrop-blur-[1px] border-2 flex items-center justify-center">
                    <span id="ticketStateOverlayLabel"
                        class="rotate-[-10deg] rounded-xl border-2 px-4 py-2 text-center text-lg font-black uppercase tracking-[0.12em]">
                        Expirado
                    </span>
                </div>
            </div>

            <p class="font-mono text-sm text-slate-600 tracking-widest bg-slate-100 py-2 px-4 rounded-lg select-all"
                id="modalTicketCodeString">
                XXXX-XXXX
            </p>
        </div>
    </div>
</div>
    </div>
@endsection

@push('scripts')
    <!-- Passamos o script do QRCodeJS apenas se preciso -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.js-demo-event-alert');
            if (!btn) return;

            e.preventDefault();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Evento de demonstração',
                    text: 'Este é um evento de demonstração. Configure eventos reais no painel administrativo.',
                    icon: 'info'
                });
                return;
            }

            if (typeof toastr !== 'undefined') {
                toastr.info('Este é um evento de demonstração. Configure eventos reais no painel administrativo.');
            }
        });

        const modal = document.getElementById('ticketModal');
        let qrcodeInstance = null;

        window.showTicketModal = function (payload) {
            const stateAlert = document.getElementById('ticketStateAlert');
            const stateOverlay = document.getElementById('ticketStateOverlay');
            const stateOverlayLabel = document.getElementById('ticketStateOverlayLabel');

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
                colorDark: "#1e293b", // slate-800
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

            stateAlert.className = 'hidden mb-5 rounded-2xl px-4 py-3 text-sm font-bold';
            stateOverlay.className = 'hidden absolute inset-0 rounded-xl bg-white/90 backdrop-blur-[1px] border-2 flex items-center justify-center';
            stateOverlayLabel.className = 'rotate-[-10deg] rounded-xl border-2 px-4 py-2 text-center text-lg font-black uppercase tracking-[0.12em]';

            if (payload.state === 'used') {
                stateAlert.textContent = payload.statusMessage || 'Ja utilizado.';
                stateAlert.classList.add('border', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
                stateAlert.classList.remove('hidden');
                stateOverlay.classList.remove('hidden');
                stateOverlay.classList.add('border-emerald-300');
                stateOverlayLabel.textContent = 'Ja utilizado';
                stateOverlayLabel.classList.add('border-emerald-600', 'bg-emerald-600/10', 'text-emerald-700');
            } else if (payload.state === 'expired') {
                stateAlert.textContent = payload.statusMessage || 'Ingresso invalido ou expirado.';
                stateAlert.classList.add('border', 'border-red-200', 'bg-red-50', 'text-red-700');
                stateAlert.classList.remove('hidden');
                stateOverlay.classList.remove('hidden');
                stateOverlay.classList.add('border-red-300');
                stateOverlayLabel.textContent = 'Ingresso invalido ou expirado';
                stateOverlayLabel.classList.add('border-red-600', 'bg-red-600/10', 'text-red-700');
            } else {
                stateAlert.textContent = '';
                stateAlert.classList.add('hidden');
                stateOverlay.classList.add('hidden');
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
