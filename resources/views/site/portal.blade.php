@extends($extends ?? 'layouts.app')

@section('title', 'Portal de Networking - UNN')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-white">
    <!-- Hero Section -->
    <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6">
                <span class="unn-gradient-title">Portal de Networking</span>
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

    <!-- Cursos em Destaque (Carrossel) -->
    @if(isset($featuredCourses) && $featuredCourses->count() > 0)
    <section class="pt-12 pb-8 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-2">
                <div>
                    <h2 class="text-2xl md:text-3xl font-black text-gray-900">Cursos em Destaque</h2>
                    <p class="text-gray-500 text-sm md:text-base">Desenvolva novas habilidades com os especialistas</p>
                </div>
                <a href="{{ route('courses.index') }}" class="text-sm font-bold text-white bg-blue-700 hover:bg-blue-800 px-5 py-2 rounded-full shadow transition">Ver Todos</a>
            </div>
            <div id="featured-courses-list" class="grid md:grid-cols-3 gap-6 overflow-hidden">
                @foreach($featuredCourses as $course)
                <article class="bg-white rounded-2xl p-6 border border-gray-100 flex flex-col shadow-sm relative transition-all duration-500">
                    @if($course->price > 0 && $course->price < 100)
                        <span class="absolute top-4 right-4 bg-purple-600 text-white text-xs font-bold px-3 py-1 rounded-full">Premium</span>
                    @elseif($course->price > 0)
                        <span class="absolute top-4 right-4 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">Pago</span>
                    @else
                        <span class="absolute top-4 right-4 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full">Grátis</span>
                    @endif
                    <img src="{{ $course->thumbnail ?? asset('img/course-default.png') }}" alt="{{ $course->title }}" class="w-full h-40 object-cover rounded-xl mb-4">
                    <div class="mb-2 flex items-center gap-2">
                        <span class="text-xs font-bold text-blue-600">{{ $course->category ?? 'Categoria' }}</span>
                        <span class="text-xs text-gray-400 flex items-center gap-1"><i class="fas fa-clock"></i> {{ $course->total_hours ?? $course->getTotalHoursAttribute() }} horas</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $course->title }}</h3>
                    <p class="text-gray-600 text-sm mb-2">{{ Str::limit($course->short_description, 80) }}</p>
                    <p class="text-xs text-gray-500 mb-2">Por {{ $course->author_name ?? 'Especialista UNN' }}</p>
                    <div class="mt-auto flex flex-col gap-2">
                        @if($course->price > 0)
                            <span class="text-base font-bold text-gray-900">R$ {{ number_format($course->price, 2, ',', '.') }}</span>
                        @else
                            <span class="text-base font-bold text-green-600">GRÁTIS</span>
                        @endif
                        <a href="{{ route('courses.show', $course->slug) }}" class="btn-primary text-white px-6 py-3 rounded-xl font-semibold w-full block text-center mt-2">@if($course->price > 0) Saber Mais @else Começar Agora @endif</a>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Palestras Gratuitas -->
    @if(isset($freeEvents) && $freeEvents->count() > 0)
    <section class="pt-8 pb-12 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-2">
                <div>
                    <h2 class="text-2xl md:text-3xl font-black text-gray-900">Palestras Gratuitas</h2>
                    <p class="text-gray-500 text-sm md:text-base">Aprenda com os melhores empresários</p>
                </div>
                <a href="{{ route('events.index') }}" class="text-sm font-bold text-white bg-blue-700 hover:bg-blue-800 px-5 py-2 rounded-full shadow transition">Ver Todas</a>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($freeEvents as $event)
                <article class="bg-white rounded-2xl p-5 border border-gray-100 flex flex-col shadow-sm relative">
                    <span class="absolute top-4 right-4 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full">Gratuito</span>
                    <img src="{{ $event->thumbnail ?? asset('img/event-default.png') }}" alt="{{ $event->title }}" class="w-full h-28 object-cover rounded-xl mb-3">
                    <h3 class="text-base font-bold text-gray-900 mb-1">{{ $event->title }}</h3>
                    <p class="text-xs text-gray-500 mb-1">{{ $event->speaker ?? $event->author_name ?? 'Palestrante UNN' }}</p>
                    <p class="text-gray-600 text-xs mb-2">{{ Str::limit($event->description, 60) }}</p>
                    <div class="flex items-center justify-between mt-auto">
                        <span class="text-xs text-gray-500 flex items-center gap-1"><i class="fas fa-clock"></i> {{ $event->duration ?? '60' }} min</span>
                        <span class="text-xs text-gray-500 flex items-center gap-1"><i class="fas fa-users"></i> {{ $event->participants_count ?? '---' }}</span>
                    </div>
                    <a href="{{ route('events.show', $event->id) }}" class="btn-primary text-white px-4 py-2 rounded-xl font-semibold w-full block text-center mt-3">Assistir Agora</a>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Cursos em Destaque -->
    @if(isset($featuredCourses) && $featuredCourses->count() > 0 && isset($__inserirCursosDestaque))
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-black text-gray-900">Destaques na Home</h2>
                <span class="text-sm text-blue-600 bg-blue-50 px-3 py-1 rounded-full font-semibold">
                    <i class="fas fa-star mr-1"></i> Cursos em destaque
                </span>
            </div>
            <div id="featured-courses-list" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($featuredCourses as $course)
                <article class="bg-slate-50 rounded-3xl p-8 border border-gray-100 flex flex-col">
                    <div class="mb-3">
                        <img src="{{ $course->thumbnail ?? asset('img/course-default.png') }}" alt="{{ $course->title }}" class="w-full h-40 object-cover rounded-xl mb-2">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $course->title }}</h3>
                        <p class="text-gray-600 text-sm mb-2">{{ Str::limit($course->short_description, 80) }}</p>
                    </div>
                    <div class="mt-auto flex flex-col gap-2">
                        <span class="text-xs text-gray-500">Carga horária: <strong>{{ $course->total_hours ?? $course->getTotalHoursAttribute() }}h</strong></span>
                        <span class="text-xs text-gray-500">Avaliação: <strong>{{ $course->average_rating ?? '-' }}/5</strong> ({{ $course->approved_reviews_count ?? '0' }} avaliações)</span>
                        <a href="{{ route('courses.show', $course->slug) }}" class="btn-primary text-white px-6 py-3 rounded-xl font-semibold w-full block text-center mt-2">
                            Ver curso
                        </a>
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    <!-- Mentorias Disponíveis -->
    @php unset($__inserirCursosDestaque); @endphp
    @if(isset($mentorings) && $mentorings->count() > 0)
    <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
        <div class="max-w-7xl mx-auto">
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
        </div>
    </section>
    @endif

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
                @forelse($topRankings as $rank)
                    <div class="bg-gradient-to-br from-slate-50 to-white rounded-3xl p-6 border border-gray-100 relative overflow-hidden">
                        @if($loop->index === 0)
                        <div class="absolute top-4 right-4">
                            <i class="fas fa-trophy text-2xl text-yellow-500"></i>
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
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="text-center p-3 bg-slate-50 rounded-xl">
                                <p class="text-xl font-bold" style="color: var(--unn-azul-1)">{{ number_format($rank->score, 0, '', '.') }}</p>
                                <p class="text-xs text-gray-500">Pontos</p>
                            </div>
                            <div class="text-center p-3 bg-slate-50 rounded-xl">
                                <p class="text-xl font-bold" style="color: var(--unn-azul-1)">{{ $rank->interactions_count }}</p>
                                <p class="text-xs text-gray-500">Conexões</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-12 text-gray-500 text-lg">
                        Nenhum ranking disponível ainda.<br>
                        Participe de conexões e avaliações para aparecer aqui!
                    </div>
                @endforelse
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
    :root {
        --unn-azul-royal: #2E3192;
        --unn-azul-oceano: #0071BC;
        --unn-ciano-vivo: #29ABE2;
    }
    .unn-gradient-title {
        background: linear-gradient(90deg, #2E3192 0%, #0071BC 60%, #29ABE2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        color: transparent;
        font-weight: 900;
    }
    .btn-primary {
        background: linear-gradient(90deg, var(--unn-azul-royal) 0%, var(--unn-azul-oceano) 60%, var(--unn-ciano-vivo) 100%);
        color: #fff !important;
        border: none;
        font-weight: 600;
        transition: background 0.18s;
    }
    .btn-primary:hover {
        background: linear-gradient(90deg, var(--unn-ciano-vivo) 0%, var(--unn-azul-oceano) 60%, var(--unn-azul-royal) 100%);
        color: #fff !important;
    }
    .bg-blue-700 {
        background-color: var(--unn-azul-royal) !important;
    }
    .bg-blue-800 {
        background-color: var(--unn-azul-oceano) !important;
    }
    .text-blue-600 {
        color: var(--unn-azul-oceano) !important;
    }
    .border-blue-200 {
        border-color: var(--unn-ciano-vivo) !important;
    }
    .shadow-lg {
        box-shadow: 0 8px 24px rgba(46,49,146,0.08);
    }
    .shadow-xl {
        box-shadow: 0 12px 32px rgba(41,171,226,0.12);
    }
    /* Mantém fundo claro */
    .bg-gradient-to-br {
        background: linear-gradient(135deg, #f8fafc 0%, #fff 100%);
    }
</style>
<script src="/js/featured-courses-carousel.js"></script>
@endsection
