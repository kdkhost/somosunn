@extends('layouts.app')

@section('title', 'Cursos - UNN')

@push('styles')
    <style>
        .unn-courses-hero {
            background:
                radial-gradient(1200px circle at 15% 20%, rgba(255, 255, 255, 0.18) 0%, transparent 55%),
                radial-gradient(900px circle at 85% 0%, rgba(255, 255, 255, 0.12) 0%, transparent 50%),
                linear-gradient(180deg, var(--unn-azul-3) 0%, var(--unn-azul-1) 55%, var(--unn-azul-3) 100%);
        }

        .unn-courses-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(rgba(255, 255, 255, 0.35) 1px, transparent 1px),
                radial-gradient(rgba(255, 255, 255, 0.18) 1px, transparent 1px);
            background-size: 36px 36px, 64px 64px;
            background-position: 0 0, 18px 18px;
            opacity: 0.28;
            pointer-events: none;
        }

        .unn-courses-cta {
            background: linear-gradient(180deg, var(--unn-azul-3) 0%, var(--unn-azul-1) 55%, var(--unn-azul-3) 100%);
        }
    </style>
@endpush

@section('content')
    @php
        $isDemo = (bool) ($isDemo ?? false);
        $courses = $courses ?? collect();
        $featuredCourse = $featuredCourse ?? null;

        $coursesCollection = method_exists($courses, 'getCollection') ? $courses->getCollection() : collect($courses);
        $totalCount = method_exists($courses, 'total') ? (int) $courses->total() : $coursesCollection->count();

        $resolveImageUrl = function (?string $path): ?string {
            $path = trim((string) $path);
            if ($path === '') return null;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
            if (str_starts_with($path, 'storage/')) return asset($path);
            if (str_starts_with($path, 'uploads/')) return asset($path);
            return asset('storage/' . ltrim($path, '/'));
        };

        $featured = $featuredCourse ?: ($coursesCollection->firstWhere('is_featured', true) ?: $coursesCollection->first());
        $featuredImage = $featured?->thumbnail ? $resolveImageUrl($featured->thumbnail) : null;
        $fallbackFeaturedImage = 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1400';

        $featuredRouteParam = $featured?->slug ?: ($featured?->id ?? null);
        $featuredShowUrl = (!$isDemo && $featuredRouteParam) ? route('courses.show', $featuredRouteParam) : '#';
        $featuredCheckoutUrl = (!$isDemo && !empty($featured?->id)) ? route('checkout.show', $featured->id) : '#';
    @endphp

    <div class="min-h-screen">
        <section class="unn-courses-hero relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-black/0 via-black/0 to-black/25 pointer-events-none"></div>

            <div class="px-4 md:px-12 lg:px-24 pt-10 md:pt-14 pb-14 md:pb-20 relative">
                <div class="max-w-6xl mx-auto">
                    <div class="text-center">
                        <span class="inline-flex items-center justify-center px-6 py-2 rounded-full text-sm font-bold text-white border border-white/20 bg-white/15 backdrop-blur">
                            Cursos
                        </span>
                        <h1 class="mt-6 text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-white">
                            Biblioteca de Cursos UNN
                        </h1>
                        <p class="mt-3 text-white/80 text-base sm:text-lg">
                            Conteúdo prático para negócios, networking e crescimento profissional
                        </p>
                    </div>

                    @if($featured)
                        @php
                            $featuredIsPaused = (string) ($featured->status ?? '') === 'paused';
                            $featuredAuthor = $featured->author_name ?? optional($featured->creator)->name ?? 'UNN Academy';
                            $featuredPrice = (float) ($featured->price ?? 0);
                            $featuredDuration = (int) ($featured->duration ?? 0);
                            $featuredDurationLabel = $featuredDuration > 0
                                ? (intdiv($featuredDuration, 60) > 0
                                    ? (intdiv($featuredDuration, 60) . 'h' . (($featuredDuration % 60) ? str_pad((string) ($featuredDuration % 60), 2, '0', STR_PAD_LEFT) : ''))
                                    : ($featuredDuration . ' min'))
                                : '—';
                            $featuredHasAccess = auth()->check() && ($featured instanceof \App\Models\Course)
                                ? auth()->user()->hasCourseAccess($featured)
                                : false;
                        @endphp

                        <div class="mt-10 md:mt-14">
                            <div class="rounded-[32px] overflow-hidden border border-white/20 bg-white/10 backdrop-blur shadow-[0_40px_120px_-60px_rgba(0,0,0,0.65)]">
                                <div class="grid lg:grid-cols-2">
                                    <div class="relative min-h-[260px] md:min-h-[320px]">
                                        <img src="{{ $featuredImage ?: $fallbackFeaturedImage }}"
                                            alt="{{ $featured->title ?? 'Curso' }}"
                                            class="absolute inset-0 w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-gradient-to-tr from-black/45 via-black/10 to-black/0"></div>

                                        <div class="absolute inset-x-0 bottom-0 p-6 md:p-8">
                                            <div class="inline-flex items-center gap-2 rounded-full bg-white/15 border border-white/20 px-4 py-2 text-sm font-bold text-white backdrop-blur">
                                                <i class="fas fa-star"></i> Em destaque
                                            </div>
                                        </div>

                                        @if(($featured->is_demo ?? false) === true)
                                            <div class="absolute top-6 left-6">
                                                <span class="inline-flex items-center gap-2 rounded-full bg-yellow-400 text-yellow-950 px-4 py-2 text-xs font-black uppercase tracking-wider">
                                                    <i class="fas fa-flask"></i> Demo
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="p-8 md:p-10 bg-white">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 leading-tight">
                                                    {{ $featured->title ?? 'Curso' }}
                                                </h2>
                                                <p class="mt-3 text-slate-600">
                                                    {{ $featured->short_description ?? 'Aprenda com conteúdo objetivo e aplicável no seu dia a dia.' }}
                                                </p>
                                                @php
                                                    $featuredAvgRating = $featured->average_rating ?? 0;
                                                    $featuredReviewsCount = $featured->approved_reviews_count ?? 0;
                                                @endphp
                                                @if($featuredReviewsCount > 0)
                                                    <div class="mt-3 flex items-center gap-2 text-sm">
                                                        <span class="font-bold text-slate-900">{{ number_format($featuredAvgRating, 1, ',', '.') }}</span>
                                                        <div class="flex items-center gap-0.5 text-yellow-400">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                @if($i <= floor($featuredAvgRating))
                                                                    <i class="fas fa-star text-xs"></i>
                                                                @elseif($i - $featuredAvgRating < 1)
                                                                    <i class="fas fa-star-half-alt text-xs"></i>
                                                                @else
                                                                    <i class="far fa-star text-xs text-slate-300"></i>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                        <span class="text-slate-500">({{ $featuredReviewsCount }} {{ $featuredReviewsCount === 1 ? 'avaliação' : 'avaliações' }})</span>
                                                    </div>
                                                @endif
                                            </div>
                                            @if($featuredIsPaused)
                                                <span class="shrink-0 inline-flex items-center gap-2 rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-xs font-black uppercase tracking-wider">
                                                    <i class="fas fa-pause"></i> Pausado
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-6 grid sm:grid-cols-3 gap-3">
                                            <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Duração</p>
                                                <p class="text-slate-900 font-black mt-1">{{ $featuredDurationLabel }}</p>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Instrutor</p>
                                                <p class="text-slate-900 font-black mt-1">{{ $featuredAuthor }}</p>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Investimento</p>
                                                <p class="text-slate-900 font-black mt-1">
                                                    {{ $featuredPrice > 0 ? 'R$ '.number_format($featuredPrice, 2, ',', '.') : 'Gratuito' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-8 flex flex-col sm:flex-row gap-3">
                                            <a href="{{ $featuredShowUrl }}"
                                                class="px-8 py-4 rounded-xl font-bold border-2 border-slate-200 text-slate-700 hover:bg-slate-50 transition inline-flex items-center justify-center {{ $isDemo ? 'pointer-events-none opacity-60' : '' }}">
                                                Saiba mais
                                            </a>

                                            @if($featuredIsPaused)
                                                <button type="button" disabled
                                                    class="px-8 py-4 rounded-xl font-bold bg-slate-100 text-slate-500 inline-flex items-center justify-center cursor-not-allowed">
                                                    Vendas pausadas
                                                </button>
                                            @elseif($featuredHasAccess)
                                                <a href="{{ $featuredShowUrl }}"
                                                    class="px-8 py-4 rounded-xl font-bold btn-primary shadow-lg hover:shadow-xl transition inline-flex items-center justify-center {{ $isDemo ? 'pointer-events-none opacity-60' : '' }}">
                                                    Acessar curso
                                                </a>
                                            @else
                                                <a href="{{ $featuredCheckoutUrl }}"
                                                    class="px-8 py-4 rounded-xl font-bold btn-primary shadow-lg hover:shadow-xl transition inline-flex items-center justify-center {{ $isDemo ? 'pointer-events-none opacity-60' : '' }}">
                                                    Comprar agora
                                                </a>
                                            @endif
                                        </div>

                                        @if($isDemo)
                                            <div class="mt-6 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-2xl p-4">
                                                <div class="flex gap-3">
                                                    <div class="mt-0.5"><i class="fas fa-exclamation-triangle"></i></div>
                                                    <div>
                                                        <p class="font-bold">Dados de demonstração</p>
                                                        <p class="text-sm">Estes cursos são exemplos. Cadastre cursos reais no painel administrativo.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-10 md:mt-14 max-w-3xl mx-auto">
                            <div class="bg-white rounded-[32px] shadow-2xl p-10 text-center">
                                <div class="text-slate-400 mb-4"><i class="fas fa-book-open text-5xl"></i></div>
                                <h2 class="text-2xl sm:text-3xl font-black text-slate-900">Nenhum curso disponível</h2>
                                <p class="mt-2 text-slate-600">Fique atento: novos conteúdos serão liberados em breve.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section id="lista-cursos" class="bg-gradient-to-br from-slate-50 to-blue-50 py-12 md:py-16 px-4 md:px-12 lg:px-24 scroll-mt-28">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
                    <div>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold"
                            style="background: var(--unn-azul-1); color: white">
                            <i class="fas fa-graduation-cap"></i> Biblioteca
                        </span>
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-gray-900 mt-4">Cursos disponíveis</h2>
                        <p class="text-gray-600 mt-2 max-w-2xl">
                            Explore a biblioteca. Você pode ver detalhes do curso e adquirir pelo checkout.
                        </p>
                    </div>

                    <a href="{{ route('premium') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-full font-bold bg-white border hover:bg-slate-50 transition whitespace-nowrap"
                        style="border-color: var(--unn-azul-1); color: var(--unn-azul-1)">
                        <i class="fas fa-crown"></i> Ver planos Premium
                    </a>
                </div>

                <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Catálogo</h3>
                        <span class="text-sm text-gray-500">
                            {{ $totalCount }} {{ $totalCount === 1 ? 'curso' : 'cursos' }}
                        </span>
                    </div>

                    @if($coursesCollection->isEmpty())
                        <div class="p-10 text-center">
                            <div class="text-slate-400 mb-4"><i class="fas fa-book-open text-4xl"></i></div>
                            <h4 class="text-xl font-black text-slate-900 mb-2">Nenhum curso encontrado</h4>
                            <p class="text-slate-600">Quando houver cursos publicados, eles aparecerão aqui.</p>
                        </div>
                    @else
                        <div class="p-6 md:p-8">
                            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($coursesCollection as $course)
                                    @php
                                        $status = (string) ($course->status ?? 'published');
                                        $isPaused = $status === 'paused';
                                        $price = (float) ($course->price ?? 0);
                                        $duration = (int) ($course->duration ?? 0);
                                        $durationLabel = $duration > 0
                                            ? (intdiv($duration, 60) > 0
                                                ? (intdiv($duration, 60) . 'h' . (($duration % 60) ? str_pad((string) ($duration % 60), 2, '0', STR_PAD_LEFT) : ''))
                                                : ($duration . ' min'))
                                            : '—';
                                        $author = $course->author_name ?? optional($course->creator)->name ?? 'UNN Academy';
                                        $thumbUrl = !empty($course->thumbnail) ? $resolveImageUrl($course->thumbnail) : null;
                                        $hasAccess = auth()->check() && ($course instanceof \App\Models\Course)
                                            ? auth()->user()->hasCourseAccess($course)
                                            : false;
                                        $courseRouteParam = $course->slug ?: ($course->id ?? null);
                                        $courseShowUrl = (!$isDemo && $courseRouteParam) ? route('courses.show', $courseRouteParam) : '#';
                                        $courseCheckoutUrl = (!$isDemo && !empty($course->id)) ? route('checkout.show', $course->id) : '#';
                                    @endphp

                                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-lg transition overflow-hidden">
                                        <div class="relative h-44 bg-slate-100 overflow-hidden">
                                            @if($thumbUrl)
                                                <img src="{{ $thumbUrl }}" alt="{{ $course->title ?? 'Curso' }}" class="absolute inset-0 w-full h-full object-cover">
                                                <div class="absolute inset-0 bg-gradient-to-tr from-black/30 via-black/10 to-black/0"></div>
                                            @else
                                                <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-blue-500 to-indigo-700"></div>
                                                <div class="absolute inset-0 opacity-30"
                                                    style="background-image: radial-gradient(rgba(255,255,255,0.35) 1px, transparent 1px); background-size: 26px 26px;"></div>
                                                <div class="absolute inset-0 flex items-center justify-center text-white/80">
                                                    <i class="fas fa-graduation-cap text-5xl"></i>
                                                </div>
                                            @endif

                                            <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                                                @if(($course->is_demo ?? false) === true)
                                                    <span class="inline-flex items-center gap-2 rounded-full bg-yellow-400 text-yellow-950 px-3 py-1 text-[11px] font-black uppercase tracking-wider">
                                                        <i class="fas fa-flask"></i> Demo
                                                    </span>
                                                @endif
                                                @if($isPaused)
                                                    <span class="inline-flex items-center gap-2 rounded-full bg-white/90 text-slate-700 px-3 py-1 text-[11px] font-black uppercase tracking-wider">
                                                        <i class="fas fa-pause"></i> Pausado
                                                    </span>
                                                @endif
                                                @if(($course->is_featured ?? false) === true)
                                                    <span class="inline-flex items-center gap-2 rounded-full bg-white/90 text-slate-700 px-3 py-1 text-[11px] font-black uppercase tracking-wider">
                                                        <i class="fas fa-star"></i> Destaque
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="p-6">
                                            <h4 class="text-lg font-black text-slate-900 leading-snug">
                                                {{ $course->title ?? 'Curso' }}
                                            </h4>
                                            <p class="mt-2 text-sm text-slate-600">
                                                {{ Str::limit((string) ($course->short_description ?? 'Sem descrição.'), 110) }}
                                            </p>

                                            @php
                                                $avgRating = $course->average_rating ?? 0;
                                                $reviewsCount = $course->approved_reviews_count ?? 0;
                                            @endphp
                                            @if($reviewsCount > 0)
                                                <div class="mt-3 flex items-center gap-2 text-sm">
                                                    <span class="font-bold text-slate-900">{{ number_format($avgRating, 1, ',', '.') }}</span>
                                                    <div class="flex items-center gap-0.5 text-yellow-400">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= floor($avgRating))
                                                                <i class="fas fa-star text-xs"></i>
                                                            @elseif($i - $avgRating < 1)
                                                                <i class="fas fa-star-half-alt text-xs"></i>
                                                            @else
                                                                <i class="far fa-star text-xs text-slate-300"></i>
                                                            @endif
                                                        @endfor
                                                    </div>
                                                    <span class="text-slate-500">({{ $reviewsCount }})</span>
                                                </div>
                                            @endif

                                            <div class="mt-4 flex items-center justify-between text-sm text-slate-500">
                                                <span class="inline-flex items-center gap-2">
                                                    <i class="far fa-clock"></i> {{ $durationLabel }}
                                                </span>
                                                <span class="font-black" style="color: var(--unn-azul-1)">
                                                    {{ $price > 0 ? 'R$ '.number_format($price, 2, ',', '.') : 'Gratuito' }}
                                                </span>
                                            </div>

                                            <div class="mt-3 text-xs text-slate-500">
                                                <i class="fas fa-user-circle mr-1"></i> {{ $author }}
                                            </div>

                                            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                                                <a href="{{ $courseShowUrl }}"
                                                    class="w-full sm:w-auto flex-1 px-4 py-3 rounded-xl font-bold border-2 border-slate-200 text-slate-700 hover:bg-slate-50 transition inline-flex items-center justify-center {{ $isDemo ? 'pointer-events-none opacity-60' : '' }}">
                                                    Saiba mais
                                                </a>

                                                @if($isPaused)
                                                    <button type="button" disabled
                                                        class="w-full sm:w-auto flex-1 px-4 py-3 rounded-xl font-bold bg-slate-100 text-slate-500 inline-flex items-center justify-center cursor-not-allowed">
                                                        Vendas pausadas
                                                    </button>
                                                @elseif($hasAccess)
                                                    <a href="{{ $courseShowUrl }}"
                                                        class="w-full sm:w-auto flex-1 px-4 py-3 rounded-xl font-bold btn-primary shadow-md hover:shadow-lg transition inline-flex items-center justify-center {{ $isDemo ? 'pointer-events-none opacity-60' : '' }}">
                                                        Acessar
                                                    </a>
                                                @else
                                                    <a href="{{ $courseCheckoutUrl }}"
                                                        class="w-full sm:w-auto flex-1 px-4 py-3 rounded-xl font-bold btn-primary shadow-md hover:shadow-lg transition inline-flex items-center justify-center {{ $isDemo ? 'pointer-events-none opacity-60' : '' }}">
                                                        Adquirir
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if(method_exists($courses, 'links'))
                                <div class="mt-10">
                                    {{ $courses->links() }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="py-12 md:py-16 px-4 md:px-12 lg:px-24 bg-white">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-3 gap-6">
                    <div class="bg-slate-50 border border-slate-100 rounded-3xl p-8">
                        <div class="h-12 w-12 rounded-2xl flex items-center justify-center text-white"
                            style="background: var(--unn-azul-1)">
                            <i class="fas fa-infinity text-xl"></i>
                        </div>
                        <h3 class="mt-5 text-xl font-black text-slate-900">Acesso e progresso</h3>
                        <p class="mt-2 text-slate-600">Acompanhe aulas, marque progresso e assista no seu ritmo.</p>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 rounded-3xl p-8">
                        <div class="h-12 w-12 rounded-2xl flex items-center justify-center text-white"
                            style="background: var(--unn-azul-2)">
                            <i class="fas fa-certificate text-xl"></i>
                        </div>
                        <h3 class="mt-5 text-xl font-black text-slate-900">Certificados</h3>
                        <p class="mt-2 text-slate-600">Cursos podem oferecer certificado de conclusão (quando habilitado pelo instrutor).</p>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 rounded-3xl p-8">
                        <div class="h-12 w-12 rounded-2xl flex items-center justify-center text-white"
                            style="background: var(--unn-azul-3)">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <h3 class="mt-5 text-xl font-black text-slate-900">Comunidade</h3>
                        <p class="mt-2 text-slate-600">Troque experiências e cresça junto com outros membros da UNN.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="unn-courses-cta py-12 md:py-16 px-4 md:px-12 lg:px-24 text-white">
            <div class="max-w-7xl mx-auto">
                <div class="rounded-[32px] border border-white/15 bg-white/10 backdrop-blur p-10 md:p-12 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                    <div>
                        <h3 class="text-2xl sm:text-3xl font-black">Pronto para evoluir sua carreira?</h3>
                        <p class="mt-2 text-white/80 max-w-2xl">Escolha um curso, adquira sua vaga e comece hoje. Você também pode conferir os próximos eventos da UNN.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('premium') }}"
                            class="px-8 py-4 rounded-xl font-bold bg-white text-slate-900 hover:bg-slate-50 transition inline-flex items-center justify-center whitespace-nowrap">
                            <i class="fas fa-crown mr-2"></i> Ver planos Premium
                        </a>
                        <a href="{{ route('events.index') }}"
                            class="px-8 py-4 rounded-xl font-bold border-2 border-white/30 hover:bg-white/10 transition inline-flex items-center justify-center">
                            <i class="fas fa-calendar-alt mr-2"></i> Ver eventos
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
