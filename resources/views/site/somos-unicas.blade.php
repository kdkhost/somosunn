@extends($extends ?? 'layouts.app')

@section('title', 'Somos Únicas - UNN')

@section('content')
    <div class="min-h-screen pb-16" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);">
        <!-- Hero Section -->
        <section class="pt-10 md:pt-24 pb-20 px-4 md:px-12 lg:px-24 mb-12 shadow-lg" style="background: linear-gradient(135deg, #4c1d95 0%, #5b21b6 100%); border-radius: 0 0 50px 50px;">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-12">
                <div class="flex-1 text-center md:text-left text-white">
                    <span class="inline-block px-4 py-1 rounded-full text-sm font-bold mb-4 bg-white/10 uppercase tracking-wider backdrop-blur-md">
                        Comunidade Feminina
                    </span>
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6 text-white">
                        {{ $pageData['hero_title'] ?? 'Somos Únicas' }}
                    </h1>
                    <div class="text-lg sm:text-xl text-purple-50 max-w-3xl mx-auto md:mx-0 leading-relaxed summernote-content">
                        {!! $pageData['hero_subtitle'] ?? 'Um espaço dedicado a mulheres empreendedoras. Acesse palestras, mentorias exclusivas, eventos e recursos focados no protagonismo feminino.' !!}
                    </div>
                </div>
                @if(isset($pageData['hero_image']) && !empty($pageData['hero_image']))
                <div class="flex-1 flex justify-center">
                    <img src="{{ Str::startsWith($pageData['hero_image'], ['http://', 'https://']) ? $pageData['hero_image'] : asset('storage/' . $pageData['hero_image']) }}" 
                         alt="{{ $pageData['hero_title'] ?? 'Somos Únicas' }}"
                         class="rounded-3xl shadow-2xl max-h-[500px] object-cover border-4 border-white/30 transform rotate-2 hover:rotate-0 transition duration-500">
                </div>
                @endif
            </div>
        </section>

        <!-- Cursos Somos Únicas -->
        @if(isset($courses) && $courses->count() > 0)
            <section class="pt-12 pb-8 px-4 md:px-12 lg:px-24">
                <div class="max-w-7xl mx-auto">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl md:text-3xl font-black unicas-title-gradient">{{ $pageData['courses_title'] ?? 'Cursos & Capacitação' }}</h2>
                            <p class="text-gray-500 text-sm md:text-base">{{ $pageData['courses_subtitle'] ?? 'Aperfeiçoe suas habilidades' }}</p>
                        </div>
                    </div>
                    <div class="grid auto-rows-fr md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($courses as $course)
                            <article
                                class="unicas-selling-card bg-white rounded-2xl p-6 border border-pink-100 flex h-full flex-col shadow-sm relative transition-all duration-300 hover:shadow-md hover:-translate-y-1">
                                @if($course->price > 0)
                                    <span
                                        class="absolute top-4 right-4 bg-pink-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">Premium</span>
                                @else
                                    <span
                                        class="absolute top-4 right-4 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">Grátis</span>
                                @endif
                                @php
                                    $courseThum = $course->thumbnail;
                                    $courseImage = asset('img/placeholder-course.jpg');

                                    if ($courseThum) {
                                        if (Str::startsWith($courseThum, ['http://', 'https://'])) {
                                            $courseImage = $courseThum;
                                        } else {
                                            $normalizedCourseThumb = ltrim(str_replace('\\', '/', $courseThum), '/');
                                            $courseImage = Str::startsWith($normalizedCourseThumb, ['uploads/', 'img/'])
                                                ? asset($normalizedCourseThumb)
                                                : asset('storage/' . $normalizedCourseThumb);
                                        }
                                    }
                                @endphp
                                <img src="{{ $courseImage }}" alt="{{ $course->title }}"
                                    class="unicas-selling-media w-full h-40 object-cover rounded-xl mb-4 border border-pink-50"
                                    onerror="this.onerror=null;this.src='{{ asset('img/placeholder-course.jpg') }}';">
                                <h3 class="unicas-selling-title text-lg font-bold text-gray-900 mb-1 line-clamp-2">{{ $course->title }}</h3>
                                <p class="unicas-selling-eyebrow text-xs text-pink-500 font-semibold mb-2">Por
                                    {{ $course->author_name ?? 'Especialista UNN' }}</p>
                                <p class="unicas-selling-copy text-gray-600 text-sm mb-4 line-clamp-3">
                                    {{ Str::limit(strip_tags((string) ($course->short_description ?? '')), 100) }}</p>
                                <div class="unicas-selling-footer mt-auto flex flex-col gap-2 pt-4 border-t border-pink-50">
                                    @if($course->price > 0)
                                        <span class="text-lg font-black text-pink-600">R$
                                            {{ number_format($course->price, 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-lg font-black text-green-600">GRÁTIS</span>
                                    @endif
                                    <a href="{{ route('courses.show', $course->slug ?? $course->id) }}"
                                        class="btn-unicas unicas-selling-action text-white px-6 py-2.5 rounded-xl font-bold w-full text-center mt-2 shadow-sm">
                                        Acessar Curso
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if($courses->count() >= 6)
                        <div class="text-center mt-8">
                            <a href="{{ route('courses.index') }}"
                                class="inline-flex items-center gap-2 text-pink-600 font-bold hover:text-pink-700 transition">
                                Ver todos os cursos <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        <!-- Eventos & Palestras -->
        @if(isset($events) && $events->count() > 0)
            <section class="py-12 px-4 md:px-12 lg:px-24 bg-white/50">
                <div class="max-w-7xl mx-auto">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl md:text-3xl font-black unicas-title-gradient">{{ $pageData['events_title'] ?? 'Eventos & Palestras' }}</h2>
                            <p class="text-gray-500 text-sm md:text-base">{{ $pageData['events_subtitle'] ?? 'Encontros especiais para mulheres incríveis' }}</p>
                        </div>
                    </div>
                    <div class="grid auto-rows-fr md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($events as $event)
                            @php
                                $isEventClosed = method_exists($event, 'isClosedForPublic') && $event->isClosedForPublic();
                                $eventDate = $event->start_at instanceof \Carbon\CarbonInterface
                                    ? $event->start_at
                                    : ($event->start_at ? \Carbon\Carbon::parse($event->start_at) : null);
                            @endphp
                            <article
                                class="unicas-selling-card bg-white rounded-2xl p-5 border border-pink-100 flex h-full flex-col shadow-sm relative transition-all duration-300 hover:shadow-md hover:border-pink-300">
                                <div
                                    class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm text-center px-3 py-1.5 rounded-xl shadow-sm border border-pink-50">
                                    <p class="text-xs font-bold text-pink-600 uppercase">{{ $eventDate ? $eventDate->translatedFormat('M') : '--' }}</p>
                                    <p class="text-xl font-black text-gray-900 leading-none">{{ $eventDate ? $eventDate->format('d') : '--' }}</p>
                                </div>
                                @php
                                    $eventImage = $event->image 
                                        ? (Str::startsWith($event->image, ['http://', 'https://']) ? $event->image : asset('storage/'.$event->image)) 
                                        : asset('img/placeholder-event.jpg');
                                @endphp
                                <img src="{{ $eventImage }}" alt="{{ $event->title }}"
                                    class="unicas-selling-media w-full h-36 object-cover rounded-xl mb-4">
                                <h3 class="unicas-selling-title text-lg font-bold text-gray-900 mb-1 line-clamp-2">{{ $event->title }}</h3>
                                <p class="unicas-selling-eyebrow text-xs text-pink-500 font-semibold mb-2"><i class="fas fa-microphone-alt mr-1"></i>
                                    {{ $event->speaker ?? $event->author_name ?? 'Palestrante UNN' }}</p>
                                <p class="unicas-selling-copy text-gray-600 text-sm mb-4 line-clamp-2">
                                    {{ Str::limit(strip_tags((string) ($event->description ?? '')), 80) }}</p>

                                <div class="unicas-selling-footer mt-auto pt-4 border-t border-pink-50">
                                    @if($isEventClosed)
                                        <span
                                            class="unicas-selling-action bg-slate-100 text-slate-500 px-4 py-2.5 rounded-xl font-bold w-full text-center mt-3 cursor-not-allowed border border-slate-200">
                                            Evento Encerrado
                                        </span>
                                    @else
                                        <a href="{{ route('events.show', $event->id) }}"
                                            class="btn-unicas-outline unicas-selling-action text-pink-600 border-2 border-pink-500 hover:bg-pink-50 px-4 py-2 rounded-xl font-bold w-full text-center mt-3 transition text-sm">
                                            Garantir Vaga
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <!-- Mentorias Somos Únicas -->
        @if(isset($mentorships) && $mentorships->count() > 0)
            <section class="py-12 px-6 md:px-12 lg:px-24">
                <div class="max-w-7xl mx-auto">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl md:text-3xl font-black unicas-title-gradient">{{ $pageData['mentorships_title'] ?? 'Mentorias Exclusivas' }}</h2>
                            <p class="text-gray-500 text-sm md:text-base">{{ $pageData['mentorships_subtitle'] ?? 'Aconselhamento direto com grandes líderes' }}</p>
                        </div>
                    </div>

                    <div class="grid auto-rows-fr md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($mentorships as $mentorship)
                            @php
                                $isMentorshipClosed = method_exists($mentorship, 'isClosedForPublic') && $mentorship->isClosedForPublic();
                            @endphp
                            <article
                                class="unicas-selling-card bg-white rounded-3xl p-6 md:p-8 border border-pink-100 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1 h-full flex flex-col">
                                <div class="unicas-selling-header flex justify-between items-center gap-4 mb-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold overflow-hidden">
                                            @if(optional($mentorship->mentor)->profile_photo_url)
                                                <img src="{{ $mentorship->mentor->profile_photo_url }}"
                                                    class="w-full h-full object-cover">
                                            @else
                                                {{ substr(optional($mentorship->mentor)->name ?? 'M', 0, 1) }}
                                            @endif
                                        </div>
                                        <p class="text-xs font-bold text-gray-700">
                                            {{ optional($mentorship->mentor)->name ?? 'Mentor UNN' }}</p>
                                    </div>
                                    <span class="font-black text-pink-600 bg-pink-50 px-3 py-1 rounded-lg">R$
                                        {{ number_format($mentorship->price, 2, ',', '.') }}</span>
                                </div>
                                <h3 class="unicas-selling-title text-xl font-black text-gray-900 mb-2 line-clamp-2">{{ $mentorship->title }}</h3>
                                <p class="unicas-selling-copy text-gray-600 text-sm mb-5 line-clamp-3">
                                    {{ Str::limit(strip_tags((string) ($mentorship->description ?? '')), 120) }}</p>
                                <div class="unicas-selling-footer mt-auto pt-4 border-t border-pink-50">
                                    <p class="unicas-selling-meta text-xs text-gray-500 font-semibold mb-3"><i
                                            class="fas fa-users text-pink-400 mr-1"></i> Vagas: {{ $mentorship->slots }} disponíveis
                                    </p>

                                    @if(!$isMentorshipClosed)
                                        <a href="{{ route('mentorships.show', $mentorship->id) }}"
                                            class="btn-unicas unicas-selling-action text-white px-6 py-3 rounded-xl font-bold w-full text-center shadow-md">
                                            Ver Detalhes
                                        </a>
                                    @else
                                        <span
                                            class="unicas-selling-action bg-slate-100 text-slate-500 px-6 py-3 rounded-xl font-bold w-full text-center cursor-not-allowed border border-slate-200">
                                            Vagas Esgotadas
                                        </span>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if(isset($testimonials) && $testimonials->isNotEmpty())
            <section class="py-12 px-4 md:px-12 lg:px-24 bg-white/50" aria-labelledby="unicas-testimonials-title">
                <div class="max-w-7xl mx-auto">
                    <div class="text-center mb-8">
                        <h2 id="unicas-testimonials-title" class="text-2xl md:text-3xl font-black unicas-title-gradient">O que dizem nossas integrantes</h2>
                        <p class="mt-2 text-gray-500">Experiências reais de mulheres que fazem parte da nossa comunidade.</p>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($testimonials as $testimonial)
                            @php
                                $testimonialName = $testimonial->display_name;
                                $testimonialAvatar = $testimonial->resolved_avatar;
                            @endphp
                            <article class="bg-white rounded-2xl p-6 border border-pink-100 shadow-sm flex flex-col">
                                <div class="flex gap-1 mb-4" aria-label="Avaliação: {{ $testimonial->rating }} de 5 estrelas">
                                    @for($star = 1; $star <= 5; $star++)
                                        <i class="fas fa-star {{ $star <= $testimonial->rating ? 'text-yellow-500' : 'text-gray-200' }}" aria-hidden="true"></i>
                                    @endfor
                                </div>
                                <blockquote class="text-gray-600 italic leading-relaxed mb-6 flex-1">“{{ $testimonial->content }}”</blockquote>
                                <div class="flex items-center gap-4">
                                    @if($testimonialAvatar)
                                        <img src="{{ $testimonialAvatar }}" alt="{{ $testimonialName }}"
                                            class="w-12 h-12 rounded-full object-cover flex-shrink-0" loading="lazy">
                                    @else
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0"
                                            style="background: var(--unicas-rosa-forte);" aria-hidden="true">
                                            {{ mb_strtoupper(mb_substr($testimonialName, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-bold text-gray-900 truncate">{{ $testimonialName }}</p>
                                        @if($testimonial->author_title)
                                            <p class="text-sm text-gray-500 truncate">{{ $testimonial->author_title }}</p>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if((!isset($courses) || $courses->isEmpty()) && (!isset($events) || $events->isEmpty()) && (!isset($mentorships) || $mentorships->isEmpty()))
            <section class="py-20 px-6 text-center">
                <div class="w-24 h-24 mx-auto bg-pink-50 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-spa text-4xl text-pink-300"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ $pageData['empty_title'] ?? 'Em breve!' }}</h3>
                <p class="text-gray-500 max-w-lg mx-auto">
                    {{ $pageData['empty_description'] ?? 'Estamos preparando conteúdos, eventos e mentorias incríveis exclusivamente para a área Somos Únicas.' }}
                </p>
            </section>
        @endif

    </div>

    <style>
        :root {
            --unicas-theme-color: {{ $pageData['theme_color'] ?? '#db2777' }};
            --unicas-rosa-forte: var(--unicas-theme-color);
            --unicas-rosa-medio: {{ $pageData['theme_color'] ?? '#db2777' }}ee;
            --unicas-rosa-claro: {{ $pageData['theme_color'] ?? '#db2777' }}cc;
        }

        .unicas-title-gradient {
            background: linear-gradient(90deg, var(--unicas-rosa-forte) 0%, var(--unicas-rosa-medio) 60%, var(--unicas-rosa-claro) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
            display: inline-block;
        }

        .btn-unicas {
            background: linear-gradient(135deg, var(--unicas-rosa-forte) 0%, var(--unicas-rosa-medio) 100%);
            box-shadow: 0 4px 14px 0 rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .btn-unicas:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px 0 rgba(0, 0, 0, 0.15);
            filter: brightness(1.1);
        }

        .summernote-content p {
            margin-bottom: 1rem;
        }

        .unicas-selling-card {
            height: 100%;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99) 0%, rgba(255, 255, 255, 0.95) 100%);
            box-shadow: 0 18px 42px rgba(91, 33, 182, 0.08);
        }

        .unicas-selling-media {
            display: block;
            width: 100%;
        }

        .unicas-selling-title {
            min-height: 3.6rem;
        }

        .unicas-selling-copy {
            min-height: 4.8rem;
        }

        .unicas-selling-header {
            min-height: 3.5rem;
            align-items: center;
        }

        .unicas-selling-footer {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .unicas-selling-meta {
            min-height: 1.5rem;
        }

        .unicas-selling-action {
            min-height: 3.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 767.98px) {
            .unicas-selling-title,
            .unicas-selling-copy,
            .unicas-selling-header {
                min-height: auto;
            }
        }
    </style>
@endsection
