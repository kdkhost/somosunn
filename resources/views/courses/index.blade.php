@extends('layouts.app')

@section('title', 'Academy - SOMOS UNN')

@section('content')
    @php
        $isDemo = (bool) ($isDemo ?? false);
        $courses = $courses ?? collect();
        $featuredCourse = $featuredCourse ?? null;

        $coursesCollection = method_exists($courses, 'getCollection') ? $courses->getCollection() : collect($courses);
        $totalCount = method_exists($courses, 'total') ? (int) $courses->total() : $coursesCollection->count();

        $resolveImageUrl = function (?string $path): ?string {
            $path = trim((string) $path);
            if ($path === '')
                return null;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
                return $path;
            if (str_starts_with($path, 'storage/'))
                return asset($path);
            if (str_starts_with($path, 'uploads/'))
                return asset($path);
            return asset('storage/' . ltrim($path, '/'));
        };

        $featured = $featuredCourse ?: ($coursesCollection->firstWhere('is_featured', true) ?: $coursesCollection->first());
        $featuredImage = $featured?->thumbnail ? $resolveImageUrl($featured->thumbnail) : null;
        $fallbackFeaturedImage = 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=1600&auto=format&fit=crop';

        $featuredRouteParam = $featured?->slug ?: ($featured?->id ?? null);
        $featuredShowUrl = (!$isDemo && $featuredRouteParam) ? route('courses.show', $featuredRouteParam) : '#';
        $featuredCheckoutUrl = (!$isDemo && !empty($featured?->id)) ? route('checkout.show', $featured->id) : '#';

        $wishlistIds = auth()->check() ? auth()->user()->wishlist()->pluck('course_id')->toArray() : [];
    @endphp

    <div class="min-h-screen bg-slate-50 text-slate-800 selection:bg-blue-500/30">
        <!-- Style Overrides -->
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100;300;400;500;600;700;800;900&display=swap');

            :root {
                --p-font: 'Outfit', sans-serif;
            }

            .academy-hero {
                background:
                    radial-gradient(1200px circle at 15% 20%, rgba(255, 255, 255, 0.18) 0%, transparent 55%),
                    radial-gradient(900px circle at 85% 0%, rgba(255, 255, 255, 0.12) 0%, transparent 50%),
                    linear-gradient(180deg, var(--unn-azul-3) 0%, var(--unn-azul-1) 55%, var(--unn-azul-3) 100%);
            }

            .academy-hero::before {
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

            .glass-card {
                background: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(0, 0, 0, 0.05);
                box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05), inset 0 1px 1px rgba(255, 255, 255, 0.8);
            }

            .glass-card-hover:hover {
                background: rgba(255, 255, 255, 0.95);
                border-color: rgba(0, 0, 0, 0.1);
                transform: translateY(-8px);
                box-shadow: 0 25px 40px -15px rgba(0, 0, 0, 0.1), inset 0 1px 1px rgba(255, 255, 255, 0.9), 0 0 0 1px var(--unn-azul-2);
            }

            .text-gradient {
                background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .btn-glow:hover {
                box-shadow: 0 0 20px rgba(37, 99, 235, 0.3);
            }

            .unn-events-cta {
                background: linear-gradient(180deg, var(--unn-azul-3) 0%, var(--unn-azul-1) 55%, var(--unn-azul-3) 100%);
            }
        </style>

        <div class="min-h-screen">
            <!-- Hero imersivo (Padronizado Claro/Azul) -->
            <section class="academy-hero relative pt-20 pb-20 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-black/0 via-black/0 to-black/20 pointer-events-none">
                </div>

                <div class="container mx-auto px-6 relative z-10 text-center">
                    <div class="flex flex-col items-center text-center space-y-8 mb-20">
                        <div
                            class="inline-flex items-center gap-3 px-6 py-2 rounded-full text-sm font-bold text-white border border-white/20 bg-white/15 backdrop-blur animate-fade-in shadow-lg">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                            UNN ACADEMY
                        </div>

                        <h1
                            class="text-4xl sm:text-5xl md:text-7xl font-black tracking-tighter text-white leading-tight max-w-5xl">
                            A Maestria dos Negócios Começa Aqui.
                        </h1>

                        <p class="text-white/80 text-lg md:text-xl font-medium max-w-2xl leading-relaxed">
                            Domine as habilidades que transformam mercados. Conteúdo prático para quem não aceita o comum.
                        </p>
                    </div>

                    @if($featured)
                        @php
                            $featuredAuthor = $featured->author_name ?? optional($featured->creator)->name ?? 'UNN Global';
                            $featuredPrice = (float) ($featured->price ?? 0);
                            $featuredDuration = (int) ($featured->duration ?? 0);
                            $featuredDurationLabel = $featuredDuration > 0
                                ? (intdiv($featuredDuration, 60) > 0
                                    ? (intdiv($featuredDuration, 60) . 'h' . (($featuredDuration % 60) ? str_pad((string) ($featuredDuration % 60), 2, '0', STR_PAD_LEFT) : ''))
                                    : ($featuredDuration . ' min'))
                                : '4h 30m';
                            $featuredHasAccess = auth()->check() && ($featured instanceof \App\Models\Course)
                                ? auth()->user()->hasCourseAccess($featured)
                                : false;
                        @endphp

                        <!-- Featured Masterclass Destaque na Capa (Sem o fundo neon slate-900) -->
                        <div class="relative group mt-10 md:mt-14 max-w-[1024px] mx-auto text-left">
                            <div
                                class="absolute -inset-1 bg-blue-100 rounded-[3rem] blur opacity-50 transition duration-1000 group-hover:duration-200 pointer-events-none">
                            </div>

                            <div
                                class="relative flex flex-col lg:flex-row rounded-[3rem] bg-white overflow-hidden border border-slate-100 shadow-2xl">
                                <div class="w-full lg:w-3/5 h-[400px] lg:h-auto relative bg-slate-50">
                                    <img src="{{ $featuredImage ?: $fallbackFeaturedImage }}"
                                        class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-700"
                                        alt="Featured">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-r from-slate-900/40 via-transparent to-transparent">
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>

                                    <div class="absolute bottom-8 left-8 flex items-center gap-4">
                                        <div
                                            class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-white/40 shadow-xl bg-white">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($featuredAuthor) }}&background=2563eb&color=fff"
                                                class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-black text-amber-300 uppercase tracking-widest">Master
                                                Instructor</p>
                                            <p class="text-white font-black drop-shadow-md">{{ $featuredAuthor }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="w-full lg:w-2/5 p-10 md:p-14 flex flex-col justify-center space-y-8 bg-slate-50">
                                    <div
                                        class="inline-flex items-center gap-2 text-amber-600 font-black text-xs uppercase tracking-widest bg-amber-100 px-4 py-2 rounded-xl self-start border border-amber-200">
                                        <i class="fas fa-crown text-amber-500"></i> EM DESTAQUE
                                    </div>

                                    <h3 class="text-3xl md:text-4xl font-black tracking-tight leading-tight text-slate-800">
                                        {{ $featured->title ?? 'Estratégias de Alto Impacto' }}
                                    </h3>

                                    <p class="text-slate-600 text-sm md:text-base font-medium leading-relaxed">
                                        {{ $featured->short_description ?: 'Um mergulho profundo nas metodologias que estão moldando o futuro dos negócios globais. Exclusivo para membros UNN.' }}
                                    </p>

                                    <div class="flex items-center gap-6 py-6 border-y border-slate-200">
                                        <div>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">
                                                Duração</p>
                                            <p class="text-slate-800 font-black">{{ $featuredDurationLabel }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">
                                                Status</p>
                                            <p class="text-emerald-600 font-black">Certificado</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">
                                                Investimento</p>
                                            <p class="text-slate-800 font-black">
                                                {{ $featuredPrice > 0 ? 'R$ ' . number_format($featuredPrice, 2, ',', '.') : 'GRATUITO' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                                        <a href="{{ $featuredShowUrl }}"
                                            class="flex-1 px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black text-center transition-all btn-glow shadow-lg shadow-blue-500/30 {{ $isDemo ? 'opacity-50 pointer-events-none' : '' }}">
                                            {{ $featuredHasAccess ? 'ASSISTIR AGORA' : 'GARANTIR ACESSO' }}
                                        </a>
                                        <button
                                            class="btn-wishlist w-14 h-14 rounded-2xl bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center transition shadow-sm"
                                            data-id="{{ $featured->id }}">
                                            <i
                                                class="{{ in_array($featured->id, $wishlistIds) ? 'fas text-red-500' : 'far text-slate-400 group-hover:text-red-500' }} fa-heart text-xl"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Wave divider -->
                <div class="absolute bottom-0 left-0 right-0 w-full overflow-hidden leading-[0]">
                    <svg class="relative block w-[calc(100%+1.3px)] h-[50px] md:h-[80px]" data-name="Layer 1"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                        <path
                            d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C59.71,118.08,130.83,121.23,189.5,109.28Z"
                            class="fill-slate-50 transition-colors duration-500"></path>
                    </svg>
                </div>
            </section>

            <!-- Filtros e Busca -->
            <section class="py-12 border-b border-slate-200 bg-white">
                <div class="container mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="flex items-center gap-4 overflow-x-auto pb-2 md:pb-0 scroll-hide">
                        <button
                            class="px-5 py-2.5 rounded-2xl bg-blue-50 text-blue-600 font-black text-[11px] uppercase tracking-widest whitespace-nowrap border border-blue-100 shadow-sm">Todos
                            Cursos</button>
                        <button
                            class="px-5 py-2.5 rounded-2xl bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-slate-800 border border-slate-200 font-black text-[11px] uppercase tracking-widest whitespace-nowrap transition">Finanças</button>
                        <button
                            class="px-5 py-2.5 rounded-2xl bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-slate-800 border border-slate-200 font-black text-[11px] uppercase tracking-widest whitespace-nowrap transition">Vendas</button>
                        <button
                            class="px-5 py-2.5 rounded-2xl bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-slate-800 border border-slate-200 font-black text-[11px] uppercase tracking-widest whitespace-nowrap transition">Networking</button>
                    </div>

                    <div class="relative w-full md:w-80">
                        <input type="text" placeholder="Buscar treinamento..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 pl-12 pr-4 text-sm font-medium focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all outline-none">
                        <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                </div>
            </section>

            <!-- Grid de Cursos -->
            <section class="py-24 relative bg-slate-50">
                <div class="container mx-auto px-6 relative z-10">
                    <div class="flex items-end justify-between mb-12 px-4 border-b border-slate-200 pb-4">
                        <h2 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-4">
                            <span
                                class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-xs text-blue-600 border border-blue-200">
                                <i class="fas fa-th-large"></i>
                            </span>
                            Catálogo Completo
                        </h2>
                        <span
                            class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full border border-blue-100">
                            {{ $totalCount }} Disponíveis
                        </span>
                    </div>

                    @if($coursesCollection->isEmpty())
                        <div class="glass-card rounded-[3rem] p-20 text-center flex flex-col items-center bg-white shadow-xl">
                            <div
                                class="w-20 h-20 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-100 mb-8">
                                <i class="fas fa-ghost text-4xl"></i>
                            </div>
                            <h3 class="text-2xl font-black mb-4 text-slate-900">Ainda Estamos Filmando...</h3>
                            <p class="text-slate-500 max-w-sm mb-10">Novas masterclasses estão em produção e serão liberadas em
                                breve para o ecossistema UNN.</p>
                            <a href="{{ route('panel.dashboard') }}"
                                class="px-8 py-3 bg-slate-100 hover:bg-slate-200 rounded-xl font-black text-slate-700 text-xs tracking-widest transition">
                                VOLTAR AO PAINEL </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 xl:gap-10">
                            @foreach($coursesCollection as $course)
                                @php
                                    $cPrice = (float) ($course->price ?? 0);
                                    $cAuthor = $course->author_name ?? optional($course->creator)->name ?? 'UNN';
                                    $cThumb = !empty($course->thumbnail) ? $resolveImageUrl($course->thumbnail) : 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=600&auto=format&fit=crop';
                                    $cHasAccess = auth()->check() && ($course instanceof \App\Models\Course) ? auth()->user()->hasCourseAccess($course) : false;
                                    $cRouteParam = $course->slug ?: ($course->id ?? null);
                                    $cShowUrl = (!$isDemo && $cRouteParam) ? route('courses.show', $cRouteParam) : '#';
                                    $cCheckoutUrl = (!$isDemo && !empty($course->id)) ? route('checkout.show', $course->id) : '#';
                                @endphp

                                <div
                                    class="glass-card rounded-[2.5rem] overflow-hidden bg-white hover:bg-white transition-all duration-500 group border-slate-100 shadow-[0_20px_40px_-20px_rgba(0,0,0,0.1)] hover:shadow-[0_25px_50px_-15px_rgba(0,0,0,0.15)] hover:-translate-y-2 flex flex-col">
                                    <div class="h-60 relative overflow-hidden bg-slate-100">
                                        <img src="{{ $cThumb }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
                                            alt="Title">
                                        <div
                                            class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-slate-900/10 to-transparent">
                                        </div>

                                        <div class="absolute top-5 left-5 flex flex-col gap-2">
                                            @if($course->is_featured)
                                                <span
                                                    class="px-2.5 py-1 bg-amber-500 text-white text-[9px] font-black uppercase tracking-widest rounded-lg shadow-sm">Premium</span>
                                            @endif
                                            @if($course->is_demo)
                                                <span
                                                    class="px-2.5 py-1 bg-white/90 text-slate-800 text-[9px] font-black uppercase tracking-widest rounded-lg border border-slate-200">Demo</span>
                                            @endif
                                        </div>

                                        <div class="absolute bottom-5 right-5">
                                            <span
                                                class="px-3 md:px-4 py-1.5 md:py-2 bg-white/95 backdrop-blur text-slate-800 text-[10px] md:text-xs font-black rounded-lg border border-slate-200 shadow-sm flex items-center gap-1.5">
                                                <i class="far fa-play-circle text-blue-600"></i>
                                                {{ (int) ($course->duration ?? 0) }}min
                                            </span>
                                        </div>
                                    </div>

                                    <div class="p-6 xl:p-8 space-y-4 flex flex-col flex-1">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 rounded-full overflow-hidden border border-slate-200 bg-slate-50">
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($cAuthor) }}&background=2563eb&color=fff"
                                                    class="w-full h-full object-cover">
                                            </div>
                                            <span
                                                class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $cAuthor }}</span>
                                        </div>

                                        <h4
                                            class="text-[1.15rem] xl:text-xl font-black tracking-tight group-hover:text-blue-600 transition-colors leading-tight min-h-[56px] flex items-start text-slate-900 line-clamp-2">
                                            {{ Str::limit($course->title, 60) }}
                                        </h4>

                                        <div class="flex items-center justify-between pt-6 border-t border-slate-100 mt-auto">
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Investimento</span>
                                                <span class="text-base xl:text-lg font-black text-slate-900">
                                                    {{ $cPrice > 0 ? 'R$ ' . number_format($cPrice, 2, ',', '.') : 'Gratuito' }}
                                                </span>
                                            </div>

                                            @if($cHasAccess)
                                                <a href="{{ $cShowUrl }}"
                                                    class="px-5 xl:px-6 py-2.5 xl:py-3 bg-slate-50 text-slate-700 border border-slate-200 rounded-xl font-black text-[10px] tracking-widest hover:bg-blue-600 hover:text-white hover:border-transparent transition-all shadow-sm">
                                                    ACESSAR
                                                </a>
                                            @else
                                                <a href="{{ $cCheckoutUrl }}"
                                                    class="px-5 xl:px-6 py-2.5 xl:py-3 bg-blue-600 text-white rounded-xl font-black text-[10px] tracking-widest hover:bg-blue-700 hover:shadow-lg transition-all shadow-md shadow-blue-500/20 {{ $isDemo ? 'opacity-50 pointer-events-none' : '' }}">
                                                    MATRICULAR
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if(method_exists($courses, 'links'))
                            <div class="mt-20 flex justify-center">
                                {{ $courses->links('pagination::tailwind') }}
                            </div>
                        @endif
                    @endif
                </div>
            </section>

            <!-- Call to Action Final (Substituindo o antigo Neon/Extensivo) -->
            <section class="py-12 md:py-16 px-4 md:px-12 lg:px-24 bg-white pb-24 border-t border-slate-100">
                <div class="max-w-6xl mx-auto">
                    <div
                        class="unn-events-cta bg-slate-900 rounded-[32px] px-6 md:px-14 py-14 md:py-16 text-center shadow-xl relative overflow-hidden">
                        <div class="absolute inset-0 opacity-20"
                            style="background-image: radial-gradient(rgba(255,255,255,0.45) 1px, transparent 1px); background-size: 42px 42px;">
                        </div>

                        <div class="relative">
                            <span
                                class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[10px] font-black uppercase text-white tracking-[0.2em] border border-white/30 bg-white/10 backdrop-blur mb-5 shadow-sm">
                                <i class="fas fa-rocket"></i> Eleve seu Nível
                            </span>

                            <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white">
                                Acesso Ilimitado à UNN Academy
                            </h2>
                            <p class="mt-4 text-white/80 text-lg sm:text-xl max-w-2xl mx-auto font-medium">
                                Networking direto com palestrantes e convites VIP para eventos exclusivos tornam a nossa
                                assinatura a escolha ideal.
                            </p>

                            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                                <a href="{{ route('premium') }}"
                                    class="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-full font-black text-sm sm:text-base bg-white shadow-lg hover:shadow-xl hover:scale-105 active:scale-95 transition-all whitespace-nowrap"
                                    style="color: var(--unn-azul-1)">
                                    <i class="fas fa-crown"></i>
                                    Conhecer Planos Premium
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const wishlistBtns = document.querySelectorAll('.btn-wishlist');

                    wishlistBtns.forEach(btn => {
                        btn.addEventListener('click', async (e) => {
                            e.preventDefault();
                            if (!{{ auth()->check() ? 'true' : 'false' }}) {
                                window.location.href = '{{ route('login') }}';
                                return;
                            }

                            const courseId = btn.dataset.id;
                            const icon = btn.querySelector('i');

                            const isActive = icon.classList.contains('fas');
                            if (isActive) {
                                icon.classList.replace('fas', 'far');
                                icon.classList.remove('text-red-500');
                                icon.classList.add('text-slate-400');
                            } else {
                                icon.classList.replace('far', 'fas');
                                icon.classList.remove('text-slate-400');
                                icon.classList.add('text-red-500');
                            }

                            icon.classList.add('scale-150');
                            setTimeout(() => icon.classList.remove('scale-150'), 300);

                            try {
                                const response = await fetch(`/painel/minha-lista/toggle/${courseId}`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    }
                                });
                                const data = await response.json();

                                document.querySelectorAll(`.btn-wishlist[data-id="${courseId}"] i`).forEach(i => {
                                    if (data.is_wishlisted) {
                                        i.classList.replace('far', 'fas');
                                        i.classList.remove('text-slate-400');
                                        i.classList.add('text-red-500');
                                    } else {
                                        i.classList.replace('fas', 'far');
                                        i.classList.remove('text-red-500');
                                        i.classList.add('text-slate-400');
                                    }
                                });
                            } catch (err) {
                                console.error('Erro wishlist:', err);
                            }
                        });
                    });
                });
            </script>
        @endpush
@endsection