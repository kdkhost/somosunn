@extends('layouts.app')

@section('title', 'Ranking da Comunidade - UNN')

@section('content')
    <div class="bg-gradient-to-br from-slate-50 via-blue-50/30 to-slate-50 min-h-screen pb-20">
        <!-- Hero Section -->
        <section class="pt-12 md:pt-20 pb-8 px-4 md:px-8">
            <div class="max-w-7xl mx-auto text-center">
                {{-- Badge Institucional --}}
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold text-amber-800 bg-amber-100/80 border border-amber-200/90 mb-4 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <i class="fas fa-trophy text-amber-600"></i>
                    <span>Gamificação & Reconhecimento UNN</span>
                </div>

                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-3.5 unn-title-gradient">
                    Ranking da Comunidade
                </h1>
                <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto font-medium leading-relaxed">
                    Conheça os empreendedores mais engajados da nossa rede. Membros que geram conexões reais, compartilham valor e aceleram resultados no ecossistema UNN.
                </p>

                @if(isset($isDemo) && $isDemo)
                    <div class="mt-4 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold">
                        <i class="fas fa-info-circle text-amber-600"></i>
                        <span>Modo Demonstrativo: Perfis e pontuações ilustrativas</span>
                    </div>
                @endif

                {{-- Pilares de Pontuação / Como Funciona --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 max-w-4xl mx-auto mt-8 text-left">
                    <div class="bg-white/80 backdrop-blur-sm p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-user-plus text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-800">+50 PTS</p>
                            <p class="text-[11px] text-slate-500 truncate">Nova conexão aceita</p>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-sm p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-id-badge text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-800">+100 PTS</p>
                            <p class="text-[11px] text-slate-500 truncate">Perfil 100% completo</p>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-sm p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-comment-dots text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-800">+20 PTS</p>
                            <p class="text-[11px] text-slate-500 truncate">Publicação e interação</p>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-sm p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-calendar-check text-sm"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-800">+150 PTS</p>
                            <p class="text-[11px] text-slate-500 truncate">Participação em eventos</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Ranking Pódio (Top 3) -->
        @if($podium->isNotEmpty())
            <section class="pt-6 pb-12 px-4 md:px-8">
                <div class="max-w-5xl mx-auto">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                        @php
                            $first = $podium->get(0);
                            $second = $podium->get(1);
                            $third = $podium->get(2);
                        @endphp

                        {{-- Segundo Lugar (Prata) - Esquerda no desktop --}}
                        @if($second)
                            @php
                                $secondLink = $second->is_demo ? '#' : route('social.profile', $second->user_id);
                            @endphp
                            <div class="order-2 md:order-1">
                                <a href="{{ $secondLink }}" class="group block bg-white rounded-3xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 border border-slate-200/90 text-center relative">
                                    {{-- Badge Prata --}}
                                    <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-[11px] font-black uppercase tracking-wider mb-4 shadow-2xs">
                                        <i class="fas fa-medal text-slate-400"></i> 2º Lugar • Prata
                                    </div>

                                    {{-- Avatar com Anel Prateado --}}
                                    <div class="flex justify-center mb-4">
                                        @if($second->avatar)
                                            <img src="{{ $second->avatar }}" alt="{{ $second->name }}"
                                                class="w-20 h-20 rounded-full ring-4 ring-slate-200 object-cover shadow-md group-hover:scale-105 transition-transform"
                                                style="width: 80px; height: 80px; min-width: 80px; min-height: 80px; object-fit: cover;"
                                                onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';" loading="lazy">
                                        @else
                                            <div class="w-20 h-20 rounded-full ring-4 ring-slate-200 bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center text-white text-xl font-black shadow-md group-hover:scale-105 transition-transform"
                                                style="width: 80px; height: 80px; min-width: 80px; min-height: 80px;">
                                                {{ $second->initials }}
                                            </div>
                                        @endif
                                    </div>

                                    <h3 class="font-black text-lg text-slate-900 group-hover:text-blue-600 transition-colors truncate" title="{{ $second->name }}">
                                        {{ $second->name }}
                                    </h3>
                                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-0.5 truncate">{{ $second->occupation ?: ($second->level ?? 'Membro UNN') }}</p>

                                    @if($second->plan_badge)
                                        <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700">
                                            {{ $second->plan_badge }}
                                        </span>
                                    @endif

                                    <div class="mt-4 pt-4 border-t border-slate-100">
                                        <p class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($second->score, 0, ',', '.') }}</p>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-0.5">Pontos</p>
                                    </div>

                                    <div class="mt-4 flex items-center justify-center gap-1.5 text-xs text-slate-500">
                                        <i class="fas fa-handshake text-blue-500"></i>
                                        <span class="font-bold">{{ $second->interactions_count }}</span> conexões
                                    </div>
                                </a>
                            </div>
                        @endif

                        {{-- Primeiro Lugar (Ouro - Campeão) - Centro e elevado --}}
                        @if($first)
                            @php
                                $firstLink = $first->is_demo ? '#' : route('social.profile', $first->user_id);
                            @endphp
                            <div class="order-1 md:order-2 md:-mb-3">
                                <div class="relative group">
                                    {{-- Brilho dourado ao redor --}}
                                    <div class="absolute -inset-1.5 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 rounded-[2rem] blur-md opacity-35 group-hover:opacity-75 transition duration-500"></div>

                                    <a href="{{ $firstLink }}" class="relative block bg-white rounded-3xl p-7 sm:p-8 shadow-xl hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-300 border-2 border-amber-300/90 text-center">
                                        {{-- Badge Ouro Flutuante --}}
                                        <div class="inline-flex items-center gap-2 px-5 py-1.5 rounded-full bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 text-slate-950 text-xs font-black uppercase tracking-wider mb-4 shadow-md shadow-amber-400/25">
                                            <i class="fas fa-crown text-xs"></i> 1º Lugar • Campeão
                                        </div>

                                        {{-- Avatar Ouro com Coroa --}}
                                        <div class="relative flex justify-center mb-5 pt-2">
                                            <div class="absolute -top-4 text-amber-500 text-3xl filter drop-shadow-md z-10 animate-bounce">
                                                <i class="fas fa-crown"></i>
                                            </div>
                                            @if($first->avatar)
                                                <img src="{{ $first->avatar }}" alt="{{ $first->name }}"
                                                    class="w-24 h-24 sm:w-28 sm:h-28 rounded-full ring-4 ring-amber-400 object-cover shadow-xl group-hover:scale-105 transition-transform"
                                                    style="width: 100px; height: 100px; min-width: 100px; min-height: 100px; object-fit: cover;"
                                                    onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';" loading="lazy">
                                            @else
                                                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full ring-4 ring-amber-400 bg-gradient-to-br from-amber-500 to-yellow-600 flex items-center justify-center text-white text-3xl font-black shadow-xl group-hover:scale-105 transition-transform"
                                                    style="width: 100px; height: 100px; min-width: 100px; min-height: 100px;">
                                                    {{ $first->initials }}
                                                </div>
                                            @endif
                                        </div>

                                        <h3 class="font-black text-xl sm:text-2xl text-slate-900 group-hover:text-amber-600 transition-colors truncate" title="{{ $first->name }}">
                                            {{ $first->name }}
                                        </h3>
                                        <p class="text-xs font-bold text-amber-700 uppercase tracking-wider mt-1 truncate">{{ $first->occupation ?: ($first->level ?? 'Elite UNN') }}</p>

                                        @if($first->company)
                                            <p class="text-xs text-slate-500 mt-0.5 truncate flex items-center justify-center gap-1">
                                                <i class="fas fa-building text-[10px] text-slate-400"></i> {{ $first->company }}
                                            </p>
                                        @endif

                                        <div class="mt-5 pt-5 border-t border-amber-100">
                                            <p class="text-4xl sm:text-5xl font-black bg-gradient-to-r from-amber-600 via-yellow-600 to-amber-700 bg-clip-text text-transparent tracking-tight">
                                                {{ number_format($first->score, 0, ',', '.') }}
                                            </p>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-1">Pontos Acumulados</p>
                                        </div>

                                        <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-800 text-xs font-bold border border-amber-200">
                                                <i class="fas fa-handshake text-amber-600"></i> {{ $first->interactions_count }} conexões
                                            </span>
                                            @if($first->plan_badge)
                                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-slate-900 text-amber-400 text-xs font-black uppercase tracking-wider">
                                                    <i class="fas fa-gem text-[10px]"></i> {{ $first->plan_badge }}
                                                </span>
                                            @endif
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- Terceiro Lugar (Bronze) - Direita no desktop --}}
                        @if($third)
                            @php
                                $thirdLink = $third->is_demo ? '#' : route('social.profile', $third->user_id);
                            @endphp
                            <div class="order-3">
                                <a href="{{ $thirdLink }}" class="group block bg-white rounded-3xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 border border-slate-200/90 text-center relative">
                                    {{-- Badge Bronze --}}
                                    <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-amber-50 text-amber-900 border border-amber-200 text-[11px] font-black uppercase tracking-wider mb-4 shadow-2xs">
                                        <i class="fas fa-medal text-amber-700"></i> 3º Lugar • Bronze
                                    </div>

                                    {{-- Avatar com Anel Bronze --}}
                                    <div class="flex justify-center mb-4">
                                        @if($third->avatar)
                                            <img src="{{ $third->avatar }}" alt="{{ $third->name }}"
                                                class="w-20 h-20 rounded-full ring-4 ring-amber-200 object-cover shadow-md group-hover:scale-105 transition-transform"
                                                style="width: 80px; height: 80px; min-width: 80px; min-height: 80px; object-fit: cover;"
                                                onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';" loading="lazy">
                                        @else
                                            <div class="w-20 h-20 rounded-full ring-4 ring-amber-200 bg-gradient-to-br from-amber-700 to-amber-900 flex items-center justify-center text-white text-xl font-black shadow-md group-hover:scale-105 transition-transform"
                                                style="width: 80px; height: 80px; min-width: 80px; min-height: 80px;">
                                                {{ $third->initials }}
                                            </div>
                                        @endif
                                    </div>

                                    <h3 class="font-black text-lg text-slate-900 group-hover:text-blue-600 transition-colors truncate" title="{{ $third->name }}">
                                        {{ $third->name }}
                                    </h3>
                                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-0.5 truncate">{{ $third->occupation ?: ($third->level ?? 'Membro UNN') }}</p>

                                    @if($third->plan_badge)
                                        <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700">
                                            {{ $third->plan_badge }}
                                        </span>
                                    @endif

                                    <div class="mt-4 pt-4 border-t border-slate-100">
                                        <p class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($third->score, 0, ',', '.') }}</p>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-0.5">Pontos</p>
                                    </div>

                                    <div class="mt-4 flex items-center justify-center gap-1.5 text-xs text-slate-500">
                                        <i class="fas fa-handshake text-blue-500"></i>
                                        <span class="font-bold">{{ $third->interactions_count }}</span> conexões
                                    </div>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @else
            <section class="py-16 px-4 text-center">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4 text-2xl shadow-xs">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3 class="text-lg font-black text-slate-800">Nenhum membro ranqueado no momento</h3>
                <p class="text-sm text-slate-500 max-w-sm mx-auto mt-1">Conecte-se e interaja na plataforma para inaugurar o quadro de líderes!</p>
            </section>
        @endif

        <!-- Classificação Geral (4º lugar em diante) com Filtro Dinâmico -->
        @if($remaining->isNotEmpty())
            <section class="pb-16 px-4 md:px-8">
                <div class="max-w-5xl mx-auto">
                    {{-- Header da Tabela com Busca em Tempo Real --}}
                    <div class="bg-white rounded-3xl p-5 md:p-6 shadow-md border border-slate-200/90 mb-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-900 flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                                    <i class="fas fa-list-ol"></i>
                                </span>
                                <span>Classificação Geral</span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Exibindo <strong>{{ $totalRanked }}</strong> empreendedores ranqueados na comunidade</p>
                        </div>

                        {{-- Campo de Busca Instantânea no Ranking --}}
                        <div class="w-full sm:w-72 relative flex items-center h-11 bg-slate-50 hover:bg-slate-100/70 focus-within:bg-white rounded-full px-4 border border-slate-200 transition-all focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/20">
                            <i class="fas fa-search text-slate-400 text-xs mr-2.5 shrink-0"></i>
                            <input type="text" id="ranking-search-input" placeholder="Buscar no ranking..."
                                class="w-full bg-transparent border-none outline-none text-slate-800 text-xs font-medium placeholder:text-slate-400">
                            <button type="button" id="ranking-search-clear" class="hidden text-slate-400 hover:text-slate-600 text-xs cursor-pointer ml-1">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Lista Estilizada de Membros Ranqueados --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/90 divide-y divide-slate-100 overflow-hidden" id="ranking-list-container">
                        @foreach($remaining as $rank)
                            @php
                                $position = $loop->index + 4;
                                $memberLink = $rank->is_demo ? '#' : route('social.profile', $rank->user_id);
                            @endphp
                            <div class="ranking-item flex items-center p-4 md:p-5 hover:bg-slate-50/80 transition-colors group"
                                data-name="{{ mb_strtolower($rank->name) }}"
                                data-occupation="{{ mb_strtolower($rank->occupation ?? '') }}"
                                data-company="{{ mb_strtolower($rank->company ?? '') }}">

                                {{-- Posição --}}
                                <div class="w-10 sm:w-14 flex-shrink-0 text-center">
                                    <span class="text-sm sm:text-base font-black text-slate-400 group-hover:text-blue-600 transition-colors">
                                        #{{ str_pad($position, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>

                                {{-- Avatar com Dimensões Estritas --}}
                                <div class="relative flex-shrink-0 mr-3.5 sm:mr-4">
                                    @if($rank->avatar)
                                        <img src="{{ $rank->avatar }}" alt="{{ $rank->name }}"
                                            class="w-12 h-12 rounded-full object-cover ring-2 ring-slate-100 shadow-xs shrink-0"
                                            style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; object-fit: cover;"
                                            onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';" loading="lazy">
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-bold text-sm shadow-xs shrink-0"
                                            style="width: 48px; height: 48px; min-width: 48px; min-height: 48px;">
                                            {{ $rank->initials }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Informações do Membro --}}
                                <div class="flex-1 min-w-0 pr-3 sm:pr-4">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <a href="{{ $memberLink }}" class="font-black text-slate-900 text-sm sm:text-base truncate group-hover:text-blue-600 transition-colors hover:underline" title="{{ $rank->name }}">
                                            {{ $rank->name }}
                                        </a>
                                        @if($rank->plan_badge)
                                            <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full {{ str_contains(strtoupper($rank->plan_badge), 'ELITE') ? 'bg-amber-100 text-amber-900 border border-amber-200' : (str_contains(strtoupper($rank->plan_badge), 'PRO') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600') }}">
                                                {{ $rank->plan_badge }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 mt-0.5 text-xs text-slate-500">
                                        @if(!empty($rank->occupation))
                                            <span class="truncate max-w-[150px] font-medium text-slate-600">{{ $rank->occupation }}</span>
                                        @endif
                                        @if(!empty($rank->company))
                                            <span class="hidden sm:inline-flex items-center gap-1 text-slate-400 text-[11px]">
                                                <i class="fas fa-building text-[9px]"></i> {{ $rank->company }}
                                            </span>
                                        @endif
                                        @if($rank->interactions_count > 0)
                                            <span class="hidden sm:inline-flex items-center gap-1 text-blue-600 font-semibold text-[11px]">
                                                <i class="fas fa-handshake text-[9px]"></i> {{ $rank->interactions_count }} conexões
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Pontuação em Destaque --}}
                                <div class="text-right flex-shrink-0 mr-2 sm:mr-4">
                                    <p class="text-lg sm:text-xl font-black text-slate-900 group-hover:text-blue-600 transition-colors tracking-tight">
                                        {{ number_format($rank->score, 0, ',', '.') }}
                                    </p>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">PTS</p>
                                </div>

                                {{-- Ação Rápida --}}
                                <div class="flex-shrink-0">
                                    <a href="{{ $memberLink }}"
                                        class="w-9 h-9 rounded-full bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-400 flex items-center justify-center text-xs transition-all duration-200 shadow-2xs hover:scale-105"
                                        title="Ver perfil de {{ $rank->name }}">
                                        <i class="fas fa-chevron-right text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Mensagem quando a busca não encontrar ninguém --}}
                    <div id="ranking-no-results" class="hidden bg-white rounded-3xl p-10 text-center border border-slate-200/90 shadow-sm mt-2">
                        <i class="fas fa-search text-3xl text-slate-300 mb-2"></i>
                        <p class="font-black text-slate-800 text-sm">Nenhum membro encontrado no ranking</p>
                        <p class="text-xs text-slate-400 mt-0.5">Tente buscar por outro termo ou nome.</p>
                    </div>
                </div>
            </section>
        @endif

        <!-- CTA de Subida de Ranking -->
        <section class="px-4 md:px-8">
            <div class="max-w-5xl mx-auto rounded-3xl p-8 md:p-12 text-center text-white relative overflow-hidden shadow-xl"
                style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative z-10 max-w-2xl mx-auto">
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white/15 border border-white/20 text-xs font-bold text-white mb-3">
                        <i class="fas fa-rocket"></i> Acelere seu Posicionamento
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-black mb-3">Quer alcançar o topo do Ranking UNN?</h3>
                    <p class="text-sm sm:text-base text-white/90 mb-6 leading-relaxed">
                        Complete as informações do seu perfil, interaja ativamente na comunidade e envie conexões para outros empresários. Cada ação impulsiona seus pontos!
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('panel.profile.edit') }}"
                            class="bg-white px-6 py-3 rounded-full font-black text-xs hover:bg-blue-50 transition-all shadow-md hover:scale-105" style="color: var(--unn-azul-1)">
                            <i class="fas fa-user-edit mr-1"></i> Completar Perfil
                        </a>
                        <a href="{{ route('membros') }}"
                            class="bg-white/15 hover:bg-white/25 border border-white/30 text-white px-6 py-3 rounded-full font-black text-xs transition-all hover:scale-105">
                            <i class="fas fa-users mr-1"></i> Explorar Membros
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        .unn-title-gradient {
            background: linear-gradient(90deg, #1F5EDB 0%, #177FD6 60%, #1D3FC4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('ranking-search-input');
                const clearBtn = document.getElementById('ranking-search-clear');
                const items = document.querySelectorAll('.ranking-item');
                const noResults = document.getElementById('ranking-no-results');

                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const term = this.value.trim().toLowerCase();

                        if (clearBtn) {
                            clearBtn.classList.toggle('hidden', term === '');
                        }

                        let visibleCount = 0;
                        items.forEach(item => {
                            const name = item.getAttribute('data-name') || '';
                            const occ = item.getAttribute('data-occupation') || '';
                            const comp = item.getAttribute('data-company') || '';

                            const matches = name.includes(term) || occ.includes(term) || comp.includes(term);
                            item.style.display = matches ? '' : 'none';
                            if (matches) visibleCount++;
                        });

                        if (noResults) {
                            noResults.classList.toggle('hidden', visibleCount > 0);
                        }
                    });

                    if (clearBtn) {
                        clearBtn.addEventListener('click', function() {
                            searchInput.value = '';
                            searchInput.dispatchEvent(new Event('input'));
                            searchInput.focus();
                        });
                    }
                }
            });
        </script>
    @endpush
@endsection
