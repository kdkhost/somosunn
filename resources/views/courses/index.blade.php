@extends('layouts.app')

@section('title', 'Academy - SOMOS UNN')

@push('styles')
    <style>
        .unn-events-hero {
            background:
                radial-gradient(1200px circle at 15% 20%, rgba(255, 255, 255, 0.18) 0%, transparent 55%),
                radial-gradient(900px circle at 85% 0%, rgba(255, 255, 255, 0.12) 0%, transparent 50%),
                linear-gradient(180deg, var(--unn-azul-3) 0%, var(--unn-azul-1) 55%, var(--unn-azul-3) 100%);
        }

        .unn-events-hero::before {
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

        .unn-events-cta {
            background: linear-gradient(180deg, var(--unn-azul-3) 0%, var(--unn-azul-1) 55%, var(--unn-azul-3) 100%);
        }

        .events-table-head,
        .events-table-row {
            display: grid;
            gap: 1rem;
        }

        .events-table-head > div,
        .events-table-row > div {
            min-width: 0;
        }

        @media (min-width: 1400px) {
            .events-table-head,
            .events-table-row {
                grid-template-columns:
                    0.6fr
                    2fr
                    1.4fr
                    0.8fr
                    0.7fr
                    0.96fr;
                align-items: center;
            }
        }

        @media (min-width: 1200px) and (max-width: 1399.98px) {
            .events-table-head,
            .events-table-row {
                grid-template-columns:
                    0.6fr
                    1.7fr
                    1.1fr
                    0.7fr
                    0.6fr
                    0.8fr;
                gap: 0.65rem;
                align-items: center;
            }

            .events-table-head {
                font-size: 0.68rem;
            }

            .events-col-action .events-action-btn {
                max-width: 138px;
                padding-left: 0.7rem;
                padding-right: 0.7rem;
                font-size: 0.93rem;
            }

            .events-col-event .font-black,
            .events-col-location .text-slate-800 {
                font-size: 0.94rem;
            }
        }

        @media (min-width: 1200px) {
            .events-col-action {
                display: flex;
                align-items: center;
                justify-content: flex-end;
            }

            .events-col-action .events-action-btn {
                width: 100%;
                max-width: 170px;
                min-width: 0;
            }

            .events-col-location,
            .events-col-event {
                overflow: hidden;
            }

            .events-col-location .text-slate-800 {
                line-height: 1.2;
                word-break: break-word;
            }
        }

        @media (max-width: 1199.98px) {
            .events-table-head {
                display: none !important;
            }

            .events-table-row {
                grid-template-columns: 1fr;
            }

            .events-col-action .events-action-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 767.98px) {
            .events-col-action .events-action-btn {
                padding-left: 0.95rem;
                padding-right: 0.95rem;
                font-size: 0.95rem;
            }
        }

        .events-total-badge {
            white-space: nowrap;
        }

        @media (max-width: 640px) {
            .events-headbar {
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .events-total-badge {
                margin-left: auto;
                padding: 0.35rem 0.65rem;
                font-size: 0.74rem;
                gap: 0.35rem;
            }
        }

        @media (max-width: 1024px) {
            .events-top-cta {
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            .events-main-title {
                font-size: clamp(1.85rem, 4.4vw, 2.5rem);
                line-height: 1.2;
            }

            .events-subtitle {
                font-size: clamp(1rem, 2.4vw, 1.2rem);
            }
        }
    </style>
@endpush

@section('content')
    @php
        $isDemo = (bool) ($isDemo ?? false);
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
        $featuredImage = $featured?->thumbnail ? $resolveImageUrl($featured->thumbnail) : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=1600&auto=format&fit=crop';

        $featuredRouteParam = $featured?->slug ?: ($featured?->id ?? null);
        $featuredShowUrl = (!$isDemo && $featuredRouteParam) ? route('courses.show', $featuredRouteParam) : '#';
        $featuredCheckoutUrl = (!$isDemo && !empty($featured?->id)) ? route('checkout.show', $featured->id) : '#';
        
        $wishlistIds = auth()->check() ? auth()->user()->wishlist()->pluck('course_id')->toArray() : [];
    @endphp

    <div class="min-h-screen">
        <section class="unn-events-hero relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-black/0 via-black/0 to-black/20 pointer-events-none"></div>

            <div class="px-4 md:px-12 lg:px-24 pt-10 md:pt-14 pb-14 md:pb-20 relative">
                <div class="max-w-6xl mx-auto">
                    <div class="text-center">
                        <span class="inline-flex items-center justify-center px-6 py-2 rounded-full text-sm font-bold text-white border border-white/20 bg-white/15 backdrop-blur">
                            UNN Academy
                        </span>
                        <h1 class="mt-6 text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-white">
                            A Maestria dos Negócios <br class="hidden md:block" />
                            Começa Aqui.
                        </h1>
                        <p class="mt-3 text-white/80 text-base sm:text-lg">
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

                        <div class="mt-10 md:mt-14">
                            <div class="rounded-[32px] overflow-hidden border border-white/20 bg-white/10 backdrop-blur shadow-[0_40px_120px_-60px_rgba(0,0,0,0.65)]">
                                <div class="grid lg:grid-cols-2">
                                    <div class="relative min-h-[260px] lg:min-h-[460px]">
                                        <img src="{{ $featuredImage }}" alt="Curso UNN" class="absolute inset-0 w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-black/10 to-transparent"></div>

                                        <span class="absolute top-4 sm:top-6 left-1/2 -translate-x-1/2 px-4 py-2 sm:px-6 sm:py-3 text-sm sm:text-base rounded-full font-bold text-white shadow-lg uppercase tracking-widest"
                                            style="background: var(--unn-azul-1)">
                                            <i class="fas fa-crown text-yellow-300"></i> Treinamento Master
                                        </span>
                                    </div>

                                    <div class="bg-white p-7 sm:p-8 md:p-10">
                                        <div class="flex flex-wrap items-center gap-2 mb-3">
                                            <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold border border-blue-100 uppercase tracking-widest">
                                                Masterclass
                                            </span>
                                            @if($featured->is_certificate_enabled)
                                                <span class="px-3 py-1 bg-green-50 text-green-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-green-100">
                                                    Certificado Incluso
                                                </span>
                                            @endif
                                        </div>

                                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 leading-tight">
                                            {{ $featured->title ?? 'Estratégias de Alto Impacto' }}
                                        </h2>
                                        
                                        <div class="flex items-center gap-3 mt-3">
                                            <div class="w-8 h-8 rounded-full overflow-hidden border border-slate-200">
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($featuredAuthor) }}&background=2563eb&color=fff" class="w-full h-full object-cover">
                                            </div>
                                            <p class="text-slate-600 font-bold uppercase tracking-widest text-xs">
                                                {{ $featuredAuthor }}
                                            </p>
                                        </div>

                                        <p class="mt-4 text-slate-600 text-base sm:text-lg leading-relaxed line-clamp-3">
                                            {{ $featured->short_description ?: 'Um mergulho profundo nas metodologias que estão moldando o futuro dos negócios globais. Exclusivo para membros UNN.' }}
                                        </p>

                                        <div class="mt-8 space-y-5">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-clock text-blue-600"></i>
                                                </div>
                                                <div class="flex flex-col justify-center h-12">
                                                    <div class="font-bold text-slate-900">{{ $featuredDurationLabel }}</div>
                                                    <div class="text-sm text-slate-500">Carga horária estimada</div>
                                                </div>
                                            </div>

                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-coins text-blue-600"></i>
                                                </div>
                                                <div class="flex flex-col justify-center h-12">
                                                    <div class="font-bold text-slate-900">{{ $featuredPrice > 0 ? 'R$ ' . number_format($featuredPrice, 2, ',', '.') : 'GRATUITO' }}</div>
                                                    <div class="text-sm text-slate-500">Valor de investimento</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-10 flex flex-col sm:flex-row gap-4">
                                            <a href="{{ $featuredShowUrl }}"
                                                class="btn-primary text-white px-8 py-4 rounded-xl font-bold inline-flex items-center justify-center gap-3 shadow-lg hover:shadow-xl transition {{ $isDemo ? 'pointer-events-none opacity-60' : '' }}">
                                                {{ $featuredHasAccess ? 'Continuar Assistindo' : 'Garantir Acesso' }} <i class="fas fa-arrow-right"></i>
                                            </a>
                                            <button
                                                class="btn-wishlist px-8 py-4 rounded-xl font-bold border-2 border-slate-200 text-slate-700 hover:bg-slate-50 transition inline-flex items-center justify-center group" data-id="{{ $featured->id }}">
                                                <i class="{{ in_array($featured->id, $wishlistIds) ? 'fas text-red-500' : 'far text-slate-400 group-hover:text-red-500' }} fa-heart transition-colors"></i> 
                                                <span class="ml-2">Salvar</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-10 md:mt-14 max-w-3xl mx-auto">
                            <div class="bg-white rounded-[32px] shadow-2xl p-10 text-center">
                                <div class="text-slate-400 mb-4"><i class="fas fa-ghost text-5xl"></i></div>
                                <h2 class="text-2xl sm:text-3xl font-black text-slate-900">Ainda Estamos Filmando...</h2>
                                <p class="mt-2 text-slate-600">Novas masterclasses estão em produção e serão liberadas em breve para o ecossistema.</p>
                                <a href="{{ route('panel.dashboard') }}"
                                    class="mt-6 inline-flex px-8 py-3 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl font-black uppercase tracking-widest text-[10px] transition">
                                    VOLTAR AO PAINEL
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="bg-gradient-to-br from-slate-50 to-blue-50 py-12 md:py-16 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
                    <div>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold"
                            style="background: var(--unn-azul-1); color: white">
                            <i class="fas fa-th-large"></i> Catálogo
                        </span>
                        <h2 class="events-main-title text-2xl sm:text-3xl md:text-4xl font-black text-gray-900 mt-4">Treinamentos Disponíveis</h2>
                        <p class="events-subtitle text-gray-600 mt-2 max-w-2xl">
                            Acesse conhecimentos exclusivos aplicados diretamente por líderes de mercado.
                        </p>
                    </div>

                    <a href="{{ route('premium') }}"
                        class="events-top-cta inline-flex items-center justify-center gap-2 px-5 py-3 rounded-full font-bold bg-white border hover:bg-slate-50 transition whitespace-nowrap"
                        style="border-color: var(--unn-azul-1); color: var(--unn-azul-1)">
                        <i class="fas fa-crown"></i> Ver Planos Premium
                    </a>
                </div>

                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
                    <div class="events-headbar px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
                        <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center btn-primary shadow-sm">
                                <i class="fas fa-play text-white"></i>
                            </span>
                            Todos os Cursos
                        </h3>
                        <span class="events-total-badge inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black bg-white border border-slate-200 text-slate-600 shadow-sm uppercase tracking-widest">
                            <i class="fas fa-layer-group text-slate-400"></i>
                            {{ $totalCount }} {{ $totalCount === 1 ? 'Conteúdo' : 'Conteúdos' }}
                        </span>
                    </div>

                    @if($coursesCollection->isEmpty())
                        <div class="p-10 text-center">
                            <div class="text-slate-400 mb-4"><i class="fas fa-search text-4xl"></i></div>
                            <h4 class="text-xl font-black text-slate-900 mb-2">Sem cursos na biblioteca</h4>
                            <p class="text-slate-600">Não encontramos nenhum conteúdo público no momento.</p>
                        </div>
                    @else
                        <div class="bg-slate-50/40 p-4 sm:p-6">
                            <div class="events-table-head px-4 pb-3 text-xs font-black uppercase tracking-wider text-slate-500">
                                <div>Duração</div>
                                <div>Nome do Treinamento</div>
                                <div>Status / Acesso</div>
                                <div>Modalidade</div>
                                <div>Investimento</div>
                                <div class="text-right">Ação</div>
                            </div>

                            <div class="space-y-3">
                                @foreach($coursesCollection as $course)
                                    @php
                                        $cPrice = (float) ($course->price ?? 0);
                                        $cAuthor = $course->author_name ?? optional($course->creator)->name ?? 'UNN';
                                        $cThumb = !empty($course->thumbnail) ? $resolveImageUrl($course->thumbnail) : 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=600&auto=format&fit=crop';
                                        $cHasAccess = auth()->check() && ($course instanceof \App\Models\Course) ? auth()->user()->hasCourseAccess($course) : false;
                                        $cRouteParam = $course->slug ?: ($course->id ?? null);
                                        $cShowUrl = (!$isDemo && $cRouteParam) ? route('courses.show', $cRouteParam) : '#';
                                        $cCheckoutUrl = (!$isDemo && !empty($course->id)) ? route('checkout.show', $course->id) : '#';

                                        $cDuration = (int) ($course->duration ?? 0);
                                        $cDurationLabel = $cDuration > 0
                                            ? (intdiv($cDuration, 60) > 0
                                                ? (intdiv($cDuration, 60) . 'h' . (($cDuration % 60) ? str_pad((string) ($cDuration % 60), 2, '0', STR_PAD_LEFT) : ''))
                                                : ($cDuration . ' min'))
                                            : '--';
                                        
                                        $cCategory = $course->category ?? 'Masterclass';
                                    @endphp

                                    <div class="group rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-[1px] transition will-change-transform relative overflow-hidden">
                                        @if($course->is_demo)
                                            <div class="absolute top-0 right-0 bg-amber-100 text-amber-700 text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-bl-xl z-20"><i class="fas fa-flask"></i> DEMO</div>
                                        @endif
                                        @if($course->is_featured && !$course->is_demo)
                                            <div class="absolute top-0 right-0 bg-amber-500 text-white text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-bl-xl z-20 shadow-sm"><i class="fas fa-star"></i> Destaque</div>
                                        @endif

                                        <div class="events-table-row p-5 md:p-6 relative z-10">
                                            <div class="events-col-date min-w-0 flex items-center h-full">
                                                <div class="flex md:block items-center justify-between gap-3 w-full">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-14 text-center rounded-2xl p-2 border border-slate-200 bg-gradient-to-br from-white to-slate-50 shadow-inner flex flex-col justify-center">
                                                            <span class="block font-black text-lg leading-none text-slate-900"><i class="far fa-play-circle mb-1 text-slate-400"></i></span>
                                                            <span class="block uppercase text-[10px] font-black text-slate-500 tracking-tighter">{{ $cDurationLabel }}</span>
                                                        </div>
                                                        <div class="md:hidden">
                                                            <div class="text-xs font-bold text-slate-700">Duração Media</div>
                                                            <div class="text-xs text-slate-500 mt-0.5"><i class="far fa-clock mr-1"></i>{{ $cDurationLabel }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="events-col-event min-w-0">
                                                <div class="flex items-start gap-4 h-full">
                                                    <div class="relative w-16 h-16 rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 flex-shrink-0 flex items-center justify-center shadow-sm">
                                                        <img src="{{ $cThumb }}" alt="Thumbnail" class="w-full h-full object-cover">
                                                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-black/0 to-transparent"></div>
                                                    </div>

                                                    <div class="min-w-0 flex flex-col justify-center h-full">
                                                        <div class="font-black text-slate-900 leading-tight truncate text-base hover:text-blue-600 transition-colors">
                                                            <a href="{{ $cShowUrl }}" class="focus:outline-none">
                                                                <span class="absolute inset-0" aria-hidden="true"></span>
                                                                {{ $course->title }}
                                                            </a>
                                                        </div>

                                                        <div class="mt-1 flex flex-wrap items-center gap-2">
                                                            <span class="text-xs font-bold text-slate-500">
                                                              <i class="fas fa-microphone-alt text-slate-400 mr-1"></i>{{ $cAuthor }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="events-col-location min-w-0 flex flex-col justify-center">
                                                <div class="text-slate-800 font-bold truncate flex items-center gap-2">
                                                    @if($cHasAccess)
                                                        <i class="fas fa-unlock-alt text-green-500"></i> Acesso Liberado
                                                    @else
                                                        <i class="fas fa-lock text-slate-400"></i> Matrícula Fechada
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="events-col-capacity min-w-0 flex flex-col justify-center">
                                                <div class="flex flex-col gap-1 items-start">
                                                  <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest bg-blue-50 text-blue-700 border border-blue-100">{{ $cCategory }}</span>
                                                  @if($course->is_certificate_enabled)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-700 border border-emerald-100">Certificado</span>
                                                  @endif
                                                </div>
                                            </div>

                                            <div class="events-col-price min-w-0 flex flex-col justify-center">
                                                @if($cPrice > 0)
                                                    <div class="font-black text-slate-900 truncate">R$ {{ number_format($cPrice, 2, ',', '.') }}</div>
                                                    <div class="text-xs text-slate-500 mt-1">vitalício</div>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-md text-[10px] font-black uppercase bg-green-100 text-green-700 tracking-widest border border-green-200">100% GRÁTIS</span>
                                                @endif
                                            </div>

                                            <div class="events-col-action md:text-right flex items-center h-full gap-2 justify-end">
                                                <button class="btn-wishlist relative z-10 w-9 h-9 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center hover:bg-slate-100 transition shadow-sm" data-id="{{ $course->id }}">
                                                    <i class="{{ in_array($course->id, $wishlistIds) ? 'fas text-red-500' : 'far text-slate-400 group-hover:text-red-500' }} fa-heart transition-colors text-xs"></i> 
                                                </button>
                                                <a href="{{ $cShowUrl }}"
                                                    class="events-action-btn relative z-10 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-full text-sm font-black text-white btn-primary shadow-lg hover:shadow-xl transition whitespace-nowrap w-full md:w-auto {{ $isDemo ? 'opacity-50 pointer-events-none' : '' }}">
                                                    {{ $cHasAccess ? 'Acessar' : 'Detalhes' }} <i class="fas fa-chevron-right text-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="p-6 border-t border-slate-100 flex justify-center bg-white">
                            @if(method_exists($courses, 'links'))
                                {{ $courses->links('pagination::tailwind') }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <!-- Call to Action Banner Limpo/Claro (Substituindo o neon) -->
        <section class="py-12 md:py-16 px-4 md:px-12 lg:px-24 bg-white">
            <div class="max-w-6xl mx-auto">
                <div class="unn-events-cta rounded-[32px] px-6 md:px-14 py-14 md:py-16 text-center shadow-2xl relative overflow-hidden">
                    <div class="absolute inset-0 opacity-20"
                        style="background-image: radial-gradient(rgba(255,255,255,0.45) 1px, transparent 1px); background-size: 42px 42px;"></div>

                    <div class="relative">
                        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[10px] font-black uppercase text-white tracking-[0.2em] border border-white/30 bg-white/10 backdrop-blur mb-5">
                            <i class="fas fa-rocket"></i> Eleve seu Nível
                        </span>
                        
                        <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white">
                            Acesso Ilimitado à UNN Academy
                        </h2>
                        <p class="mt-4 text-white/80 text-lg sm:text-xl max-w-2xl mx-auto">
                            Networking direto com palestrantes e convites VIP para eventos exclusivos tornam a nossa assinatura a escolha ideal.
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