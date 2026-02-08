@extends($extends ?? 'layouts.app')

@section('title', 'Portal de Networking - UNN')

@section('content')
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6">
                Portal de <span class="text-gradient">Networking</span>
            </h1>
            <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Acesse palestras, mentorias premium e recursos exclusivos para potencializar seu crescimento empreendedor.
            </p>
        </div>
    </section>

    <!-- Stats -->
    <section class="pb-12 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                    <p class="text-2xl sm:text-3xl md:text-4xl font-black truncate" style="color: var(--unn-azul-1)">120+</p>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Palestras</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                    <p class="text-2xl sm:text-3xl md:text-4xl font-black truncate" style="color: var(--unn-azul-1)">50+</p>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Mentorias</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                    <p class="text-2xl sm:text-3xl md:text-4xl font-black truncate" style="color: var(--unn-azul-1)">5.000+</p>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Membros</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                    <p class="text-2xl sm:text-3xl md:text-4xl font-black truncate" style="color: var(--unn-azul-1)">95%</p>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Satisfação</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Access -->
    <section class="py-12 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-8">Acesso Rápido</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="{{ route('social.feed') }}" class="bg-white rounded-3xl p-6 shadow-lg hover:shadow-xl transition group">
                    <div class="w-14 h-14 btn-primary rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <i class="fas fa-newspaper text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Feed Social</h3>
                    <p class="text-sm text-gray-500">Conecte-se com outros membros</p>
                </a>
                <a href="{{ route('courses.index') }}" class="bg-white rounded-3xl p-6 shadow-lg hover:shadow-xl transition group">
                    <div class="w-14 h-14 btn-primary rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <i class="fas fa-graduation-cap text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Cursos</h3>
                    <p class="text-sm text-gray-500">Aprenda com especialistas</p>
                </a>
                <a href="{{ route('events.index') }}" class="bg-white rounded-3xl p-6 shadow-lg hover:shadow-xl transition group">
                    <div class="w-14 h-14 btn-primary rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <i class="fas fa-calendar-alt text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Eventos</h3>
                    <p class="text-sm text-gray-500">Participe de encontros</p>
                </a>
                <a href="{{ route('membros') }}" class="bg-white rounded-3xl p-6 shadow-lg hover:shadow-xl transition group">
                    <div class="w-14 h-14 btn-primary rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Membros</h3>
                    <p class="text-sm text-gray-500">Conheça a comunidade</p>
                </a>
            </div>
        </div>
    </section>

    <!-- Mentorias Demo -->
            @if(isset($mentorings) && $mentorings->count() > 0)
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-black text-gray-900">Mentorias Disponíveis</h2>
                @if(isset($isDemo) && $isDemo)
                <span class="text-sm text-yellow-600 bg-yellow-50 px-3 py-1 rounded-full font-semibold">
                    <i class="fas fa-info-circle mr-1"></i> Dados Demo
                </span>
                @endif
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($mentorings as $mentorship)
                <article class="bg-slate-50 rounded-3xl p-8 border border-gray-100">
                    <div class="flex justify-between items-center mb-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500">{{ optional($mentorship->mentor)->name ?? 'Mentor UNN' }}</p>
                        <span class="font-bold" style="color: var(--unn-azul-1)">R$ {{ number_format($mentorship->price, 2, ',', '.') }}</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $mentorship->title }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ Str::limit($mentorship->description, 100) }}</p>
                    <p class="text-sm text-gray-500 mb-4">Vagas: <strong>{{ $mentorship->slots }}</strong></p>
                    @if(isset($mentorship->id))
                    <a href="{{ route('mentorships.show', $mentorship->id) }}" class="btn-primary text-white px-6 py-3 rounded-xl font-semibold w-full block text-center">
                        Ver detalhes
                    </a>
                    @else
                    <button class="btn-primary text-white px-6 py-3 rounded-xl font-semibold w-full opacity-70 cursor-not-allowed">
                        Ver detalhes (Demo)
                    </button>
                    @endif
                </article>
                @endforeach
            </div>
            @endif
        </div>
    </section>

    <!-- Comunidade Segmentada -->
    <section class="py-16 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">Níveis da Comunidade</h2>
            
            <div class="grid md:grid-cols-4 gap-6">
                @php
                    $levels = [
                        ['name' => 'Iniciante', 'count' => 1200, 'icon' => 'seedling', 'color' => '#10B981', 'desc' => 'Começando a jornada'],
                        ['name' => 'Empreendedor', 'count' => 2500, 'icon' => 'rocket', 'color' => '#3B82F6', 'desc' => 'Em crescimento'],
                        ['name' => 'Empresário', 'count' => 800, 'icon' => 'building', 'color' => '#8B5CF6', 'desc' => 'Consolidado'],
                        ['name' => 'Mentor', 'count' => 150, 'icon' => 'crown', 'color' => '#F59E0B', 'desc' => 'Elite da comunidade'],
                    ];
                @endphp
                
                @foreach($levels as $level)
                <div class="bg-white rounded-3xl p-6 text-center shadow-lg">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background: {{ $level['color'] }}20">
                        <i class="fas fa-{{ $level['icon'] }} text-2xl" style="color: {{ $level['color'] }}"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">{{ $level['name'] }}</h3>
                    <p class="text-3xl font-black mb-2" style="color: {{ $level['color'] }}">{{ number_format($level['count'], 0, '', '.') }}</p>
                    <p class="text-xs text-gray-500">{{ $level['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Ranking -->
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-black text-gray-900">Top Networkers</h2>
                <span class="text-sm text-gray-500">Ranking baseado em conexões</span>
            </div>
            
            <div class="grid md:grid-cols-3 gap-6">
                @php
                    $topRankers = [
                        ['name' => 'Marcelo Silva', 'score' => 9850, 'connections' => 234, 'level' => 'Mentor', 'position' => 1],
                        ['name' => 'Juliana Costa', 'score' => 8720, 'connections' => 198, 'level' => 'Empresário', 'position' => 2],
                        ['name' => 'Fernando Alves', 'score' => 7650, 'connections' => 176, 'level' => 'Empresário', 'position' => 3],
                    ];
                @endphp
                
                @foreach($topRankers as $ranker)
                <div class="bg-gradient-to-br from-slate-50 to-white rounded-3xl p-6 border border-gray-100 relative overflow-hidden">
                    @if($ranker['position'] === 1)
                    <div class="absolute top-4 right-4">
                        <i class="fas fa-trophy text-2xl text-yellow-500"></i>
                    </div>
                    @endif
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-14 h-14 btn-primary rounded-full flex items-center justify-center text-white font-bold text-xl">
                            {{ substr($ranker['name'], 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">{{ $ranker['name'] }}</h3>
                            <p class="text-sm text-gray-500">{{ $ranker['level'] }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div class="text-center p-3 bg-slate-50 rounded-xl">
                            <p class="text-xl font-bold" style="color: var(--unn-azul-1)">{{ number_format($ranker['score'], 0, '', '.') }}</p>
                            <p class="text-xs text-gray-500">Pontos</p>
                        </div>
                        <div class="text-center p-3 bg-slate-50 rounded-xl">
                            <p class="text-xl font-bold" style="color: var(--unn-azul-1)">{{ $ranker['connections'] }}</p>
                            <p class="text-xs text-gray-500">Conexões</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Premium -->
    <section class="py-16 px-6 md:px-12 lg:px-24" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-3xl lg:text-4xl font-black mb-4">Desbloqueie todos os recursos</h2>
            <p class="text-lg opacity-90 mb-8">Torne-se Premium e tenha acesso ilimitado a mentorias, cursos e eventos exclusivos.</p>
            <a href="{{ route('premium') }}" class="inline-flex items-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition" style="color: var(--unn-azul-1)">
                <i class="fas fa-crown"></i>
                Conhecer planos Premium
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
</style>
@endsection
