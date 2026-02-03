@extends('layouts.app')

@section('title', $event->title . ' - Eventos UNN')

@section('content')
@php
    $isDemo = $event->is_demo ?? false;
    $startDate = is_string($event->start_at) ? \Carbon\Carbon::parse($event->start_at) : $event->start_at;
    $endDate = is_string($event->end_at) ? \Carbon\Carbon::parse($event->end_at) : $event->end_at;
    $eventColor = $event->color ?? '#1F5EDB';
    $mapQuery = urlencode($event->address);
@endphp

<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-28 pb-12 px-6 md:px-12 lg:px-24" style="background: linear-gradient(135deg, {{ $eventColor }}20 0%, {{ $eventColor }}05 100%);">
        <div class="max-w-7xl mx-auto">
            <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 text-gray-600 mb-6 transition" style="--tw-text-opacity:1" onmouseover="this.style.color='var(--unn-azul-1)'" onmouseout="this.style.color=''">
                <i class="fas fa-arrow-left"></i> Voltar para eventos
            </a>
            
            <div class="flex flex-col lg:flex-row gap-8 items-start">
                <!-- Event Info -->
                <div class="flex-1">
                    @if($isDemo)
                    <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold mb-4">
                        <i class="fas fa-info-circle"></i> Evento de Demonstração
                    </span>
                    @endif
                    
                    <h1 class="text-4xl lg:text-5xl font-black text-gray-900 mb-4">{{ $event->title }}</h1>
                    <p class="text-xl font-semibold mb-6" style="color: var(--unn-azul-1)">
                        <i class="fas fa-user-tie mr-2"></i> {{ $event->speaker }}
                    </p>
                    
                    <p class="text-lg text-gray-600 leading-relaxed mb-8">{{ $event->description }}</p>

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
                                    <p class="text-lg font-bold text-gray-900">{{ $startDate->format('H:i') }} às {{ $endDate->format('H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticket Card -->
                <div class="w-full lg:w-96 shrink-0">
                    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden sticky top-28">
                        <div class="h-2" style="background-color: {{ $eventColor }}"></div>
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
                                    <span class="font-bold text-gray-900">{{ $event->capacity }} lugares</span>
                                </div>
                                <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500" style="width: {{ rand(30, 70) }}%; background: linear-gradient(90deg, var(--unn-azul-1), var(--unn-azul-2))"></div>
                                </div>
                                <p class="text-xs text-orange-600 mt-2 font-medium">
                                    <i class="fas fa-fire mr-1"></i> Últimas vagas!
                                </p>
                            </div>
                            @endif

                            <button 
                                class="w-full btn-primary text-white py-4 rounded-2xl font-bold text-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 {{ $isDemo ? 'opacity-75 cursor-not-allowed' : '' }}"
                                @if($isDemo) onclick="alert('Este é um evento de demonstração. Configure eventos reais no painel administrativo.');" @endif>
                                <i class="fas fa-ticket-alt"></i>
                                {{ $event->price > 0 ? 'Comprar Ingresso' : 'Garantir Minha Vaga' }}
                            </button>

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
                    <p class="text-2xl font-bold text-gray-900">{{ $startDate->diffInHours($endDate) }}h</p>
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
