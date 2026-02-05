@extends('admin.layouts.app')

@section('title', 'Próximos Eventos - UNN')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Próximos Eventos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Eventos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        @if(isset($isDemo) && $isDemo)
        <div class="alert alert-warning">
            <i class="icon fas fa-exclamation-triangle"></i>
            <strong>Dados de Demonstração:</strong> Estes eventos são exemplos. Configure eventos reais no painel administrativo.
        </div>
        @endif

        <div class="card">
            <div class="card-header bg-white border-bottom-0">
                <h3 class="card-title">Agenda de Eventos</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="width: 15%">Data</th>
                                <th style="width: 30%">Evento</th>
                                <th style="width: 20%">Localização</th>
                                <th style="width: 15%">Vagas</th>
                                <th style="width: 10%">Valor</th>
                                <th style="width: 10%">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $event)
                                @php
                                    $startDate = is_string($event->start_at) ? \Carbon\Carbon::parse($event->start_at) : $event->start_at;
                                    $endDate = is_string($event->end_at) ? \Carbon\Carbon::parse($event->end_at) : $event->end_at;
                                @endphp
                                <tr>
                                    <td class="align-middle">
                                        <div class="text-center bg-light rounded p-2 border" style="width: 60px;">
                                            <span class="d-block font-weight-bold text-lg leading-none">{{ $startDate->format('d') }}</span>
                                            <span class="d-block text-uppercase small font-weight-bold text-muted">{{ $startDate->translatedFormat('M') }}</span>
                                        </div>
                                        <small class="d-block mt-1 text-muted text-center">{{ $startDate->format('H:i') }}</small>
                                    </td>
                                    <td class="align-middle">
                                        <h5 class="font-weight-bold mb-1 text-primary">{{ $event->title }}</h5>
                                        <span class="text-muted small"><i class="fas fa-user-tie mr-1"></i> {{ $event->speaker }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="d-block"><i class="fas fa-map-marker-alt text-danger mr-1"></i> {{ $event->location }}</span>
                                        <small class="text-muted d-block text-truncate" style="max-width: 200px;">{{ $event->address }}</small>
                                    </td>
                                    <td class="align-middle">
                                        @if($event->capacity)
                                            <div class="progress progress-sm rounded mb-1" style="height: 6px;">
                                                <div class="progress-bar bg-success" style="width: {{ rand(30, 80) }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $event->capacity }} vagas totais</small>
                                        @else
                                            <span class="badge badge-success">Ilimitado</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($event->current_price > 0 || $event->price > 0)
                                            <span class="font-weight-bold text-success">R$ {{ number_format($event->current_price ?: $event->price, 2, ',', '.') }}</span>
                                        @else
                                            <span class="badge badge-success">GRÁTIS</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-right">
                                        <a href="{{ isset($isDemo) && $isDemo ? '#' : route('events.show', $event->id) }}" class="btn btn-primary btn-sm shadow-sm">
                                            <i class="fas fa-ticket-alt mr-1"></i> Detalhes
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                            <h5>Nenhum evento próximo encontrado</h5>
                                            <p>Fique atento, novas datas serão liberadas em breve.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6">
                <span class="text-gradient">Eventos</span> UNN
            </h1>
            <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Encontros presenciais e online para conectar você com os melhores empreendedores do Brasil.
            </p>
        </div>
    </section>

    @if(isset($isDemo) && $isDemo)
    <div class="max-w-7xl mx-auto px-6 mb-8">
        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 flex items-center gap-3">
            <i class="fas fa-info-circle text-yellow-600 text-xl"></i>
            <p class="text-yellow-800">
                <strong>Dados de Demonstração:</strong> Estes eventos são exemplos. Configure eventos reais no painel administrativo.
            </p>
        </div>
    </div>
    @endif

    <!-- Events Grid -->
    <section class="pb-20 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($events as $event)
                    @php
                        $isDemo = $event->is_demo ?? false;
                        $startDate = is_string($event->start_at) ? \Carbon\Carbon::parse($event->start_at) : $event->start_at;
                        $endDate = is_string($event->end_at) ? \Carbon\Carbon::parse($event->end_at) : $event->end_at;
                        $eventColor = $event->color ?? '#1F5EDB';
                    @endphp
                    <article class="bg-white rounded-3xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 {{ $isDemo ? 'ring-2 ring-yellow-400' : '' }}">
                        <!-- Event Header with Color -->
                        <div class="h-3" style="background-color: {{ $eventColor }}"></div>
                        
                        <!-- Date Badge -->
                        <div class="px-6 pt-6">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="btn-primary text-white rounded-2xl p-4 text-center min-w-[80px]">
                                        <p class="text-2xl font-black">{{ $startDate->format('d') }}</p>
                                        <p class="text-xs uppercase tracking-wide">{{ $startDate->translatedFormat('M') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            <i class="far fa-clock mr-1"></i>
                                            {{ $startDate->format('H:i') }} - {{ $endDate->format('H:i') }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            <i class="far fa-calendar mr-1"></i>
                                            {{ $startDate->translatedFormat('l') }}
                                        </p>
                                    </div>
                                </div>
                                @if($isDemo)
                                    <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full font-semibold">DEMO</span>
                                @endif
                            </div>
                        </div>

                        <!-- Event Content -->
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $event->title }}</h3>
                            <p class="text-sm font-medium mb-3" style="color: var(--unn-azul-1)">
                                <i class="fas fa-user-tie mr-1"></i> {{ $event->speaker }}
                            </p>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $event->description }}</p>
                            
                            <!-- Location -->
                            <div class="bg-slate-50 rounded-xl p-4 mb-4">
                                <p class="font-semibold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-red-500"></i>
                                    {{ $event->location }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1">{{ $event->address }}</p>
                            </div>

                            <!-- Mini Map Preview -->
                            @if($event->latitude && $event->longitude)
                            <div class="rounded-xl overflow-hidden h-32 mb-4 relative">
                                <iframe 
                                    src="https://www.openstreetmap.org/export/embed.html?bbox={{ $event->longitude - 0.01 }},{{ $event->latitude - 0.01 }},{{ $event->longitude + 0.01 }},{{ $event->latitude + 0.01 }}&layer=mapnik&marker={{ $event->latitude }},{{ $event->longitude }}"
                                    class="w-full h-full border-0"
                                    loading="lazy"
                                ></iframe>
                                <a href="https://www.openstreetmap.org/?mlat={{ $event->latitude }}&mlon={{ $event->longitude }}#map=17/{{ $event->latitude }}/{{ $event->longitude }}" 
                                   target="_blank"
                                   class="absolute bottom-2 right-2 bg-white/90 hover:bg-white text-gray-700 text-xs px-2 py-1 rounded-lg shadow transition">
                                    <i class="fas fa-expand-alt mr-1"></i> Ver mapa
                                </a>
                            </div>
                            @endif

                            <!-- Price and CTA -->
                            <div class="pt-4 border-t border-gray-100">
                                <div class="text-center mb-4">
                                    @if($event->current_price > 0)
                                        <div class="mb-1">
                                            <span class="inline-block px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-wider">
                                                {{ $event->current_batch_label }}
                                            </span>
                                        </div>
                                        <p class="text-3xl font-black text-gray-900">R$ {{ number_format($event->current_price, 2, ',', '.') }}</p>
                                        <p class="text-sm text-gray-500">por pessoa</p>
                                    @else
                                        <div class="mb-1 h-6"></div> <!-- Spacer for alignment -->
                                        <p class="text-3xl font-black text-green-600">Gratuito</p>
                                        <p class="text-sm text-gray-500">entrada liberada</p>
                                    @endif
                                </div>
                                <a href="{{ $isDemo ? '#' : route('events.show', $event->id) }}" 
                                   class="w-full flex items-center justify-center gap-2 btn-primary text-white px-6 py-4 rounded-2xl font-bold text-lg shadow-lg hover:shadow-xl transition-all duration-300 {{ $isDemo ? 'opacity-75 cursor-not-allowed' : '' }}"
                                   @if($isDemo) onclick="alert('Este é um evento de demonstração. Configure eventos reais no painel administrativo.'); return false;" @endif>
                                    <i class="fas fa-ticket-alt"></i>
                                    Garantir Minha Vaga
                                </a>
                            </div>

                            <!-- Capacity -->
                            @if($event->capacity)
                            <div class="mt-4">
                                <div class="flex items-center justify-between text-sm text-gray-500 mb-1">
                                    <span>Vagas disponíveis</span>
                                    <span class="font-semibold">{{ $event->capacity }} lugares</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="width: {{ rand(30, 80) }}%; background: linear-gradient(90deg, var(--unn-azul-1), var(--unn-azul-2))"></div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-16">
                        <div class="bg-white rounded-3xl p-12 shadow-lg max-w-md mx-auto">
                            <i class="fas fa-calendar-alt text-6xl text-gray-300 mb-6"></i>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Nenhum evento disponível</h3>
                            <p class="text-gray-500">Novos eventos serão anunciados em breve. Fique ligado!</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 px-6 md:px-12 lg:px-24" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-3xl lg:text-4xl font-black mb-4">Quer ser notificado sobre novos eventos?</h2>
            <p class="text-lg opacity-90 mb-8">Cadastre-se e receba em primeira mão informações sobre nossos encontros exclusivos.</p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition" style="color: var(--unn-azul-1)">
                <i class="fas fa-bell"></i>
                Quero ser avisado
            </a>
        </div>
    </section>
</div>

<style>
.text-gradient {
    background: linear-gradient(135deg, var(--unn-azul-1) 0%, var(--unn-azul-3) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
