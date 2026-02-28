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

    <div class="min-h-screen bg-slate-950 text-white selection:bg-blue-500/30">
        <!-- Style Overrides -->
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100;300;400;500;600;700;800;900&display=swap');

            :root {
                --p-font: 'Outfit', sans-serif;
            }

            .academy-hero {
                background: radial-gradient(circle at 50% -20%, rgba(37, 99, 235, 0.15) 0%, transparent 70%);
            }

            .glass-card {
                background: rgba(255, 255, 255, 0.03);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.08);
            }

            .glass-card-hover:hover {
                background: rgba(255, 255, 255, 0.06);
                border-color: rgba(255, 255, 255, 0.15);
                transform: translateY(-8px);
            }

            .text-gradient {
                background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .btn-glow:hover {
                box-shadow: 0 0 20px rgba(37, 99, 235, 0.4);
            }
        </style>

        <div class="min-h-screen">
            <!-- Hero imersivo -->
            <section class="academy-hero relative pt-20 pb-20 overflow-hidden">
                <div
                    class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 pointer-events-none">
                </div>

                <div class="container mx-auto px-6 relative z-10">
                    <div class="flex flex-col items-center text-center space-y-8 mb-20">
                        <div
                            class="inline-flex items-center gap-3 px-4 py-2 rounded-2xl bg-white/5 border border-white/10 text-blue-400 text-xs font-black uppercase tracking-[0.3em] animate-fade-in">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                            UNN Academy
                        </div>

                        <h1
                            class="text-5xl md:text-7xl lg:text-8xl font-black tracking-tighter text-gradient leading-tight max-w-5xl">
                            A Maestria dos Negócios Começa Aqui.
                        </h1>

                        <p class="text-slate-400 text-lg md:text-xl font-medium max-w-2xl leading-relaxed">
                            Domine as habilidades que transformam mercados. Conteúdo exclusivo, prático e desenhado para
                            quem não aceita o comum.
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

                        <!-- Featured Masterclass -->
                        <div class="relative group mt-10">
                            <div
                                class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-[3rem] blur opacity-25 group-hover:opacity-40 transition duration-1000 group-hover:duration-200">
                            </div>

                            <div
                                class="relative flex flex-col lg:flex-row rounded-[3rem] bg-slate-900 overflow-hidden border border-white/10">
                                <div class="w-full lg:w-3/5 h-[400px] lg:h-auto relative">
                                    <img src="{{ $featuredImage ?: $fallbackFeaturedImage }}"
                                        class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-700"
                                        alt="Featured">
                                    <div
                                        class="absolute inset-0 bg-gradient-to-r from-slate-900 via-transparent to-transparent">
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 to-transparent"></div>

                                    <div class="absolute bottom-8 left-8 flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-white/20 shadow-2xl">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($featuredAuthor) }}&background=2563eb&color=fff"
                                                class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Master
                                                Instructor</p>
                                            <p class="text-white font-black">{{ $featuredAuthor }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="w-full lg:w-2/5 p-10 md:p-14 flex flex-col justify-center space-y-8">
                                    <div
                                        class="inline-flex items-center gap-2 text-amber-500 font-black text-xs uppercase tracking-widest bg-amber-500/10 px-4 py-2 rounded-xl self-start">
                                        <i class="fas fa-crown"></i> EM DESTAQUE
                                    </div>

                                    <h3 class="text-3xl md:text-4xl font-black tracking-tight leading-tight">
                                        {{ $featured->title ?? 'Estratégias de Alto Impacto' }}
                                    </h3>

                                    <p class="text-slate-400 text-sm md:text-base font-medium leading-relaxed">
                                        {{ $featured->short_description ?: 'Um mergulho profundo nas metodologias que estão moldando o futuro dos negócios globais. Exclusivo para membros UNN.' }}
                                    </p>

                                    <div class="flex items-center gap-8 py-6 border-y border-white/5">
                                        <div>
                                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">
                                                Duração</p>
                                            <p class="text-white font-black">{{ $featuredDurationLabel }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">
                                                Status</p>
                                            <p class="text-emerald-400 font-black">Certificado incluso</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">
                                                Investimento</p>
                                            <p class="text-white font-black">
                                                {{ $featuredPrice > 0 ? 'R$ ' . number_format($featuredPrice, 2, ',', '.') : 'GRATUITO' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                                        <a href="{{ $featuredShowUrl }}"
                                            class="flex-1 px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black text-center transition-all btn-glow {{ $isDemo ? 'opacity-50 pointer-events-none' : '' }}">
                                            {{ $featuredHasAccess ? 'ASSISTIR AGORA' : 'GARANTIR ACESSO' }}
                                        </a>
                                        <button
                                            class="btn-wishlist w-14 h-14 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 flex items-center justify-center transition"
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
            </section>

            <!-- Filtros e Busca (Exploração) -->
            <section class="py-12 border-y border-white/5 bg-slate-900/30">
                <div class="container mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="flex items-center gap-6 overflow-x-auto pb-2 md:pb-0 scroll-hide">
                        <button
                            class="px-6 py-3 rounded-2xl bg-blue-600 text-white font-black text-xs uppercase tracking-widest whitespace-nowrap">Todos
                            Cursos</button>
                        <button
                            class="px-6 py-3 rounded-2xl bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white font-black text-xs uppercase tracking-widest whitespace-nowrap transition">Finanças</button>
                        <button
                            class="px-6 py-3 rounded-2xl bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white font-black text-xs uppercase tracking-widest whitespace-nowrap transition">Vendas</button>
                        <button
                            class="px-6 py-3 rounded-2xl bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white font-black text-xs uppercase tracking-widest whitespace-nowrap transition">Networking</button>
                    </div>

                    <div class="relative w-full md:w-80">
                        <input type="text" placeholder="Buscar treinamento..."
                            class="w-full bg-white/5 border border-white/10 rounded-2xl py-4 pl-12 pr-4 text-sm font-medium focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all outline-none">
                        <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    </div>
                </div>
            </section>

            <!-- Grid de Cursos -->
            <section class="py-24">
                <div class="container mx-auto px-6">
                    <div class="flex items-center justify-between mb-16 px-4">
                        <h2 class="text-3xl font-black tracking-tight text-white flex items-center gap-4">
                            <span class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-xs">
                                <i class="fas fa-th-large"></i>
                            </span>
                            Catálogo Completo
                        </h2>
                        <p class="text-slate-500 font-bold text-xs uppercase tracking-[0.2em]">{{ $totalCount }} Disponíveis
                        </p>
                    </div>

                    @if($coursesCollection->isEmpty())
                        <div class="glass-card rounded-[3rem] p-20 text-center flex flex-col items-center">
                            <div class="w-20 h-20 rounded-3xl bg-white/5 flex items-center justify-center text-slate-600 mb-8">
                                <i class="fas fa-ghost text-4xl"></i>
                            </div>
                            <h3 class="text-2xl font-black mb-4">Ainda Estamos Filmando...</h3>
                            <p class="text-slate-500 max-w-sm mb-10">Novas masterclasses estão em produção e serão liberadas em
                                breve para o ecossistema UNN.</p>
                            <a href="{{ route('panel.dashboard') }}"
                                class="px-8 py-3 bg-white/10 hover:bg-white/20 rounded-xl font-black text-white text-xs tracking-widest transition">
                                VOLTAR AO PAINEL </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
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
                                                class="glass-card rounded-[2.5rem] overflow-hidden transition-all duration-500 group academy-course-item glass-card-hover">
                                                <div class="h-64 relative overflow-hidden">
                                                    <img src="{{ $cThumb }}"
                                                        class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                                                        alt="Title">
                                                    <div
                                                        class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent opacity-60">
                                                    </div>

                                                    <div class="absolute top-6 left-6 flex flex-col gap-2">
                                                        @if($course->is_featured)
                                                            <span
                                                                class="px-3 py-1 bg-amber-500 text-white text-[9px] font-black uppercase tracking-widest rounded-lg shadow-lg">Premium</span>
                                                        @endif
                                                        @if($course->is_demo)
                                                            <span
                                                                class="px-3 py-1 bg-white/20 backdrop-blur-md text-white text-[9px] font-black uppercase tracking-widest rounded-lg border border-white/20">Demo</span>
                                                        @endif
                                                    </div>

                                                    <div class="absolute bottom-6 right-6">
                                                        <span
                                                            class="px-4 py-2 bg-slate-950/80 backdrop-blur text-white text-xs font-black rounded-xl border border-white/10">
                                                            {{ (int) ($course->duration ?? 0) }}min
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="p-8 space-y-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-6 h-6 rounded-full overflow-hidden border border-white/20">
                                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($cAuthor) }}&background=2563eb&color=fff"
                                                                class="w-full h-full object-cover">
                                                        </div>
                                                        <span
                                                            class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $cAuthor }}</span>
                                                    </div>

                                                    <h4
                                                        class="text-xl font-black tracking-tight group-hover:text-blue-400 transition-colors leading-tight min-h-[56px] flex items-center">
                                                        {{ Str::limit($course->title, 55) }}
                                                    </h4>

                                                    <div class="flex items-center justify-between pt-6 border-t border-white/5">
                                                        <div class="flex flex-col">
                                                            <span
                                                                class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Investimento</span>
                                                            <span class="text-lg font-black text-white">
                                                                {{ $cPrice > 0 ? 'R$ ' . number_format($cPrice, 0, ',', '.') : 'Gratuito' }}
                                                            </span>
                                                        </div>

                                                        @if($cHasAccess)
                                                            <a href="{{ $cShowUrl }}"
                                                                class="px-6 py-3 bg-white text-slate-950 rounded-xl font-black text-[10px] tracking-widest hover:bg-blue-600 hover:text-white transition-all shadow-xl shadow-white/5">
                                                                ACESSAR
                                                            </a>
                                                        @else
                                                            <a href="{{ $cCheckoutUrl }}"
                                                                class="px-6 py-3 bg-blue-600 text-white rounded-xl font-black text-[10px] tracking-widest hover:bg-blue-700 transition-all {{ $isDemo ? 'opacity-50 pointer-events-none' : '' }}">
                                                                MATRICULAR
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                            @endforeach
                        </div>

                        @if(method_exists($courses, 'links'))
                            <div class="mt-20">
                                {{ $courses->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </section>

            <!-- CTA Final -->
            <section class="pb-32 container mx-auto px-6">
                <div
                    class="relative rounded-[4rem] bg-gradient-to-br from-blue-600 to-indigo-800 p-12 md:p-20 overflow-hidden group">
                    <div
                        class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition duration-1000">
                    </div>
                    <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-slate-950/20 rounded-full blur-3xl"></div>

                    <div class="relative z-10 flex flex-col items-center text-center space-y-8">
                        <h3 class="text-4xl md:text-6xl font-black tracking-tighter max-w-3xl leading-tight">
                            Eleve sua Vida para o Próximo Nível no UNN Premium.
                        </h3>
                        <p class="text-white/80 text-lg md:text-xl font-medium max-w-2xl">
                            Acesso ilimitado a todos os cursos, networking direto com palestrantes e convites VIP para
                            eventos presenciais.
                        </p>
                        <div class="pt-6">
                            <a href="{{ route('premium') }}"
                                class="px-10 py-5 bg-white text-blue-700 rounded-[2rem] font-black text-lg shadow-2xl hover:scale-105 transition-transform">
                                CONHECER PLANOS PREMIUM
                            </a>
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