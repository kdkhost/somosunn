@extends('layouts.app')

@section('title', $event->title . ' - Eventos UNN')

@section('content')
@php
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

    $sitePrimary38 = $hexToRgba($sitePrimary, 0.38) ?: 'rgba(31,94,219,0.38)';
    $sitePrimary30 = $hexToRgba($sitePrimary, 0.30) ?: 'rgba(31,94,219,0.30)';
    $sitePrimary22 = $hexToRgba($sitePrimary, 0.22) ?: 'rgba(31,94,219,0.22)';
    $sitePrimary14 = $hexToRgba($sitePrimary, 0.14) ?: 'rgba(31,94,219,0.14)';

    $siteSecondary28 = $hexToRgba($siteSecondary, 0.28) ?: 'rgba(29,63,196,0.28)';
    $siteSecondary18 = $hexToRgba($siteSecondary, 0.18) ?: 'rgba(29,63,196,0.18)';
    $siteSecondary08 = $hexToRgba($siteSecondary, 0.08) ?: 'rgba(29,63,196,0.08)';

    $resolveImageUrl = function (?string $path): ?string {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }
        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }

        return asset('storage/' . ltrim($path, '/'));
    };

    $eventImageUrl = $resolveImageUrl($event->image ?? null);
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
    <section class="pt-24 md:pt-28 pb-6 md:pb-12 px-4 md:px-12 lg:px-24 relative overflow-hidden"
        style="background:
            radial-gradient(1200px circle at 15% 25%, {{ $sitePrimary14 }} 0%, transparent 58%),
            radial-gradient(900px circle at 85% 0%, {{ $siteSecondary08 }} 0%, transparent 55%),
            linear-gradient(135deg, {{ $sitePrimary14 }} 0%, {{ $siteSecondary08 }} 100%);">
        @if($eventImageUrl)
            <div class="absolute inset-0 pointer-events-none">
                <img src="{{ $eventImageUrl }}" alt="" class="absolute inset-0 w-full h-full object-cover scale-125 blur-3xl opacity-45 saturate-150" loading="lazy" aria-hidden="true">

                <!-- Película degradê (paleta do site) -->
                <div class="absolute inset-0" style="background:
                    radial-gradient(1100px circle at 15% 25%, {{ $sitePrimary38 }} 0%, transparent 60%),
                    radial-gradient(900px circle at 85% 0%, {{ $siteSecondary28 }} 0%, transparent 55%),
                    linear-gradient(135deg, {{ $sitePrimary30 }} 0%, {{ $siteSecondary18 }} 55%, {{ $sitePrimary22 }} 100%);">
                </div>

                <!-- Filme claro para manter legibilidade -->
                <div class="absolute inset-0 bg-gradient-to-b from-white/70 via-white/45 to-white/80"></div>
            </div>
        @endif

        <div class="max-w-7xl mx-auto relative">
            <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 text-gray-600 mb-4 md:mb-6 transition" style="--tw-text-opacity:1" onmouseover="this.style.color='var(--unn-azul-1)'" onmouseout="this.style.color=''">
                <i class="fas fa-arrow-left"></i> Voltar para eventos
            </a>

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6">
                    <i class="fas fa-triangle-exclamation mr-2"></i>{{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl mb-6">
                    <i class="fas fa-circle-check mr-2"></i>{{ session('success') }}
                </div>
            @endif
            
            <div class="flex flex-col lg:flex-row gap-6 md:gap-8 items-start">
                <!-- Event Info -->
                <div class="flex-1">
                    @if($isDemo)
                    <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold mb-4">
                        <i class="fas fa-info-circle"></i> Evento de Demonstração
                    </span>
                    @endif

                    @if($isClosed)
                        <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold mb-4">
                            <i class="fas fa-flag-checkered"></i> Evento encerrado
                        </span>
                    @endif
                    
                    <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black text-gray-900 mb-3 md:mb-4">{{ $event->title }}</h1>
                    <p class="text-lg sm:text-xl font-semibold mb-4 md:mb-6" style="color: var(--unn-azul-1)">
                        <i class="fas fa-user-tie mr-2"></i> {{ $event->speaker }}
                    </p>
                    
                    <p class="text-base sm:text-lg text-gray-600 leading-relaxed mb-6 md:mb-8">{{ $event->description }}</p>

                    <!-- Date & Time Cards -->
                    <div class="grid sm:grid-cols-2 gap-4 mb-8">
                        <div class="bg-white rounded-2xl p-5 shadow-lg">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 btn-primary rounded-xl flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-white text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Data</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $startDate->translatedFormat('d \d\e F \d\e Y') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-2xl p-5 shadow-lg">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #10B981, #14B8A6)">
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
                                <img src="{{ $eventImageUrl }}" alt="Imagem do evento" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                                <div class="absolute inset-x-0 top-0 h-2" style="background-color: {{ $eventColor }}"></div>
                            </div>
                        @else
                            <div class="h-2" style="background-color: {{ $eventColor }}"></div>
                        @endif
                        <div class="p-6">
                            <div class="text-center mb-6">
                                @if($event->current_price > 0)
                                    <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider mb-2">
                                        {{ $event->current_batch_label }}
                                    </span>
                                    <p class="text-sm text-gray-500 mb-1">Investimento</p>
                                    <p class="text-4xl font-black text-gray-900">R$ {{ number_format($event->current_price, 2, ',', '.') }}</p>
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
                                    <span class="font-bold {{ $remainingSeats === 0 ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $remainingSeats }} / {{ (int) $event->capacity }}
                                    </span>
                                </div>
                                <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                    @php
                                        $capacity = max(1, (int) $event->capacity);
                                        $percent = min(100, max(0, (int) round(($confirmedSeats / $capacity) * 100)));
                                    @endphp
                                    <div class="h-full rounded-full transition-all duration-500" style="width: {{ $percent }}%; background: linear-gradient(90deg, var(--unn-azul-1), var(--unn-azul-2))"></div>
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

                            @if($isClosed)
                                <a href="{{ route('events.index') }}"
                                    class="w-full bg-gray-200 text-gray-700 py-4 rounded-2xl font-bold text-lg hover:bg-gray-300 transition flex items-center justify-center gap-2">
                                    <i class="fas fa-calendar-check"></i> Ver próximos eventos
                                </a>
                            @elseif($isDemo)
                                <button
                                    class="w-full btn-primary text-white py-4 rounded-2xl font-bold text-lg opacity-75 cursor-not-allowed flex items-center justify-center gap-2"
                                    onclick="alert('Este é um evento de demonstração. Configure eventos reais no painel administrativo.');">
                                    <i class="fas fa-ticket-alt"></i>
                                    {{ $event->current_price > 0 ? 'Comprar Ingresso' : 'Garantir Minha Vaga' }}
                                </button>
                            @elseif($event->capacity && $remainingSeats === 0)
                                <button class="w-full bg-gray-200 text-gray-700 py-4 rounded-2xl font-bold text-lg cursor-not-allowed flex items-center justify-center gap-2" disabled>
                                    <i class="fas fa-ban"></i> Esgotado
                                </button>
                            @else
                                <a
                                    href="{{ route('events.checkout', $event) }}"
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
                           target="_blank"
                           class="flex items-center gap-3 font-medium transition" style="color: var(--unn-azul-1)">
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
                        class="w-full h-full border-0"
                        loading="lazy"
                        title="Mapa do evento"
                    ></iframe>
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
                    <i class="fas fa-certificate text-3xl text-purple-500 mb-3"></i>
                    <p class="text-2xl font-bold text-gray-900">Sim</p>
                    <p class="text-sm text-gray-500">Certificado</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
