@extends('layouts.app')

@section('title', ($pageData['seo_title'] ?? null) ?: 'Oportunidades de Carreira - UNN')

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

        /* Glassmorphism Light Classes */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            /* Light border */
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05), inset 0 1px 1px rgba(255, 255, 255, 0.8);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .glass-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 40px -15px rgba(0, 0, 0, 0.1), inset 0 1px 1px rgba(255, 255, 255, 0.9), 0 0 0 1px var(--unn-azul-2);
        }

        /* Override to enforce dark mode ONLY when explicit (removing class mapping that breaks layout) */
        .dark .glass-card {
            background: rgba(255, 255, 255, 0.85) !important;
            border-color: rgba(0, 0, 0, 0.05) !important;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05), inset 0 1px 1px rgba(255, 255, 255, 0.8) !important;
        }

        .dark .glass-card:hover {
            box-shadow: 0 25px 40px -15px rgba(0, 0, 0, 0.1), inset 0 1px 1px rgba(255, 255, 255, 0.9), 0 0 0 1px var(--unn-azul-2) !important;
        }

        /* Grid Background Pattern */
        .bg-grid-pattern {
            background-image:
                linear-gradient(to right, rgba(0, 0, 0, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .unn-events-cta {
            background: linear-gradient(180deg, var(--unn-azul-3) 0%, var(--unn-azul-1) 55%, var(--unn-azul-3) 100%);
        }

        /* Filter Controls */
        .filter-input {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 100px;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .filter-input:focus-within {
            background: #fff;
            border-color: var(--unn-azul-1);
            box-shadow: 0 0 0 4px rgba(43, 110, 250, 0.15), inset 0 2px 4px rgba(0, 0, 0, 0);
        }
    </style>
@endpush

@section('content')
    @php
        $pageData = $pageData ?? [];
        $vagasCollection = method_exists($vagas, 'getCollection') ? $vagas->getCollection() : collect($vagas);
        $totalCount = method_exists($vagas, 'total') ? (int) $vagas->total() : $vagasCollection->count();
        $featuredJob = $vagasCollection->first();
        $otherJobs = $vagasCollection;
        if (request()->page == 1 || !request()->has('page')) {
            $otherJobs = $vagasCollection->skip(1); // skip only on pg 1 if featured is shown
        }
    @endphp

    <div class="min-h-screen bg-slate-50 overflow-hidden">

        <!-- Hero Section - IDÊNTICO AO DE EVENTOS (LIMPO E CLARO) -->
        <section class="unn-events-hero relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-black/0 via-black/0 to-black/20 pointer-events-none"></div>

            <div class="px-4 md:px-12 lg:px-24 pt-10 md:pt-14 pb-14 md:pb-20 relative">
                <div class="max-w-6xl mx-auto">
                    <div class="text-center">
                        <span
                            class="inline-flex items-center justify-center px-6 py-2 rounded-full text-sm font-bold text-white border border-white/20 bg-white/15 backdrop-blur">
                            Carreiras & Oportunidades
                        </span>
                        <h1 class="mt-6 text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-white mb-6">
                            {{ ($pageData['hero_title'] ?? null) ?: 'Descubra seu próximo passo na UNN Startups' }}
                        </h1>
                        <p class="mt-3 text-white/80 text-base sm:text-lg mb-8 max-w-2xl mx-auto">
                            {{ ($pageData['hero_subtitle'] ?? null) ?: 'Conecte-se com empresas inovadoras, aplique para vagas exclusivas e acelere sua trajetória profissional com precisão.' }}
                        </p>

                        <!-- Search/Filter Bar -->
                        <div
                            class="max-w-4xl mx-auto bg-white/10 border border-white/20 backdrop-blur-md p-3 md:p-4 rounded-3xl"
                            role="search" aria-label="Filtros de vagas">
                            <form method="GET" action="" class="flex flex-col gap-3">
                                {{-- Linha 1: Busca principal + Local + Buscar --}}
                                <div class="flex flex-col md:flex-row gap-3 md:gap-2">
                                    <div class="flex-1 filter-input relative flex items-center h-14 bg-white rounded-full px-5">
                                        <i class="fas fa-search text-slate-400 mr-2" aria-hidden="true"></i>
                                        <input type="text" name="area" id="filtro-area"
                                            placeholder="Cargo, tecnologia ou palavra-chave..."
                                            value="{{ request('area') }}"
                                            aria-label="Buscar por cargo ou palavra-chave"
                                            class="w-full h-full bg-transparent border-none outline-none focus:ring-0 text-slate-700 font-medium placeholder:text-slate-400">
                                    </div>
                                    <div class="w-full md:w-56 filter-input relative flex items-center h-14 bg-white rounded-full px-5">
                                        <i class="fas fa-map-marker-alt text-slate-400 mr-2" aria-hidden="true"></i>
                                        <input type="text" name="local" id="filtro-local"
                                            placeholder="Ex: São Paulo, Remoto"
                                            value="{{ request('local') }}"
                                            aria-label="Filtrar por localidade"
                                            class="w-full h-full bg-transparent border-none outline-none focus:ring-0 text-slate-700 font-medium placeholder:text-slate-400">
                                    </div>
                                    <button type="submit"
                                        class="h-14 px-8 rounded-full btn-primary font-bold flex items-center justify-center gap-2 shrink-0 shadow-lg group"
                                        aria-label="Buscar vagas">
                                        Buscar <i class="fas fa-search text-sm group-hover:scale-110 transition-transform" aria-hidden="true"></i>
                                    </button>
                                </div>
                                {{-- Linha 2: Filtros avançados (empresa + tipo) --}}
                                <div class="flex flex-col md:flex-row gap-3 md:gap-2">
                                    <div class="flex-1 filter-input relative flex items-center h-12 bg-white/90 rounded-full px-5">
                                        <i class="fas fa-building text-slate-400 mr-2" aria-hidden="true"></i>
                                        <input type="text" name="empresa" id="filtro-empresa"
                                            placeholder="empresa (ex: Acme, Google…)"
                                            value="{{ request('empresa') }}"
                                            aria-label="Filtrar por empresa"
                                            class="w-full h-full bg-transparent border-none outline-none focus:ring-0 text-slate-700 text-sm font-medium placeholder:text-slate-400">
                                    </div>
                                    <div class="w-full md:w-56 filter-input relative flex items-center h-12 bg-white/90 rounded-full px-5">
                                        <i class="fas fa-layer-group text-slate-400 mr-2" aria-hidden="true"></i>
                                        <select name="tipo" id="filtro-tipo"
                                            aria-label="Filtrar por tipo de vaga"
                                            class="w-full h-full bg-transparent border-none outline-none focus:ring-0 text-sm font-medium {{ request('tipo') ? 'text-slate-700' : 'text-slate-400' }} cursor-pointer appearance-none">
                                            <option value="">Tipo de vaga</option>
                                            @foreach($tiposDisponiveis as $tipo)
                                                <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
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
                </div>
            </div>
            <!-- Wave divider Limpo Branco -->
            <div class="absolute bottom-0 left-0 right-0 w-full overflow-hidden leading-[0]">
                <svg class="relative block w-[calc(100%+1.3px)] h-[50px] md:h-[80px]" data-name="Layer 1"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                    <path
                        d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C59.71,118.08,130.83,121.23,189.5,109.28Z"
                        class="fill-slate-50 transition-colors duration-500"></path>
                </svg>
            </div>
        </section>

        <section
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20 {{ $featuredJob && (request()->page == 1 || !request()->has('page')) ? '-mt-8 md:-mt-16' : 'mt-8 md:mt-12' }} mb-20 pt-10">
            <!-- Vaga de Destaque Card Limpo -->
            @if($featuredJob && (request()->page == 1 || !request()->has('page')))
                <div
                    class="glass-card rounded-[2.5rem] p-2 md:p-3 overflow-hidden group shadow-[0_30px_60px_-15px_rgba(0,0,0,0.15)] mb-16 md:mb-24 bg-white/95">
                    <div class="bg-white rounded-[2rem] overflow-hidden flex flex-col lg:flex-row relative">
                        <div
                            class="absolute top-0 right-0 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none">
                        </div>

                        <div
                            class="lg:w-2/5 p-8 md:p-12 border-b lg:border-b-0 lg:border-r border-slate-100 flex flex-col justify-center relative overflow-hidden bg-slate-50/50">
                            <div class="absolute top-6 left-6">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-widest rounded-full border border-amber-200">
                                    <i class="fas fa-crown text-amber-500"></i> Destaque Especial
                                </span>
                            </div>

                            <div
                                class="w-24 h-24 md:w-32 md:h-32 bg-white rounded-[1.5rem] p-4 flex items-center justify-center mx-auto mb-6 transform group-hover:scale-105 transition-transform duration-500 border border-slate-100 overflow-hidden shadow-sm">
                                @if($featuredJob->image)
                                    <img src="{{ asset($featuredJob->image) }}" alt="{{ $featuredJob->company_name }}"
                                        class="w-full h-full object-contain">
                                @else
                                    <i class="fas fa-briefcase text-4xl text-blue-500/30"></i>
                                @endif
                            </div>
                            <h3 class="text-xl md:text-2xl font-black text-slate-900 text-center mb-2">
                                {{ $featuredJob->company_name ?? 'Empresa Confidencial' }}
                            </h3>
                            <div class="flex items-center justify-center gap-2 text-sm font-medium text-slate-500">
                                <i class="fas fa-map-marker-alt text-red-500"></i>
                                {{ $featuredJob->location ?? 'Global/Remoto' }}
                            </div>
                        </div>

                        <div class="lg:w-3/5 p-8 md:p-12 flex flex-col bg-white z-10">
                            <div class="flex flex-wrap items-center gap-3 mb-4">
                                <span
                                    class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold border border-blue-100">
                                    {{ $featuredJob->type }}
                                </span>
                                <span
                                    class="px-3 py-1 bg-purple-50 text-purple-600 rounded-full text-xs font-bold border border-purple-100">
                                    {{ $featuredJob->level ?? 'Pleno/Sênior' }}
                                </span>
                                @if($featuredJob->is_demo)
                                    <span
                                        class="px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-xs font-bold border border-amber-100">
                                        <i class="fas fa-flask" aria-hidden="true"></i> Modo Demo
                                    </span>
                                @endif
                                @if(isset($partnerNames) && in_array(mb_strtolower(trim((string)$featuredJob->company_name)), $partnerNames))
                                    <span
                                        class="px-3 py-1 bg-violet-50 text-violet-700 rounded-full text-xs font-bold border border-violet-100 flex items-center gap-1"
                                        title="Empresa parceira UNN">
                                        <i class="fas fa-handshake" aria-hidden="true"></i> Empresa Parceira
                                    </span>
                                @endif
                            </div>

                            <h2
                                class="text-2xl md:text-4xl font-black text-slate-900 mb-4 leading-tight group-hover:text-blue-600 transition-colors">
                                {{ $featuredJob->title }}
                            </h2>

                            <p class="text-slate-600 text-base md:text-lg mb-8 line-clamp-3">
                                {{ $featuredJob->short_description ?? Str::limit(strip_tags($featuredJob->description), 200) }}
                            </p>

                            <div class="mt-auto flex flex-col sm:flex-row gap-4 items-center">
                                <a href="{{ route('jobs.public.show', $featuredJob->id) }}"
                                    class="w-full sm:w-auto px-8 py-4 rounded-xl btn-primary font-bold flex items-center justify-center gap-2 text-white shadow-xl shadow-blue-500/20">
                                    Explorar Oportunidade <i class="fas fa-arrow-right"></i>
                                </a>
                                @if($featuredJob->salary_range)
                                    <div class="text-slate-500 font-medium text-sm w-full sm:w-auto text-center sm:text-left">
                                        <i class="fas fa-coins text-yellow-500 mr-1"></i> Remuneração: <strong
                                            class="text-slate-800">{{ is_numeric($featuredJob->salary_range) ? 'R$ ' . number_format((float) $featuredJob->salary_range, 2, ',', '.') : $featuredJob->salary_range }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Grid Tradicional de Vagas -->
            <div id="lista-vagas">
                <div
                    class="flex flex-col md:flex-row md:items-end justify-between mb-8 border-b border-slate-200 pb-4 gap-4">
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900 flex items-center gap-3">
                        <i class="fas fa-briefcase text-blue-500"></i> Últimas Publicações
                        <span
                            class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full border border-blue-100 align-middle">
                            {{ $totalCount }} vagas
                        </span>
                    </h2>
                </div>

                @if($otherJobs->isEmpty())
                    <div class="glass-card rounded-3xl p-16 text-center shadow-lg border-slate-200">
                        <div
                            class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-slate-100">
                            <i class="fas fa-search text-3xl text-slate-300"></i>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 mb-2">Ops, nada por aqui ainda!</h3>
                        <p class="text-slate-500 max-w-md mx-auto mb-8">Não encontramos oportunidades exatas para o seu filtro
                            neste momento.</p>
                        <a href="{{ route('jobs.public.index') }}"
                            class="inline-flex btn-primary items-center gap-2 px-6 py-3 rounded-xl font-bold text-white shadow-lg transition text-sm">
                            <i class="fas fa-sync-alt"></i> Limpar Buscas
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 relative" role="list" aria-label="Lista de vagas disponíveis">
                        <!-- BG Light Pattern -->
                        <div class="absolute inset-0 bg-grid-pattern opacity-50 z-0 pointer-events-none rounded-3xl"></div>

                        @foreach($otherJobs as $vaga)
                            <!-- Cartão Premium Refeito Sem Cores Escuras -->
                            <div class="glass-card rounded-2xl flex flex-col group relative z-10 overflow-hidden bg-white/95 border-slate-200 shadow-xl shadow-slate-200/50"
                                role="article"
                                aria-label="Vaga: {{ $vaga->title }} em {{ $vaga->company_name ?? 'Empresa Confidencial' }}"
                                style="animation: fadeInUp 0.5s ease-out forwards; opacity:0; animation-delay: {{ $loop->index * 0.05 }}s;">

                                <div class="p-6 flex-1 flex flex-col">
                                    <div class="flex items-start justify-between mb-5">
                                        <div
                                            class="w-14 h-14 bg-white rounded-xl shadow-md border border-slate-100 flex flex-shrink-0 items-center justify-center overflow-hidden p-2 relative z-10 group-hover:scale-110 transition-transform duration-500">
                                            @if($vaga->image)
                                                <img src="{{ asset($vaga->image) }}" alt="Logo" class="w-full h-full object-contain">
                                            @else
                                                <i class="fas fa-building text-xl text-slate-300"></i>
                                            @endif
                                        </div>
                                        <div class="flex flex-col items-end gap-1 text-right relative z-10">
                                            <span
                                                class="text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center gap-1">
                                                <i class="far fa-clock" aria-hidden="true"></i>
                                                {{ $vaga->created_at ? $vaga->created_at->diffForHumans() : 'Recente' }}
                                            </span>
                                            @if($vaga->is_demo)
                                                <span
                                                    class="bg-amber-100 text-amber-600 text-[9px] px-2 py-0.5 rounded-full font-bold border border-amber-200">
                                                    <i class="fas fa-flask" aria-hidden="true"></i> DEMO
                                                </span>
                                            @endif
                                            @if(isset($partnerNames) && in_array(mb_strtolower(trim((string)$vaga->company_name)), $partnerNames))
                                                <span
                                                    class="bg-violet-100 text-violet-700 text-[9px] px-2 py-0.5 rounded-full font-bold border border-violet-200 flex items-center gap-1"
                                                    title="Empresa parceira UNN">
                                                    <i class="fas fa-handshake text-[8px]" aria-hidden="true"></i> PARCEIRO
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <h3
                                        class="text-xl font-black text-slate-900 mb-1 line-clamp-2 leading-tight group-hover:text-blue-600 transition-colors">
                                        <a href="{{ route('jobs.public.show', $vaga->id) }}" class="focus:outline-none">
                                            <span class="absolute inset-0" aria-hidden="true"></span>
                                            {{ $vaga->title }}
                                        </a>
                                    </h3>

                                    <p class="text-sm font-bold text-blue-600 mb-4">{{ $vaga->company_name ?? 'Confidencial' }}</p>

                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <span
                                            class="bg-slate-50 text-slate-600 text-[11px] px-2.5 py-1 rounded-md font-semibold border border-slate-200">
                                            {{ $vaga->type ?? 'Integral' }}
                                        </span>
                                        <span
                                            class="bg-slate-50 text-slate-600 text-[11px] px-2.5 py-1 rounded-md font-semibold border border-slate-200">
                                            <i class="fas fa-map-marker-alt text-slate-400 mr-1"></i>
                                            {{ Str::limit($vaga->location ?? 'Remoto', 15) }}
                                        </span>
                                    </div>

                                    <p class="text-sm text-slate-500 line-clamp-3 mb-6 flex-1 font-medium">
                                        {{ $vaga->short_description ?? Str::limit(strip_tags($vaga->description), 120) }}
                                    </p>
                                </div>

                                <div
                                    class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between group-hover:bg-blue-50 transition-colors z-10 w-full shrink-0">
                                    <span class="font-bold text-sm text-slate-700 truncate pr-4">
                                        {{ is_numeric($vaga->salary_range) ? 'R$ ' . number_format((float) $vaga->salary_range, 2, ',', '.') : ($vaga->salary_range ?? 'A combinar') }}
                                    </span>
                                    <span
                                        class="btn-primary text-white text-[10px] px-3 py-2 rounded-lg transition-all font-black uppercase tracking-widest shadow-md shrink-0"
                                        aria-hidden="true">
                                        Ver vaga <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i>
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-16 flex justify-center unn-pagination-wrapper">
                        {{ $vagas->links('pagination::tailwind') }}
                    </div>
                @endif
            </div>
        </section>

        <style>
            /* Ajustes Finos para Paginação Tailwind Nativa ficar mais bonita/clara */
            .unn-pagination-wrapper nav div.hidden.sm\:flex-1.sm\:flex.sm\:items-center.sm\:justify-between {
                flex-direction: column;
                gap: 1rem;
                align-items: center;
            }

            .unn-pagination-wrapper nav p.text-sm.text-gray-700.leading-5 {
                color: #64748b;
                /* slate-500 */
                font-weight: 500;
            }

            .unn-pagination-wrapper nav span.relative.z-0.inline-flex.shadow-sm.rounded-md {
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
                border-radius: 9999px;
                overflow: hidden;
                border: 1px solid #e2e8f0;
                /* slate-200 */
                background: white;
            }

            .unn-pagination-wrapper nav span.relative.z-0.inline-flex.shadow-sm.rounded-md>span,
            .unn-pagination-wrapper nav span.relative.z-0.inline-flex.shadow-sm.rounded-md>a {
                padding: 0.75rem 1.25rem;
                font-weight: 700;
                font-size: 0.875rem;
                color: #475569;
                /* slate-600 */
                border: none !important;
                box-shadow: none !important;
                border-right: 1px solid #f1f5f9 !important;
                /* slate-100 */
                background-color: transparent;
                transition: all 0.2s;
            }

            .unn-pagination-wrapper nav span.relative.z-0.inline-flex.shadow-sm.rounded-md>span:last-child,
            .unn-pagination-wrapper nav span.relative.z-0.inline-flex.shadow-sm.rounded-md>a:last-child {
                border-right: none !important;
            }

            .unn-pagination-wrapper nav span.relative.z-0.inline-flex.shadow-sm.rounded-md>a:hover {
                background-color: #f8fafc;
                /* hover do botão */
                color: #2563eb;
                /* blue-600 */
            }

            /* Item Ativo nativo do tailwind pagination blade */
            .unn-pagination-wrapper nav span[aria-current="page"]>span {
                background-color: #2563eb !important;
                /* var(--unn-azul-1) / blue-600 */
                color: white !important;
                position: relative;
                z-index: 10;
            }

            /* Item desabilitado (Setas cinzas) */
            .unn-pagination-wrapper nav span[aria-disabled="true"]>span {
                color: #cbd5e1;
                /* slate-300 */
                background-color: #f8fafc;
            }
        </style>

        <!-- Call to Action Banner Limpo/Claro (Substituindo o neon) -->
        <section class="py-12 md:py-16 px-4 md:px-12 lg:px-24 bg-transparent pb-24">
            <div class="max-w-6xl mx-auto">
                <div
                    class="unn-events-cta rounded-[32px] px-6 md:px-14 py-14 md:py-16 text-center shadow-2xl relative overflow-hidden">
                    <div class="absolute inset-0 opacity-20"
                        style="background-image: radial-gradient(rgba(255,255,255,0.45) 1px, transparent 1px); background-size: 42px 42px;">
                    </div>

                    <div class="relative">
                        <span
                            class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[10px] font-black uppercase text-white tracking-[0.2em] border border-white/30 bg-white/10 backdrop-blur mb-5 shadow-sm">
                            <i class="fas fa-radar"></i> Mantenha seu Radar Ativo
                        </span>

                        <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white">
                            As melhores empresas buscam talentos diariamente.
                        </h2>
                        <p class="mt-4 text-white/80 text-lg sm:text-xl max-w-2xl mx-auto font-medium">
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

    @push('scripts')
        <script>
            // Ensure hover animations trigger correctly
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    document.querySelectorAll('.glass-card').forEach(el => {
                        el.style.opacity = '1';
                    });
                }, 500);
            });
        </script>
    @endpush
@endsection