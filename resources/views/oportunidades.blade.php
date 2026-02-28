@extends('layouts.app')

@section('title', 'Oportunidades de Carreira - UNN')

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
                    0.78fr
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
                    0.68fr
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
        $vagasCollection = method_exists($vagas, 'getCollection') ? $vagas->getCollection() : collect($vagas);
        $totalCount = method_exists($vagas, 'total') ? (int) $vagas->total() : $vagasCollection->count();
        $featuredJob = $vagasCollection->first();
        $otherJobs = $vagasCollection;
        if (request()->page == 1 || !request()->has('page')) {
            $otherJobs = $vagasCollection->skip(1); // skip only on pg 1 if featured is shown
        }

        $featuredImageValue = $featuredJob && $featuredJob->image
            ? asset($featuredJob->image)
            : 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1400';
    @endphp

    <div class="min-h-screen">
        <section class="unn-events-hero relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-black/0 via-black/0 to-black/20 pointer-events-none"></div>

            <div class="px-4 md:px-12 lg:px-24 pt-10 md:pt-14 pb-14 md:pb-20 relative">
                <div class="max-w-6xl mx-auto">
                    <div class="text-center">
                        <span class="inline-flex items-center justify-center px-6 py-2 rounded-full text-sm font-bold text-white border border-white/20 bg-white/15 backdrop-blur">
                            Carreiras & Oportunidades
                        </span>
                        <h1 class="mt-6 text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-white mb-6">
                            Descubra seu próximo passo <br class="hidden md:block" />
                            na UNN Startups
                        </h1>
                        <p class="mt-3 text-white/80 text-base sm:text-lg mb-8 max-w-2xl mx-auto">
                            Conecte-se com empresas inovadoras, aplique para vagas exclusivas e acelere sua trajetória profissional com precisão.
                        </p>

                        <!-- Search/Filter Bar -->
                        <div class="max-w-4xl mx-auto bg-white/10 border border-white/20 backdrop-blur-md p-3 md:p-4 rounded-3xl md:rounded-full">
                            <form method="GET" action="" class="flex flex-col md:flex-row gap-3 md:gap-2 relative">
                                <div class="flex-1 filter-input relative flex items-center h-14 bg-white rounded-full px-5">
                                    <i class="fas fa-search text-slate-400 mr-2"></i>
                                    <input type="text" name="area" placeholder="Cargo, tecnologia ou palavra-chave..."
                                        value="{{ request('area') }}"
                                        class="w-full h-full bg-transparent border-none outline-none focus:ring-0 text-slate-700 font-medium placeholder:text-slate-400">
                                </div>

                                <div class="w-full md:w-64 filter-input relative flex items-center h-14 bg-white rounded-full px-5">
                                    <i class="fas fa-map-marker-alt text-slate-400 mr-2"></i>
                                    <input type="text" name="local" placeholder="Ex: São Paulo, Remoto"
                                        value="{{ request('local') }}"
                                        class="w-full h-full bg-transparent border-none outline-none focus:ring-0 text-slate-700 font-medium placeholder:text-slate-400">
                                </div>

                                <button type="submit"
                                    class="h-14 px-8 rounded-full btn-primary font-bold flex items-center justify-center gap-2 shrink-0 shadow-lg group">
                                    Buscar <i class="fas fa-search text-sm group-hover:scale-110 transition-transform"></i>
                                </button>
                            </form>
                        </div>
                        @if(request()->anyFilled(['area', 'local', 'empresa', 'tipo']))
                            <div class="mt-3 flex items-center justify-center gap-2">
                                <a href="{{ route('jobs.public.index') }}"
                                    class="text-xs text-white/80 bg-white/10 hover:bg-white/20 px-3 py-1 rounded-full transition-colors font-bold uppercase tracking-widest flex items-center gap-2 shadow-sm">
                                    <i class="fas fa-times"></i> Limpar filtros 
                                </a>
                            </div>
                        @endif
                    </div>

                    @if($featuredJob && (request()->page == 1 || !request()->has('page')))
                        <div class="mt-10 md:mt-14">
                            <div class="rounded-[32px] overflow-hidden border border-white/20 bg-white/10 backdrop-blur shadow-[0_40px_120px_-60px_rgba(0,0,0,0.65)]">
                                <div class="grid lg:grid-cols-2">
                                    <div class="relative min-h-[260px] lg:min-h-[460px] bg-white">
                                        <img src="{{ $featuredImageValue }}" alt="{{ $featuredJob->company_name }}" class="absolute inset-0 w-full h-full object-contain p-10 mix-blend-multiply opacity-80">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/5 via-black/0 to-transparent"></div>

                                        <span class="absolute top-4 sm:top-6 left-1/2 -translate-x-1/2 px-4 py-2 sm:px-6 sm:py-3 text-sm sm:text-base rounded-full font-bold text-white shadow-lg uppercase tracking-widest"
                                            style="background: var(--unn-azul-1)">
                                            <i class="fas fa-star text-yellow-300"></i> Destaque
                                        </span>
                                    </div>

                                    <div class="bg-white p-7 sm:p-8 md:p-10 flex flex-col justify-center">
                                        <div class="flex flex-wrap items-center gap-2 mb-3">
                                            <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold border border-blue-100">
                                                {{ $featuredJob->type }}
                                            </span>
                                            <span class="px-3 py-1 bg-purple-50 text-purple-600 rounded-full text-xs font-bold border border-purple-100">
                                                {{ $featuredJob->level ?? 'Pleno/Sênior' }}
                                            </span>
                                        </div>

                                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 leading-tight">
                                            {{ $featuredJob->title }}
                                        </h2>
                                        <p class="mt-3 text-blue-600 font-bold uppercase tracking-widest text-sm">
                                            {{ $featuredJob->company_name ?? 'Empresa Confidencial' }}
                                        </p>

                                        <p class="mt-4 text-slate-600 text-base sm:text-lg leading-relaxed line-clamp-3">
                                            {{ $featuredJob->short_description ?? Str::limit(strip_tags($featuredJob->description), 200) }}
                                        </p>

                                        <div class="mt-8 space-y-5">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-map-marker-alt text-blue-600"></i>
                                                </div>
                                                <div class="flex flex-col justify-center h-12">
                                                    <div class="font-bold text-slate-900">{{ $featuredJob->location ?? 'Global/Remoto' }}</div>
                                                    <div class="text-sm text-slate-500">Local de atuação</div>
                                                </div>
                                            </div>

                                            @if($featuredJob->salary_range)
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-coins text-blue-600"></i>
                                                </div>
                                                <div class="flex flex-col justify-center h-12">
                                                    <div class="font-bold text-slate-900">{{ $featuredJob->salary_range }}</div>
                                                    <div class="text-sm text-slate-500">Faixa salarial</div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>

                                        <div class="mt-10 flex flex-col sm:flex-row gap-4">
                                            <a href="{{ route('jobs.public.show', $featuredJob->id) }}"
                                                class="btn-primary text-white px-8 py-4 rounded-xl font-bold inline-flex items-center justify-center gap-3 shadow-lg hover:shadow-xl transition">
                                                Aplicar agora <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="bg-gradient-to-br from-slate-50 to-blue-50 py-12 md:py-16 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto" id="lista-vagas">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
                    <div>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold"
                            style="background: var(--unn-azul-1); color: white">
                            <i class="fas fa-briefcase"></i> Mural de Vagas
                        </span>
                        <h2 class="events-main-title text-2xl sm:text-3xl md:text-4xl font-black text-gray-900 mt-4">Pesquisa de Oportunidades</h2>
                        <p class="events-subtitle text-gray-600 mt-2 max-w-2xl">
                            Confira as últimas vagas cadastradas no ecossistema e candidate-se hoje mesmo.
                        </p>
                    </div>

                    <a href="{{ route('panel.profile.edit', ['ref_curriculum' => 1]) }}"
                        class="events-top-cta inline-flex items-center justify-center gap-2 px-5 py-3 rounded-full font-bold bg-white border hover:bg-slate-50 transition whitespace-nowrap"
                        style="border-color: var(--unn-azul-1); color: var(--unn-azul-1)">
                        <i class="fas fa-file-invoice"></i> Cadastrar Currículo
                    </a>
                </div>

                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
                    <div class="events-headbar px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
                        <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center btn-primary shadow-sm">
                                <i class="fas fa-list text-white"></i>
                            </span>
                            Últimas Vagas
                        </h3>
                        <span class="events-total-badge inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black bg-white border border-slate-200 text-slate-600 shadow-sm uppercase tracking-widest">
                            <i class="fas fa-briefcase text-slate-400"></i>
                            {{ $totalCount }} {{ $totalCount === 1 ? 'VAGA' : 'VAGAS' }}
                        </span>
                    </div>

                    @if($otherJobs->isEmpty())
                        <div class="p-16 text-center">
                            <div class="text-slate-400 mb-4"><i class="fas fa-search text-5xl"></i></div>
                            <h4 class="text-2xl font-black text-slate-900 mb-2">Nenhuma vaga encontrada</h4>
                            <p class="text-slate-600 mb-6">Não conseguimos localizar nenhuma oportunidade com estes critérios no momento.</p>
                            <a href="{{ route('jobs.public.index') }}" class="btn-primary text-white font-bold px-6 py-3 rounded-full inline-flex items-center justify-center gap-2 shadow-lg">
                                <i class="fas fa-sync-alt"></i> Limpar Buscas
                            </a>
                        </div>
                    @else
                        <div class="bg-slate-50/40 p-4 sm:p-6">
                            <div class="events-table-head px-4 pb-3 text-xs font-black uppercase tracking-wider text-slate-500">
                                <div>Publicação</div>
                                <div>Oportunidade e Empresa</div>
                                <div>Localização</div>
                                <div>Nível / Tipo</div>
                                <div>Remuneração</div>
                                <div class="text-right">Ação</div>
                            </div>

                            <div class="space-y-3">
                                @foreach($otherJobs as $vaga)
                                    @php
                                        $postDate = $vaga->created_at ? $vaga->created_at : now();
                                        $relativeLabel = $postDate->diffForHumans();
                                        
                                        $vagaImg = $vaga->image ? asset($vaga->image) : '';
                                    @endphp

                                    <div class="group rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-[1px] transition will-change-transform relative overflow-hidden">
                                        @if($vaga->is_demo)
                                            <div class="absolute top-0 right-0 bg-amber-100 text-amber-700 text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-bl-xl z-20"><i class="fas fa-flask"></i> DEMO</div>
                                        @endif

                                        <div class="events-table-row p-5 md:p-6 relative z-10">
                                            <div class="events-col-date min-w-0">
                                                <div class="flex md:block items-center justify-between gap-3">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-14 text-center rounded-2xl p-2 border border-slate-200 bg-gradient-to-br from-white to-slate-50 shadow-inner">
                                                            <span class="block font-black text-lg leading-none text-slate-900">{{ $postDate->format('d') }}</span>
                                                            <span class="block uppercase text-xs font-black text-slate-500">{{ $postDate->translatedFormat('M') }}</span>
                                                        </div>
                                                        <div class="md:hidden">
                                                            <div class="text-xs font-bold text-slate-700">
                                                                Postado em
                                                            </div>
                                                            <div class="text-xs text-slate-500 mt-0.5">
                                                                <i class="far fa-clock mr-1"></i>{{ $relativeLabel }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <span class="md:mt-3 inline-flex items-center px-3 py-1 rounded-full text-[10px] uppercase font-black bg-slate-100 text-slate-700 border border-slate-200 text-center tracking-widest">
                                                        {{ $relativeLabel }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="events-col-event min-w-0">
                                                <div class="flex items-start gap-4">
                                                    <div class="relative w-16 h-16 rounded-2xl overflow-hidden border border-slate-200 bg-white flex-shrink-0 flex items-center justify-center shadow-sm">
                                                        @if($vagaImg)
                                                            <img src="{{ $vagaImg }}" alt="Logo" class="w-[85%] h-[85%] object-contain mix-blend-multiply" loading="lazy">
                                                        @else
                                                            <i class="fas fa-building text-3xl text-slate-200"></i>
                                                        @endif
                                                    </div>

                                                    <div class="min-w-0 flex flex-col justify-center min-h-[4rem]">
                                                        <div class="font-black text-slate-900 leading-tight truncate text-base hover:text-blue-600 transition-colors">
                                                            <a href="{{ route('jobs.public.show', $vaga->id) }}" class="focus:outline-none">
                                                                <span class="absolute inset-0" aria-hidden="true"></span>
                                                                {{ $vaga->title }}
                                                            </a>
                                                        </div>

                                                        <div class="mt-1 flex flex-wrap items-center gap-2">
                                                            <span class="text-xs font-bold text-blue-600">
                                                              <i class="fas fa-briefcase text-blue-400 mr-1"></i>{{ $vaga->company_name ?? 'Confidencial' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="events-col-location min-w-0 flex flex-col justify-center">
                                                <div class="text-slate-800 font-bold truncate">
                                                    <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>{{ Str::limit($vaga->location ?? 'Remoto', 25) }}
                                                </div>
                                            </div>

                                            <div class="events-col-capacity min-w-0 flex flex-col justify-center">
                                                <div class="flex flex-col gap-1 items-start">
                                                  <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest bg-blue-50 text-blue-700 border border-blue-100">{{ $vaga->type ?? 'Integral' }}</span>
                                                  <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest bg-purple-50 text-purple-700 border border-purple-100">{{ $vaga->level ?? 'Pleno' }}</span>
                                                </div>
                                            </div>

                                            <div class="events-col-price min-w-0 flex flex-col justify-center">
                                                <div class="font-black text-slate-900 truncate">{{ $vaga->salary_range ?? 'A combinar' }}</div>
                                            </div>

                                            <div class="events-col-action md:text-right flex items-center h-full">
                                                <a href="{{ route('jobs.public.show', $vaga->id) }}"
                                                    class="events-action-btn relative z-10 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-full text-sm font-black text-white btn-primary shadow-lg hover:shadow-xl transition whitespace-nowrap w-full md:w-auto">
                                                    Ver Vaga <i class="fas fa-arrow-right text-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="p-6 border-t border-slate-100 flex justify-center bg-white">
                             {{ $vagas->links('pagination::tailwind') }}
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
                            <i class="fas fa-radar"></i> Mantenha seu Radar Ativo
                        </span>
                        
                        <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white">
                            As melhores empresas buscam talentos diariamente.
                        </h2>
                        <p class="mt-4 text-white/80 text-lg sm:text-xl max-w-2xl mx-auto">
                            Prepare seu currículo e seja notado por recrutadores de ponta no ecossistema UNN.
                        </p>

                        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                            <a href="{{ route('panel.profile.edit', ['ref_curriculum' => 1]) }}"
                                class="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-full font-black text-sm sm:text-base bg-white shadow-lg hover:shadow-xl hover:scale-105 active:scale-95 transition-all whitespace-nowrap"
                                style="color: var(--unn-azul-1)">
                                <i class="fas fa-file-invoice"></i>
                                Cadastrar Currículo Aqui
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection