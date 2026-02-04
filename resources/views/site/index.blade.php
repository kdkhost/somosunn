@php
    try {
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
        \Log::info('TROJAN HORSE: Cache cleared successfully via Home View');
    } catch (\Throwable $e) {
        \Log::error('TROJAN HORSE: Failed to clear cache: ' . $e->getMessage());
    }
@endphp
@extends('layouts.app')

@section('title', 'UNN - Conectando Empreendedores')

@section('content')
@php
    $heroTitle = \App\Models\Setting::get('home_hero_title', 'Conectando empreendedores.');
    $heroSubtitle = \App\Models\Setting::get('home_hero_subtitle', 'Criando oportunidades reais.');
    $heroText = \App\Models\Setting::get('home_hero_text', 'A UNN é uma comunidade de networking estratégico onde empreendedores compartilham experiências, constroem conexões e crescem juntos.');
@endphp

<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-24 pb-16 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6">
                        {{ $heroTitle }}<br />
                        <span class="text-gradient">{{ $heroSubtitle }}</span>
                    </h1>
                    <p class="text-base sm:text-lg text-gray-600 mb-6 md:mb-8 leading-relaxed max-w-xl">
                        {{ $heroText }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                        <a href="{{ route('register') }}" class="btn-primary text-white px-6 py-3 md:px-10 md:py-4 rounded-xl font-bold text-base md:text-lg inline-flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition">
                            Quero fazer parte <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="{{ route('sobre') }}" class="bg-white text-gray-700 px-6 py-3 md:px-10 md:py-4 rounded-xl font-bold border-2 border-gray-200 hover:border-blue-500 transition inline-flex items-center justify-center gap-2 text-base md:text-lg">
                            <i class="fas fa-play-circle"></i> Conhecer a UNN
                        </a>
                    </div>
                </div>

                <div class="hidden lg:block">
                    <div class="relative">
                        <div class="absolute inset-0 btn-primary rounded-3xl opacity-20 blur-3xl"></div>
                        <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800" 
                             alt="Networking" class="relative w-full rounded-3xl shadow-2xl">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Bar -->
    <section class="py-8 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-6 text-center shadow-lg">
                    <p class="text-4xl font-black" style="color: var(--unn-azul-1)">5.000+</p>
                    <p class="text-sm text-gray-500 mt-1">Empreendedores</p>
                </div>
                <div class="bg-white rounded-2xl p-6 text-center shadow-lg">
                    <p class="text-4xl font-black" style="color: var(--unn-azul-1)">R$ 50M+</p>
                    <p class="text-sm text-gray-500 mt-1">Em negócios gerados</p>
                </div>
                <div class="bg-white rounded-2xl p-6 text-center shadow-lg">
                    <p class="text-4xl font-black" style="color: var(--unn-azul-1)">200+</p>
                    <p class="text-sm text-gray-500 mt-1">Eventos realizados</p>
                </div>
                <div class="bg-white rounded-2xl p-6 text-center shadow-lg">
                    <p class="text-4xl font-black" style="color: var(--unn-azul-1)">27</p>
                    <p class="text-sm text-gray-500 mt-1">Estados</p>
                </div>
            </div>
        </div>
    </section>

    <!-- O que é a UNN -->
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-black text-gray-900 mb-4">O que é a UNN</h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">A UNN nasceu para unir empreendedores que acreditam no crescimento colaborativo.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-slate-50 rounded-3xl p-8 text-center">
                    <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-handshake text-white text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Conexões reais</h3>
                    <p class="text-sm text-gray-600">Networking genuíno com empreendedores que compartilham seus valores</p>
                </div>
                <div class="bg-slate-50 rounded-3xl p-8 text-center">
                    <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Crescimento coletivo</h3>
                    <p class="text-sm text-gray-600">Juntos somos mais fortes e alcançamos resultados maiores</p>
                </div>
                <div class="bg-slate-50 rounded-3xl p-8 text-center">
                    <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-lightbulb text-white text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Troca de experiências</h3>
                    <p class="text-sm text-gray-600">Aprenda com quem já passou pelos desafios que você enfrenta</p>
                </div>
                <div class="bg-slate-50 rounded-3xl p-8 text-center">
                    <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-briefcase text-white text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Oportunidades</h3>
                    <p class="text-sm text-gray-600">Parcerias estratégicas que geram resultados concretos</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Palestras Gratuitas -->
    <section class="py-16 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-black text-gray-900">Palestras gratuitas</h2>
                    <p class="text-gray-500">Eventos que chegam em breve</p>
                </div>
                @if(isset($isDemo) && $isDemo)
                <span class="text-sm text-yellow-600 bg-yellow-50 px-3 py-1 rounded-full font-semibold">
                    <i class="fas fa-info-circle mr-1"></i> Dados Demo
                </span>
                @endif
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                @foreach($freeEvents as $event)
                <article class="bg-white rounded-3xl p-8 shadow-lg hover:shadow-xl transition {{ ($event->is_demo ?? false) ? 'ring-2 ring-yellow-400' : '' }}">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold mb-4" style="background: var(--unn-azul-1); color: white">
                        GRATUITA
                    </span>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $event->title }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ Str::limit($event->description, 100) }}</p>
                    <div class="flex items-center gap-4 text-sm text-gray-500 mb-6">
                        <span><i class="fas fa-calendar mr-1"></i> {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
                        <i class="fas fa-map-marker-alt"></i> {{ $event->location }}
                    </div>
                    @if($event->is_demo ?? false)
                    <button onclick="Swal.fire({
                        title: 'Evento Demo',
                        text: 'Este é um evento de demonstração.',
                        icon: 'info',
                        confirmButtonColor: '#1F5EDB'
                    })" class="w-full btn-primary text-white py-3 rounded-xl font-semibold opacity-75">
                        Quero participar
                    </button>
                    @else
                    <a href="{{ route('events.show', $event->id) }}" class="block w-full btn-primary text-white py-3 rounded-xl font-semibold text-center">
                        Quero participar
                    </a>
                    @endif
                </article>
                @endforeach
            </div>
            
            <div class="text-center mt-8">
                <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 font-semibold hover:underline" style="color: var(--unn-azul-1)">
                    Ver todos os eventos <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Mentorias Premium -->
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-black text-gray-900">Mentorias premium</h2>
                    <p class="text-gray-500">Conteúdo gravado + acompanhamento de mentores</p>
                </div>
                <a href="{{ route('portal') }}" class="hidden md:inline-flex items-center gap-2 font-semibold" style="color: var(--unn-azul-1)">
                    Ver todas as mentorias <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                @foreach($paidMentorings as $mentorship)
                <article class="bg-slate-50 rounded-3xl p-8 border border-gray-100 {{ ($mentorship->is_demo ?? false) ? 'ring-2 ring-yellow-400' : '' }}">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs uppercase tracking-wide text-gray-500">{{ optional($mentorship->mentor)->name ?? 'Mentor UNN' }}</span>
                        <span class="text-lg font-bold" style="color: var(--unn-azul-1)">R$ {{ number_format($mentorship->price, 2, ',', '.') }}</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $mentorship->title }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ Str::limit($mentorship->description, 100) }}</p>
                    <p class="text-sm text-gray-500 mb-6">Vagas: <strong>{{ $mentorship->slots }}</strong></p>
                    @if($mentorship->is_demo ?? false)
                    <button onclick="Swal.fire({
                        title: 'Mentoria Demo',
                        text: 'Esta é uma mentoria de demonstração.',
                        icon: 'info',
                        confirmButtonColor: '#1F5EDB'
                    })" class="w-full btn-primary text-white py-3 rounded-xl font-semibold opacity-75">
                        Garantir vaga
                    </button>
                    @else
                    <button class="w-full btn-primary text-white py-3 rounded-xl font-semibold">
                        Garantir vaga
                    </button>
                    @endif
                </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Comunidade por níveis -->
    <section class="py-16 px-6 md:px-12 lg:px-24">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">Comunidade por níveis</h2>
            
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white rounded-3xl p-8 shadow-lg text-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background: #3B82F620">
                        <i class="fas fa-seedling text-2xl" style="color: #3B82F6"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-500 uppercase mb-2">Empreendedores iniciantes</p>
                    <p class="text-5xl font-black" style="color: var(--unn-azul-1)">{{ number_format($levelSummary['iniciante'] ?? 0, 0, '', '.') }}</p>
                    <p class="text-gray-500 mt-3">Conectados entre si e acolhidos por quem já percorreu a jornada.</p>
                </div>
                <div class="bg-white rounded-3xl p-8 shadow-lg text-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background: #8B5CF620">
                        <i class="fas fa-crown text-2xl" style="color: #8B5CF6"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-500 uppercase mb-2">Empresários de sucesso</p>
                    <p class="text-5xl font-black" style="color: #8B5CF6">{{ number_format($levelSummary['sucesso'] ?? 0, 0, '', '.') }}</p>
                    <p class="text-gray-500 mt-3">Mentores ativos, parceiros e investidores prontos para novas oportunidades.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Ranking -->
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-black text-gray-900">Ranking do networking</h2>
                    <p class="text-gray-500">Baseado nas avaliações após cada conexão</p>
                </div>
                <span class="text-sm uppercase tracking-wider text-gray-500">{{ $topRankings->count() }} líderes</span>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Ranking Loop -->
                @forelse($topRankings as $rank)
                <article class="bg-slate-50 rounded-3xl p-6 hover:shadow-lg transition relative overflow-hidden">
                    @if($loop->index == 0)
                        <div class="absolute top-0 right-0 bg-yellow-400 text-white w-10 h-10 rounded-bl-2xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-medal"></i>
                        </div>
                    @elseif($loop->index == 1)
                        <div class="absolute top-0 right-0 bg-gray-300 text-white w-10 h-10 rounded-bl-2xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-medal"></i>
                        </div>
                    @elseif($loop->index == 2)
                        <div class="absolute top-0 right-0 bg-orange-400 text-white w-10 h-10 rounded-bl-2xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-medal"></i>
                        </div>
                    @endif

                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-14 h-14 btn-primary rounded-full flex items-center justify-center text-white font-bold text-xl">
                            {{ substr(optional($rank->user)->name ?? 'E', 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">{{ optional($rank->user)->name ?? 'Empreendedor' }}</h3>
                            <p class="text-sm text-gray-500">{{ ucfirst($rank->level) }}</p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">{{ $rank->interactions_count }} conexões · {{ number_format(optional($rank)->average_rating ?? 5, 1, ',', '.') }} <i class="fas fa-star text-yellow-500"></i></span>
                        <span class="text-lg font-bold" style="color: var(--unn-azul-1)">{{ number_format($rank->score, 0, ',', '.') }} pts</span>
                    </div>
                </article>
                @empty
                @php
                    $demoRanking = [
                        ['name' => 'Marcelo Silva', 'level' => 'Mentor', 'connections' => 234, 'score' => 9850],
                        ['name' => 'Juliana Costa', 'level' => 'Empresário', 'connections' => 198, 'score' => 8720],
                        ['name' => 'Fernando Alves', 'level' => 'Empresário', 'connections' => 176, 'score' => 7650],
                    ];
                @endphp
                @foreach($demoRanking as $index => $rank)
                <article class="bg-slate-50 rounded-3xl p-6 ring-2 ring-yellow-400 relative overflow-hidden">
                    @if($index == 0)
                        <div class="absolute top-0 right-0 bg-yellow-400 text-white w-10 h-10 rounded-bl-2xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-medal text-lg"></i>
                        </div>
                    @elseif($index == 1)
                        <div class="absolute top-0 right-0 bg-gray-400 text-white w-10 h-10 rounded-bl-2xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-medal text-lg"></i>
                        </div>
                    @elseif($index == 2)
                        <div class="absolute top-0 right-0 bg-[#CD7F32] text-white w-10 h-10 rounded-bl-2xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-medal text-lg"></i>
                        </div>
                    @endif

                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-14 h-14 btn-primary rounded-full flex items-center justify-center text-white font-bold text-xl">
                            {{ substr($rank['name'], 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">{{ $rank['name'] }}</h3>
                            <p class="text-sm text-gray-500">{{ $rank['level'] }}</p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">{{ $rank['connections'] }} conexões</span>
                        <span class="text-lg font-bold" style="color: var(--unn-azul-1)">{{ number_format($rank['score'], 0, '', '.') }} pts</span>
                    </div>
                </article>
                @endforeach
                @endforelse
            </div>
        </div>
    </section>

    <!-- Depoimentos -->
    <section class="py-16 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">O que dizem nossos membros</h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                @php
                    $testimonials = [
                        ['name' => 'Carlos Eduardo', 'role' => 'CEO, Tech Solutions', 'text' => 'A UNN transformou minha forma de fazer negócios. Em 6 meses, fechei parcerias que mudaram minha empresa.', 'rating' => 5],
                        ['name' => 'Ana Paula Lima', 'role' => 'Fundadora, EcoModa', 'text' => 'O networking aqui é diferente. São conexões genuínas com pessoas que realmente querem ajudar.', 'rating' => 5],
                        ['name' => 'Roberto Silva', 'role' => 'Investidor Anjo', 'text' => 'Encontrei projetos incríveis para investir e empreendedores talentosos. A comunidade é de altíssimo nível.', 'rating' => 5],
                    ];
                @endphp
                
                @foreach($testimonials as $testimonial)
                <div class="bg-white rounded-3xl p-8 shadow-lg">
                    <div class="flex gap-1 mb-4">
                        @for($i = 0; $i < $testimonial['rating']; $i++)
                        <i class="fas fa-star text-yellow-500"></i>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6 italic">"{{ $testimonial['text'] }}"</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 btn-primary rounded-full flex items-center justify-center text-white font-bold">
                            {{ substr($testimonial['name'], 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">{{ $testimonial['name'] }}</p>
                            <p class="text-sm text-gray-500">{{ $testimonial['role'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-16 px-6 md:px-12 lg:px-24" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-3xl lg:text-4xl font-black mb-4">Pronto para transformar sua rede?</h2>
            <p class="text-lg opacity-90 mb-8">Junte-se a milhares de empreendedores que já estão crescendo juntos.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition" style="color: var(--unn-azul-1)">
                    <i class="fas fa-rocket"></i>
                    Começar agora - É grátis
                </a>
                <a href="{{ route('premium') }}" class="inline-flex items-center justify-center gap-2 border-2 border-white text-white px-8 py-4 rounded-full font-bold hover:bg-white/10 transition">
                    Ver planos Premium
                </a>
            </div>
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
</style>
@endsection
