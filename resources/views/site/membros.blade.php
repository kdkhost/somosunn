@extends('layouts.app')

@section('title', ($pageData['seo_title'] ?? null) ?: 'Membros da Comunidade - UNN')

@section('content')
    @php $pageData = $pageData ?? []; @endphp
    <div class="bg-gradient-to-br from-slate-50 via-blue-50/40 to-slate-50 min-h-screen">
        <!-- Hero Section -->
        <section class="pt-10 md:pt-16 pb-8 px-4 md:px-8">
            <div class="max-w-7xl mx-auto text-center">
                {{-- Badge Institucional da Comunidade --}}
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold text-blue-700 bg-blue-100/70 border border-blue-200/80 mb-4 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <i class="fas fa-users text-blue-600"></i>
                    <span>Rede de Empreendedores UNN</span>
                </div>

                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black leading-tight mb-3 unn-title-gradient">
                    {{ ($pageData['hero_title'] ?? null) ?: 'Membros SOMOS UNN' }}
                </h1>
                <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
                    {!! ($pageData['hero_subtitle'] ?? null) ?: 'Conecte-se com empreendedores, líderes e mentores da nossa comunidade exclusiva de networking empresarial.' !!}
                </p>
            </div>
        </section>

        @if(isset($isDemo) && $isDemo)
            <div class="max-w-7xl mx-auto px-4 mb-6">
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-3.5 flex items-center gap-3 text-sm shadow-xs">
                    <i class="fas fa-info-circle text-amber-600 text-lg"></i>
                    <p class="text-amber-900"><strong>Modo Demonstração:</strong> Perfis ilustrativos ativos. Os membros reais cadastrados na plataforma aparecerão nesta listagem.</p>
                </div>
            </div>
        @endif

        <!-- Stats Section -->
        @if($pageData['stats_enabled'] ?? true)
            <section class="pb-8 px-4 md:px-8">
                <div class="max-w-7xl mx-auto">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 sm:gap-4">
                        @php
                            $stats = [
                                [
                                    'value' => $pageData['stat_1_value'] ?? '500+',
                                    'label' => $pageData['stat_1_label'] ?? 'Empreendedores',
                                    'icon' => 'fas fa-user-tie',
                                    'color' => 'text-blue-600',
                                    'bg' => 'bg-blue-50'
                                ],
                                [
                                    'value' => $pageData['stat_2_value'] ?? '50+',
                                    'label' => $pageData['stat_2_label'] ?? 'Mentores',
                                    'icon' => 'fas fa-chalkboard-teacher',
                                    'color' => 'text-indigo-600',
                                    'bg' => 'bg-indigo-50'
                                ],
                                [
                                    'value' => $pageData['stat_3_value'] ?? '27',
                                    'label' => $pageData['stat_3_label'] ?? 'Estados',
                                    'icon' => 'fas fa-map-marked-alt',
                                    'color' => 'text-sky-600',
                                    'bg' => 'bg-sky-50'
                                ],
                                [
                                    'value' => $pageData['stat_4_value'] ?? '1.2k+',
                                    'label' => $pageData['stat_4_label'] ?? 'Conexões feitas',
                                    'icon' => 'fas fa-handshake',
                                    'color' => 'text-emerald-600',
                                    'bg' => 'bg-emerald-50'
                                ],
                            ];
                        @endphp
                        @foreach($stats as $stat)
                            <div class="bg-white rounded-2xl p-4 sm:p-5 text-center shadow-xs border border-slate-200/80 hover:border-blue-300 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                                <div class="w-10 h-10 mx-auto mb-2 rounded-xl {{ $stat['bg'] }} {{ $stat['color'] }} flex items-center justify-center text-base">
                                    <i class="{{ $stat['icon'] }}"></i>
                                </div>
                                <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $stat['value'] }}</p>
                                <p class="text-[11px] text-slate-500 font-bold uppercase tracking-wider mt-0.5">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <!-- Search and Multifaceted Filters (Classificação e Busca) -->
        {{-- relative z-50 para garantir que o menu suspenso nunca fique atrás dos cards inferiores --}}
        <section class="pb-8 px-4 md:px-8 relative z-50">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white rounded-3xl p-5 md:p-6 shadow-xl border border-slate-200/90 relative z-50">
                    <form method="GET" action="{{ route('membros') }}" id="members-filter-form" class="space-y-4">
                        @if(!empty($isFreeUserRestricted))
                            <div class="p-4 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/90 text-amber-950 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-2xs">
                                <div class="flex items-center gap-3">
                                    <span class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600 shrink-0">
                                        <i class="fas fa-crown text-lg"></i>
                                    </span>
                                    <div>
                                        <p class="font-bold text-xs uppercase tracking-wider text-amber-800">Plano Gratuito Ativo</p>
                                        <p class="text-xs text-amber-900/80">Você está visualizando membros no mesmo patamar. Faça upgrade para o <strong>Plano Pro ou Elite</strong> para liberar networking completo com todos os empresários e investidores da comunidade.</p>
                                    </div>
                                </div>
                                <a href="{{ route('premium') }}" class="btn-primary text-white text-xs px-5 py-2.5 rounded-xl font-bold uppercase tracking-wider shrink-0 shadow-sm hover:scale-105 transition-transform">
                                    Desbloquear Todos
                                </a>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                            {{-- Campo de busca por texto livre (Padrão Vagas Abertas com Botão Limpar) --}}
                            <div class="sm:col-span-2 lg:col-span-4 relative flex items-center h-12 bg-white hover:bg-slate-50/50 rounded-full px-5 border border-slate-200/90 shadow-2xs transition-all focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/20">
                                <i class="fas fa-search text-slate-400 mr-3 text-sm shrink-0"></i>
                                <input type="text" name="q" id="search-member-input" value="{{ request('q') ?: request('search') }}"
                                    placeholder="Buscar por nome, cargo, empresa ou bio..."
                                    class="w-full bg-transparent border-none outline-none text-slate-800 text-sm font-medium placeholder:text-slate-400 pr-7">
                                @if(request('q') || request('search'))
                                    <button type="button" id="clear-search-btn" title="Limpar busca"
                                        class="absolute right-4 text-slate-400 hover:text-slate-600 text-sm cursor-pointer transition-colors">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </div>

                            {{-- Custom Dropdown: Cidade / Estado (Padrão Vagas Abertas) --}}
                            <div class="lg:col-span-3 relative select-none z-[45]" id="custom-city-container">
                                <input type="hidden" name="city" id="filtro-city" value="{{ request('city') }}">
                                <button type="button" id="custom-city-trigger"
                                    class="w-full h-12 flex items-center justify-between px-5 bg-white hover:bg-slate-50 text-left rounded-full border border-slate-200/90 shadow-2xs transition-all outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer">
                                    <span class="flex items-center min-w-0 pr-2">
                                        <i class="fas fa-map-marker-alt text-slate-400 mr-2.5 text-xs shrink-0"></i>
                                        <span id="custom-city-label" class="truncate text-sm font-medium {{ request('city') ? 'text-slate-800 font-bold' : 'text-slate-500' }}">
                                            {{ request('city') ?: 'Todas as Cidades' }}
                                        </span>
                                    </span>
                                    <i id="custom-city-arrow" class="fas fa-chevron-down text-slate-400 text-xs transition-transform duration-200 shrink-0"></i>
                                </button>
                                <div id="custom-city-menu"
                                    class="hidden absolute top-full mt-2 left-0 right-0 w-full min-w-[250px] bg-white rounded-2xl border border-slate-200 py-2.5 z-[9999] overflow-hidden transform opacity-0 scale-95 transition-all duration-200 ease-out origin-top text-left shadow-[0_25px_60px_rgba(8,112,184,0.22),0_15px_30px_rgba(0,0,0,0.12)]">
                                    <div class="px-3.5 pb-2 mb-1.5 border-b border-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                        <span>Localidade</span>
                                        <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-full text-slate-500 font-bold">{{ count($availableCities ?? []) + 1 }} opções</span>
                                    </div>
                                    <div class="max-h-60 overflow-y-auto custom-select-scrollbar px-1.5 space-y-1">
                                        <div class="custom-city-opt px-3.5 py-2.5 rounded-xl text-sm font-semibold cursor-pointer transition-all flex items-center justify-between {{ !request('city') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/25' : 'text-slate-800 hover:bg-blue-50/80 hover:text-blue-700' }}"
                                            data-value="" data-label="Todas as Cidades">
                                            <span class="flex items-center gap-2">
                                                <span class="opt-dot w-2 h-2 rounded-full {{ !request('city') ? 'bg-white' : 'bg-slate-300' }}"></span>
                                                <span>Todas as Cidades</span>
                                            </span>
                                            <i class="fas fa-check text-xs check-icon {{ !request('city') ? 'text-white' : 'hidden' }}"></i>
                                        </div>
                                        @foreach($availableCities ?? [] as $cityName)
                                            @php $isCitySelected = request('city') === $cityName; @endphp
                                            <div class="custom-city-opt px-3.5 py-2.5 rounded-xl text-sm font-semibold cursor-pointer transition-all flex items-center justify-between {{ $isCitySelected ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/25' : 'text-slate-800 hover:bg-blue-50/80 hover:text-blue-700' }}"
                                                data-value="{{ $cityName }}" data-label="{{ $cityName }}">
                                                <span class="flex items-center gap-2 truncate">
                                                    <span class="opt-dot w-2 h-2 rounded-full {{ $isCitySelected ? 'bg-white' : 'bg-slate-300' }}"></span>
                                                    <span class="truncate">{{ $cityName }}</span>
                                                </span>
                                                <i class="fas fa-check text-xs check-icon {{ $isCitySelected ? 'text-white' : 'hidden' }}"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Custom Dropdown: Plano / Categoria (Padrão Vagas Abertas) --}}
                            <div class="lg:col-span-2 relative select-none z-[44]" id="custom-plan-container">
                                <input type="hidden" name="plan_id" id="filtro-plan" value="{{ request('plan_id') }}">
                                @php
                                    $selectedPlanName = 'Todos os Planos';
                                    if (request('plan_id') && isset($availablePlans)) {
                                        $foundPlan = collect($availablePlans)->firstWhere('id', request('plan_id'));
                                        if ($foundPlan) $selectedPlanName = $foundPlan->name;
                                    }
                                @endphp
                                <button type="button" id="custom-plan-trigger"
                                    class="w-full h-12 flex items-center justify-between px-5 bg-white hover:bg-slate-50 text-left rounded-full border border-slate-200/90 shadow-2xs transition-all outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer">
                                    <span class="flex items-center min-w-0 pr-2">
                                        <i class="fas fa-crown text-slate-400 mr-2.5 text-xs shrink-0"></i>
                                        <span id="custom-plan-label" class="truncate text-sm font-medium {{ request('plan_id') ? 'text-slate-800 font-bold' : 'text-slate-500' }}">
                                            {{ $selectedPlanName }}
                                        </span>
                                    </span>
                                    <i id="custom-plan-arrow" class="fas fa-chevron-down text-slate-400 text-xs transition-transform duration-200 shrink-0"></i>
                                </button>
                                <div id="custom-plan-menu"
                                    class="hidden absolute top-full mt-2 left-0 right-0 w-full min-w-[230px] bg-white rounded-2xl border border-slate-200 py-2.5 z-[9999] overflow-hidden transform opacity-0 scale-95 transition-all duration-200 ease-out origin-top text-left shadow-[0_25px_60px_rgba(8,112,184,0.22),0_15px_30px_rgba(0,0,0,0.12)]">
                                    <div class="px-3.5 pb-2 mb-1.5 border-b border-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                        <span>Planos</span>
                                        <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-full text-slate-500 font-bold">{{ count($availablePlans ?? []) + 1 }} opções</span>
                                    </div>
                                    <div class="max-h-60 overflow-y-auto custom-select-scrollbar px-1.5 space-y-1">
                                        <div class="custom-plan-opt px-3.5 py-2.5 rounded-xl text-sm font-semibold cursor-pointer transition-all flex items-center justify-between {{ !request('plan_id') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/25' : 'text-slate-800 hover:bg-blue-50/80 hover:text-blue-700' }}"
                                            data-value="" data-label="Todos os Planos">
                                            <span class="flex items-center gap-2">
                                                <span class="opt-dot w-2 h-2 rounded-full {{ !request('plan_id') ? 'bg-white' : 'bg-slate-300' }}"></span>
                                                <span>Todos os Planos</span>
                                            </span>
                                            <i class="fas fa-check text-xs check-icon {{ !request('plan_id') ? 'text-white' : 'hidden' }}"></i>
                                        </div>
                                        @foreach($availablePlans ?? [] as $p)
                                            @php $isPlanSelected = request('plan_id') == $p->id; @endphp
                                            <div class="custom-plan-opt px-3.5 py-2.5 rounded-xl text-sm font-semibold cursor-pointer transition-all flex items-center justify-between {{ $isPlanSelected ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/25' : 'text-slate-800 hover:bg-blue-50/80 hover:text-blue-700' }}"
                                                data-value="{{ $p->id }}" data-label="{{ $p->name }}">
                                                <span class="flex items-center gap-2 truncate">
                                                    <span class="opt-dot w-2 h-2 rounded-full {{ $isPlanSelected ? 'bg-white' : 'bg-slate-300' }}"></span>
                                                    <span class="truncate">{{ $p->name }}</span>
                                                </span>
                                                <i class="fas fa-check text-xs check-icon {{ $isPlanSelected ? 'text-white' : 'hidden' }}"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Custom Dropdown: Classificação / Ordenação (Padrão Vagas Abertas) --}}
                            <div class="lg:col-span-2 relative select-none z-[43]" id="custom-sort-container">
                                <input type="hidden" name="sort" id="filtro-sort" value="{{ request('sort', 'latest') }}">
                                @php
                                    $sortLabels = [
                                        'latest' => 'Mais Recentes',
                                        'connections' => 'Mais Conectados',
                                        'name' => 'Ordem Alfabética (A-Z)'
                                    ];
                                    $selectedSortLabel = $sortLabels[request('sort', 'latest')] ?? 'Mais Recentes';
                                @endphp
                                <button type="button" id="custom-sort-trigger"
                                    class="w-full h-12 flex items-center justify-between px-5 bg-white hover:bg-slate-50 text-left rounded-full border border-slate-200/90 shadow-2xs transition-all outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer">
                                    <span class="flex items-center min-w-0 pr-2">
                                        <i class="fas fa-sort-amount-down text-slate-400 mr-2.5 text-xs shrink-0"></i>
                                        <span id="custom-sort-label" class="truncate text-sm font-bold text-slate-800">
                                            {{ $selectedSortLabel }}
                                        </span>
                                    </span>
                                    <i id="custom-sort-arrow" class="fas fa-chevron-down text-slate-400 text-xs transition-transform duration-200 shrink-0"></i>
                                </button>
                                <div id="custom-sort-menu"
                                    class="hidden absolute top-full mt-2 left-0 right-0 w-full min-w-[220px] bg-white rounded-2xl border border-slate-200 py-2.5 z-[9999] overflow-hidden transform opacity-0 scale-95 transition-all duration-200 ease-out origin-top text-left shadow-[0_25px_60px_rgba(8,112,184,0.22),0_15px_30px_rgba(0,0,0,0.12)]">
                                    <div class="px-3.5 pb-2 mb-1.5 border-b border-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                        <span>Classificação</span>
                                        <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-full text-slate-500 font-bold">3 opções</span>
                                    </div>
                                    <div class="max-h-60 overflow-y-auto custom-select-scrollbar px-1.5 space-y-1">
                                        @foreach($sortLabels as $sVal => $sLabel)
                                            @php $isSortSelected = request('sort', 'latest') === $sVal; @endphp
                                            <div class="custom-sort-opt px-3.5 py-2.5 rounded-xl text-sm font-semibold cursor-pointer transition-all flex items-center justify-between {{ $isSortSelected ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/25' : 'text-slate-800 hover:bg-blue-50/80 hover:text-blue-700' }}"
                                                data-value="{{ $sVal }}" data-label="{{ $sLabel }}">
                                                <span class="flex items-center gap-2 truncate">
                                                    <span class="opt-dot w-2 h-2 rounded-full {{ $isSortSelected ? 'bg-white' : 'bg-slate-300' }}"></span>
                                                    <span class="truncate">{{ $sLabel }}</span>
                                                </span>
                                                <i class="fas fa-check text-xs check-icon {{ $isSortSelected ? 'text-white' : 'hidden' }}"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Botão Filtrar (Padrão Vagas Abertas) --}}
                            <div class="lg:col-span-1">
                                <button type="submit"
                                    class="w-full h-12 rounded-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold flex items-center justify-center gap-2 shadow-md shadow-blue-500/25 transition-all hover:scale-105 active:scale-95 cursor-pointer"
                                    title="Aplicar filtros">
                                    <i class="fas fa-filter text-xs"></i>
                                    <span class="lg:hidden text-sm">Filtrar</span>
                                </button>
                            </div>
                        </div>

                        {{-- Barra de Status / Chips de Filtros Ativos / Limpeza --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
                            <div class="flex items-center gap-2 text-slate-600 font-medium">
                                <span class="inline-flex items-center justify-center w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Total de <strong>{{ method_exists($members, 'total') ? $members->total() : $members->count() }}</strong> membros encontrados</span>
                            </div>

                            @php
                                $hasActiveFilters = request()->anyFilled(['q', 'search', 'city', 'plan_id']) || (request('sort') && request('sort') !== 'latest');
                            @endphp

                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Chips de filtros ativos com remoção individual --}}
                                @if(request('q') || request('search'))
                                    @php $qText = request('q') ?: request('search'); @endphp
                                    <a href="{{ request()->fullUrlWithQuery(['q' => null, 'search' => null]) }}"
                                        class="inline-flex items-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-medium transition-colors"
                                        title="Remover filtro de busca">
                                        <i class="fas fa-search text-[10px]"></i>
                                        <span>"{{ Str::limit($qText, 15) }}"</span>
                                        <i class="fas fa-times text-[10px] ml-0.5 opacity-70 hover:opacity-100"></i>
                                    </a>
                                @endif

                                @if(request('city'))
                                    <a href="{{ request()->fullUrlWithQuery(['city' => null]) }}"
                                        class="inline-flex items-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-medium transition-colors"
                                        title="Remover filtro de cidade">
                                        <i class="fas fa-map-marker-alt text-[10px]"></i>
                                        <span>{{ Str::limit(request('city'), 15) }}</span>
                                        <i class="fas fa-times text-[10px] ml-0.5 opacity-70 hover:opacity-100"></i>
                                    </a>
                                @endif

                                @if(request('plan_id'))
                                    <a href="{{ request()->fullUrlWithQuery(['plan_id' => null]) }}"
                                        class="inline-flex items-center gap-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 px-3 py-1 rounded-full font-medium transition-colors"
                                        title="Remover filtro de plano">
                                        <i class="fas fa-crown text-[10px]"></i>
                                        <span>{{ Str::limit($selectedPlanName, 15) }}</span>
                                        <i class="fas fa-times text-[10px] ml-0.5 opacity-70 hover:opacity-100"></i>
                                    </a>
                                @endif

                                @if($hasActiveFilters)
                                    <a href="{{ route('membros') }}"
                                        class="inline-flex items-center gap-1.5 text-slate-600 hover:text-red-600 font-semibold transition-colors bg-slate-100 hover:bg-red-50 px-3.5 py-1.5 rounded-full">
                                        <i class="fas fa-times text-[10px]"></i> Limpar tudo
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Members Grid Section (relative z-10) -->
        <section class="pb-16 px-4 md:px-8 relative z-10">
            <div class="max-w-7xl mx-auto">
                {{-- Desktop/Tablet Grid --}}
                <div class="hidden sm:grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @forelse($members as $member)
                        @include('site.partials.member-card', ['member' => $member, 'connectionMap' => $connectionMap])
                    @empty
                        <div class="col-span-full text-center py-16">
                            <div class="bg-white rounded-3xl p-10 shadow-xs max-w-md mx-auto border border-slate-200/90 text-center">
                                <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4 text-2xl">
                                    <i class="fas fa-users-slash"></i>
                                </div>
                                <h3 class="text-lg font-black text-slate-900 mb-1">Nenhum membro encontrado</h3>
                                <p class="text-sm text-slate-500 mb-6">Tente ajustar seus termos de busca ou remover os filtros aplicados.</p>
                                <div class="flex items-center justify-center gap-3">
                                    @if($hasActiveFilters)
                                        <a href="{{ route('membros') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2.5 rounded-full font-bold text-xs inline-flex items-center gap-2 transition-colors">
                                            <i class="fas fa-times"></i> Limpar filtros
                                        </a>
                                    @endif
                                    <a href="{{ route('register') }}" class="btn-primary text-white px-5 py-2.5 rounded-full font-bold text-xs inline-flex items-center gap-2 shadow-xs hover:scale-105 transition-transform">
                                        <i class="fas fa-user-plus"></i> Fazer parte
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Mobile Swiper Carousel --}}
                <div class="sm:hidden">
                    <div class="swiper members-swiper">
                        <div class="swiper-wrapper">
                            @foreach($members as $member)
                                <div class="swiper-slide">
                                    @include('site.partials.member-card', ['member' => $member, 'connectionMap' => $connectionMap])
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination mt-4"></div>
                    </div>
                </div>

                {{-- Paginação Estilizada Padrão UNN --}}
                @if(method_exists($members, 'hasPages') && $members->hasPages())
                    <div class="mt-12 flex justify-center unn-pagination-wrapper">
                        {{ $members->links('pagination::tailwind') }}
                    </div>
                @endif
            </div>
        </section>

        <!-- CTA Section -->
        @if($pageData['cta_enabled'] ?? true)
            <section class="py-14 px-4 md:px-8" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
                <div class="max-w-3xl mx-auto text-center text-white">
                    <h2 class="text-2xl lg:text-3xl font-black mb-3">{{ $pageData['cta_title'] ?? 'Faça parte desta comunidade' }}</h2>
                    <p class="text-base text-white/90 mb-6 max-w-xl mx-auto leading-relaxed">{!! $pageData['cta_subtitle'] ?? 'Conecte-se com empreendedores de sucesso, compartilhe oportunidades e expanda sua rede de negócios com a SOMOS UNN.' !!}</p>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white px-7 py-3.5 rounded-full font-black text-sm hover:bg-blue-50 transition-all shadow-lg hover:scale-105" style="color: var(--unn-azul-1)">
                        <i class="fas fa-rocket"></i> {{ $pageData['cta_btn'] ?? 'Quero fazer parte' }}
                    </a>
                </div>
            </section>
        @endif
    </div>

    <style>
        .unn-title-gradient {
            background: linear-gradient(90deg, #1F5EDB 0%, #177FD6 60%, #1D3FC4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Custom Dropdown Scrollbar */
        .custom-select-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-select-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 8px;
        }
        .custom-select-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
        }
        .custom-select-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Swiper Mobile
                new Swiper('.members-swiper', {
                    slidesPerView: 1.15,
                    spaceBetween: 16,
                    centeredSlides: true,
                    autoplay: { delay: 4500, disableOnInteraction: false },
                    pagination: { el: '.swiper-pagination', clickable: true },
                    grabCursor: true,
                    loop: {{ $members->count() > 2 ? 'true' : 'false' }},
                    breakpoints: {
                        480: { slidesPerView: 1.3, centeredSlides: false },
                    }
                });

                // Botão de limpar texto de busca
                const clearSearchBtn = document.getElementById('clear-search-btn');
                const searchInput = document.getElementById('search-member-input');
                if (clearSearchBtn && searchInput) {
                    clearSearchBtn.addEventListener('click', function() {
                        searchInput.value = '';
                        document.getElementById('members-filter-form').submit();
                    });
                }
            });

            function requestConnection(userId, btn) {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;
                fetch('/connect/' + userId, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(data.message);
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    }
                })
                .catch(() => {
                    toastr.error('Erro ao processar conexão.');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                });
            }

            function acceptConnection(userId) {
                fetch('/connection/accept/' + userId, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        location.reload();
                    } else {
                        toastr.error(data.message);
                    }
                })
                .catch(() => {
                    toastr.error('Erro ao aceitar conexão.');
                });
            }

            function blockUser(userId) {
                Swal.fire({
                    title: 'Bloquear membro?',
                    text: 'Este membro não poderá mais visualizar suas publicações ou enviar mensagens.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sim, bloquear',
                    cancelButtonText: 'Cancelar'
                }).then(r => {
                    if (r.isConfirmed) {
                        fetch('/connection/block/' + userId, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                toastr.success(data.message);
                                location.reload();
                            } else {
                                toastr.error(data.message);
                            }
                        })
                        .catch(() => {
                            toastr.error('Erro ao bloquear membro.');
                        });
                    }
                });
            }

            function openChat(userId) {
                window.location.href = '{{ url("/chat/start") }}/' + userId;
            }

            // --- Custom Dropdown Engine Padronizado Estilo Vagas com Prevenção de Sobreposição ---
            function initCustomDropdown(containerId, triggerId, menuId, arrowId, labelId, inputId, optClass) {
                const container = document.getElementById(containerId);
                const trigger = document.getElementById(triggerId);
                const menu = document.getElementById(menuId);
                const arrow = document.getElementById(arrowId);
                const label = document.getElementById(labelId);
                const input = document.getElementById(inputId);
                const options = document.querySelectorAll('.' + optClass);

                if (!trigger || !menu || !container) return;
                let isOpen = false;

                const openMenu = () => {
                    // Fechar qualquer outro dropdown aberto antes
                    document.querySelectorAll('[id$="-menu"]').forEach(m => {
                        if (m !== menu && !m.classList.contains('hidden')) {
                            m.classList.add('hidden', 'opacity-0', 'scale-95');
                            m.classList.remove('opacity-100', 'scale-100');
                            const parentCont = m.closest('[id$="-container"]');
                            if (parentCont) parentCont.style.zIndex = '';
                        }
                    });

                    // Elevar o z-index deste container ativo sobre tudo
                    container.style.zIndex = '100';

                    // Checagem inteligente de viewport para abrir para cima se o espaço abaixo for insuficiente
                    const triggerRect = trigger.getBoundingClientRect();
                    const spaceBelow = window.innerHeight - triggerRect.bottom;
                    if (spaceBelow < 280 && triggerRect.top > 280) {
                        menu.classList.remove('top-full', 'mt-2', 'origin-top');
                        menu.classList.add('bottom-full', 'mb-2', 'origin-bottom');
                    } else {
                        menu.classList.remove('bottom-full', 'mb-2', 'origin-bottom');
                        menu.classList.add('top-full', 'mt-2', 'origin-top');
                    }

                    isOpen = true;
                    menu.classList.remove('hidden');
                    requestAnimationFrame(() => {
                        menu.classList.remove('opacity-0', 'scale-95');
                        menu.classList.add('opacity-100', 'scale-100');
                    });
                    if (arrow) arrow.classList.add('rotate-180');
                    trigger.setAttribute('aria-expanded', 'true');
                };

                const closeMenu = () => {
                    if (!isOpen) return;
                    isOpen = false;
                    menu.classList.remove('opacity-100', 'scale-100');
                    menu.classList.add('opacity-0', 'scale-95');
                    if (arrow) arrow.classList.remove('rotate-180');
                    trigger.setAttribute('aria-expanded', 'false');
                    container.style.zIndex = '';
                    setTimeout(() => {
                        if (!isOpen) menu.classList.add('hidden');
                    }, 180);
                };

                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    isOpen ? closeMenu() : openMenu();
                });

                options.forEach(opt => {
                    opt.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const val = opt.getAttribute('data-value');
                        const text = opt.getAttribute('data-label');

                        if (input) input.value = val;
                        if (label) {
                            label.textContent = text;
                            if (val) {
                                label.classList.remove('text-slate-500');
                                label.classList.add('text-slate-800', 'font-bold');
                            } else {
                                label.classList.remove('text-slate-800', 'font-bold');
                                label.classList.add('text-slate-500');
                            }
                        }

                        options.forEach(o => {
                            const isCur = o === opt;
                            o.classList.toggle('bg-gradient-to-r', isCur);
                            o.classList.toggle('from-blue-600', isCur);
                            o.classList.toggle('to-blue-700', isCur);
                            o.classList.toggle('text-white', isCur);
                            o.classList.toggle('shadow-md', isCur);
                            o.classList.toggle('shadow-blue-500/25', isCur);
                            o.classList.toggle('text-slate-800', !isCur);
                            o.classList.toggle('hover:bg-blue-50/80', !isCur);
                            o.classList.toggle('hover:text-blue-700', !isCur);

                            const dot = o.querySelector('.opt-dot');
                            if (dot) {
                                dot.classList.toggle('bg-white', isCur);
                                dot.classList.toggle('bg-slate-300', !isCur);
                            }

                            const chk = o.querySelector('.check-icon');
                            if (chk) {
                                chk.classList.toggle('hidden', !isCur);
                                chk.classList.toggle('text-white', isCur);
                            }
                        });

                        closeMenu();

                        // Submeter formulário automaticamente ao selecionar uma opção para agilidade
                        document.getElementById('members-filter-form').submit();
                    });
                });

                document.addEventListener('click', (e) => {
                    if (!e.target.closest('#' + containerId)) {
                        closeMenu();
                    }
                });
            }

            initCustomDropdown('custom-city-container', 'custom-city-trigger', 'custom-city-menu', 'custom-city-arrow', 'custom-city-label', 'filtro-city', 'custom-city-opt');
            initCustomDropdown('custom-plan-container', 'custom-plan-trigger', 'custom-plan-menu', 'custom-plan-arrow', 'custom-plan-label', 'filtro-plan', 'custom-plan-opt');
            initCustomDropdown('custom-sort-container', 'custom-sort-trigger', 'custom-sort-menu', 'custom-sort-arrow', 'custom-sort-label', 'filtro-sort', 'custom-sort-opt');
        </script>
    @endpush
@endsection
