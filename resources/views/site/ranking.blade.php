@extends('layouts.app')

@section('title', 'Ranking da Comunidade - SOMOS UNN')

@section('content')
    <style>
        @keyframes spinSlow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.6; transform: scale(1); }
            50% { opacity: 0.95; transform: scale(1.05); }
        }
        .animate-spin-slow {
            animation: spinSlow 8s linear infinite;
        }
        .animate-pulse-glow {
            animation: pulseGlow 2.8s ease-in-out infinite;
        }
    </style>

    <div class="bg-gradient-to-br from-slate-50 via-blue-50/25 to-slate-50 min-h-screen pb-20">
        <!-- Hero Section -->
        <section class="pt-12 md:pt-16 pb-6 px-4 md:px-8">
            <div class="max-w-6xl mx-auto text-center">
                {{-- Badge Institucional --}}
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold text-amber-800 bg-amber-100/80 border border-amber-200 mb-3 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <i class="fas fa-trophy text-amber-600"></i>
                    <span>Gamificação & Reconhecimento UNN</span>
                </div>

                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black leading-tight mb-3 unn-title-gradient">
                    Ranking da Comunidade
                </h1>
                <p class="text-sm sm:text-base text-slate-600 max-w-2xl mx-auto font-medium leading-relaxed">
                    Conheça os empreendedores mais engajados da nossa rede. Membros que geram conexões reais, compartilham valor e aceleram resultados no ecossistema UNN.
                </p>

                {{-- Pilares de Pontuação Rápidos --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-3xl mx-auto mt-6 text-left">
                    <div class="bg-white/90 p-3 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 text-xs">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-900">+50 PTS</p>
                            <p class="text-[10px] text-slate-500 truncate">Nova conexão</p>
                        </div>
                    </div>

                    <div class="bg-white/90 p-3 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 text-xs">
                            <i class="fas fa-id-badge"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-900">+100 PTS</p>
                            <p class="text-[10px] text-slate-500 truncate">Perfil completo</p>
                        </div>
                    </div>

                    <div class="bg-white/90 p-3 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 text-xs">
                            <i class="fas fa-comment-dots"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-900">+20 PTS</p>
                            <p class="text-[10px] text-slate-500 truncate">Interação no feed</p>
                        </div>
                    </div>

                    <div class="bg-white/90 p-3 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 text-xs">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-900">+20 PTS</p>
                            <p class="text-[10px] text-slate-500 truncate">Consistência semanal</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="#regras-pontuacao" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 transition">
                        <i class="fas fa-info-circle"></i>
                        <span>Ver regras completas de pontuação e decaimento por inatividade</span>
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Ranking Pódio (Top 3) -->
        @if($podium->isNotEmpty())
            <section class="pt-4 pb-12 px-4 md:px-8">
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
                                    <div class="relative flex justify-center mb-4">
                                        <div class="relative w-22 h-22 flex items-center justify-center">
                                            <div class="absolute -inset-1 rounded-full bg-gradient-to-tr from-slate-300 via-slate-100 to-slate-400 opacity-80"></div>
                                            <div class="absolute inset-0.5 rounded-full bg-white"></div>
                                            @if($second->avatar)
                                                <img src="{{ $second->avatar }}" alt="{{ $second->name }}"
                                                    class="relative z-10 w-20 h-20 rounded-full object-cover shadow-md group-hover:scale-105 transition-transform"
                                                    style="width: 80px; height: 80px; min-width: 80px; min-height: 80px; object-fit: cover;"
                                                    onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';" loading="lazy">
                                            @else
                                                <div class="relative z-10 w-20 h-20 rounded-full bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center text-white text-xl font-black shadow-md group-hover:scale-105 transition-transform"
                                                    style="width: 80px; height: 80px; min-width: 80px; min-height: 80px;">
                                                    {{ $second->initials }}
                                                </div>
                                            @endif
                                        </div>
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

                                    <div class="mt-3 flex items-center justify-center gap-1.5 text-xs text-slate-500">
                                        <i class="fas fa-handshake text-blue-500"></i>
                                        <span class="font-bold">{{ $second->interactions_count }}</span> conexões
                                    </div>
                                </a>
                            </div>
                        @endif

                        {{-- Primeiro Lugar (Ouro - Campeão) - Centro e elevado com animação giratória --}}
                        @if($first)
                            @php
                                $firstLink = $first->is_demo ? '#' : route('social.profile', $first->user_id);
                            @endphp
                            <div class="order-1 md:order-2 md:-mb-3">
                                <div class="relative group">
                                    {{-- Brilho dourado ao redor do card --}}
                                    <div class="absolute -inset-1.5 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 rounded-[2rem] blur-md opacity-40 group-hover:opacity-75 transition duration-500"></div>

                                    <a href="{{ $firstLink }}" class="relative block bg-white rounded-3xl p-7 sm:p-8 shadow-xl hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-300 border-2 border-amber-300/90 text-center">
                                        {{-- Badge Ouro Flutuante --}}
                                        <div class="inline-flex items-center gap-2 px-5 py-1.5 rounded-full bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 text-slate-950 text-xs font-black uppercase tracking-wider mb-4 shadow-md shadow-amber-400/25">
                                            <i class="fas fa-crown text-xs"></i> 1º Lugar • Campeão
                                        </div>

                                        {{-- Avatar Ouro com Coroa e Anel Giratório Animado --}}
                                        <div class="relative flex justify-center mb-5 pt-3">
                                            {{-- Coroa animada --}}
                                            <div class="absolute -top-5 text-amber-500 text-3xl filter drop-shadow-md z-30 animate-bounce">
                                                <i class="fas fa-crown"></i>
                                            </div>

                                            {{-- Anel Giratório Contínuo Dourado --}}
                                            <div class="relative w-28 h-28 sm:w-32 sm:h-32 flex items-center justify-center">
                                                {{-- Glow pulsante dourado --}}
                                                <div class="absolute -inset-2 rounded-full bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 opacity-70 blur-md animate-pulse-glow"></div>
                                                {{-- Borda giratória contínua em volta do avatar --}}
                                                <div class="absolute -inset-1 rounded-full bg-gradient-to-tr from-amber-500 via-yellow-200 to-amber-600 animate-spin-slow"></div>
                                                {{-- Borda interna branca para contraste --}}
                                                <div class="absolute inset-0.5 rounded-full bg-white"></div>

                                                {{-- Avatar do Campeão --}}
                                                @if($first->avatar)
                                                    <img src="{{ $first->avatar }}" alt="{{ $first->name }}"
                                                        class="relative z-10 w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover shadow-xl group-hover:scale-105 transition-transform"
                                                        style="width: 104px; height: 104px; min-width: 104px; min-height: 104px; object-fit: cover;"
                                                        onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';" loading="lazy">
                                                @else
                                                    <div class="relative z-10 w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-gradient-to-br from-amber-500 to-yellow-600 flex items-center justify-center text-white text-3xl font-black shadow-xl group-hover:scale-105 transition-transform"
                                                        style="width: 104px; height: 104px; min-width: 104px; min-height: 104px;">
                                                        {{ $first->initials }}
                                                    </div>
                                                @endif
                                            </div>
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

                                        {{-- Pontuação em Alto Contraste (100% legível) --}}
                                        <div class="mt-5 pt-5 border-t border-amber-100">
                                            <p class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight">
                                                {{ number_format($first->score, 0, ',', '.') }}
                                            </p>
                                            <p class="text-[11px] font-black uppercase tracking-widest text-amber-600 mt-1">Pontos Acumulados</p>
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
                                    <div class="relative flex justify-center mb-4">
                                        <div class="relative w-22 h-22 flex items-center justify-center">
                                            <div class="absolute -inset-1 rounded-full bg-gradient-to-tr from-amber-600 via-yellow-600 to-amber-700 opacity-70"></div>
                                            <div class="absolute inset-0.5 rounded-full bg-white"></div>
                                            @if($third->avatar)
                                                <img src="{{ $third->avatar }}" alt="{{ $third->name }}"
                                                    class="relative z-10 w-20 h-20 rounded-full object-cover shadow-md group-hover:scale-105 transition-transform"
                                                    style="width: 80px; height: 80px; min-width: 80px; min-height: 80px; object-fit: cover;"
                                                    onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';" loading="lazy">
                                            @else
                                                <div class="relative z-10 w-20 h-20 rounded-full bg-gradient-to-br from-amber-700 to-amber-900 flex items-center justify-center text-white text-xl font-black shadow-md group-hover:scale-105 transition-transform"
                                                    style="width: 80px; height: 80px; min-width: 80px; min-height: 80px;">
                                                    {{ $third->initials }}
                                                </div>
                                            @endif
                                        </div>
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

                                    <div class="mt-3 flex items-center justify-center gap-1.5 text-xs text-slate-500">
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

        <!-- Classificação Geral (4º lugar em diante) -->
        @if($remaining->isNotEmpty())
            <section class="pb-12 px-4 md:px-8">
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
                                        <span class="truncate max-w-[180px] font-medium text-slate-600">
                                            {{ $rank->occupation ?: ($rank->level ?? 'Membro da Comunidade') }}
                                        </span>
                                        @if(!empty($rank->company))
                                            <span class="hidden sm:inline-flex items-center gap-1 text-slate-400 text-[11px]">
                                                <i class="fas fa-building text-[9px]"></i> {{ $rank->company }}
                                            </span>
                                        @endif
                                        @if($rank->interactions_count > 0)
                                            <span class="inline-flex items-center gap-1 text-blue-600 font-semibold text-[11px]">
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

        {{-- Painel de Regras e Dinâmica de Pontuação (Posicionado abaixo do ranking) --}}
        <section id="regras-pontuacao" class="pb-16 px-4 md:px-8">
            <div class="max-w-5xl mx-auto bg-white/90 backdrop-blur-md rounded-3xl p-6 md:p-8 border border-slate-200/90 shadow-sm text-left">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-sm shrink-0">
                            <i class="fas fa-balance-scale"></i>
                        </span>
                        <div>
                            <h3 class="font-black text-base text-slate-900">Como Funciona a Pontuação: Ganhos & Decaimento</h3>
                            <p class="text-xs text-slate-500">Regras transparentes de incentivo ao engajamento na comunidade UNN</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[10px] uppercase font-bold tracking-wider text-slate-500 bg-slate-100 px-3 py-1 rounded-full w-fit">
                        <i class="fas fa-rotate text-blue-500"></i> Atualização Diária Automática
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
                    {{-- Ganhos (+) --}}
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80">
                        <p class="font-black text-emerald-900 flex items-center gap-2 mb-3 text-sm">
                            <i class="fas fa-arrow-trend-up text-emerald-600"></i> Como Ganhar Pontos (+)
                        </p>
                        <ul class="space-y-2 text-emerald-950/90 font-medium text-xs">
                            <li class="flex items-center justify-between py-1 border-b border-emerald-100/60">
                                <span>• Login diário na plataforma</span>
                                <strong class="text-emerald-700 font-black">+5 a +10 pts</strong>
                            </li>
                            <li class="flex items-center justify-between py-1 border-b border-emerald-100/60">
                                <span>• Consistência ativa semanal (3+ dias)</span>
                                <strong class="text-emerald-700 font-black">+20 pts</strong>
                            </li>
                            <li class="flex items-center justify-between py-1 border-b border-emerald-100/60">
                                <span>• Sequência de 7 e 30 dias seguidos</span>
                                <strong class="text-emerald-700 font-black">+50 a +200 pts</strong>
                            </li>
                            <li class="flex items-center justify-between py-1 border-b border-emerald-100/60">
                                <span>• Cada nova conexão aceita</span>
                                <strong class="text-emerald-700 font-black">+50 pts</strong>
                            </li>
                            <li class="flex items-center justify-between py-1 border-b border-emerald-100/60">
                                <span>• Postagens e comentários no feed</span>
                                <strong class="text-emerald-700 font-black">+3 a +20 pts</strong>
                            </li>
                            <li class="flex items-center justify-between py-1">
                                <span>• Eventos, palestras e mentorias</span>
                                <strong class="text-emerald-700 font-black">+30 a +150 pts</strong>
                            </li>
                        </ul>
                    </div>

                    {{-- Perdas / Penalidades (-) --}}
                    <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80">
                        <p class="font-black text-amber-950 flex items-center gap-2 mb-3 text-sm">
                            <i class="fas fa-arrow-trend-down text-amber-600"></i> Decaimento por Inatividade (-)
                        </p>
                        <ul class="space-y-2 text-amber-950/90 font-medium text-xs">
                            <li class="flex items-center justify-between py-1 border-b border-amber-100/60">
                                <span>• 7 dias sem acessar a plataforma</span>
                                <strong class="text-amber-700 font-black">-10 pts/sem</strong>
                            </li>
                            <li class="flex items-center justify-between py-1 border-b border-amber-100/60">
                                <span>• 15 dias sem login consecutivo</span>
                                <strong class="text-amber-800 font-black">-25 pts/sem</strong>
                            </li>
                            <li class="flex items-center justify-between py-1 border-b border-amber-100/60">
                                <span>• 30+ dias sem login na rede</span>
                                <strong class="text-rose-700 font-black">-50 pts/sem</strong>
                            </li>
                            <li class="flex items-center justify-between py-1 border-b border-amber-100/60">
                                <span>• 14 dias sem postar ou interagir</span>
                                <strong class="text-amber-700 font-black">-15 pts/quinzena</strong>
                            </li>
                            <li class="pt-2 text-[11px] text-amber-800 italic leading-relaxed">
                                <i class="fas fa-shield-alt text-amber-600 mr-1"></i>
                                <strong>Garantia de Piso Zero:</strong> Sua pontuação nunca fica negativa. Ao retomar seus acessos e interações, seus pontos voltam a subir normalmente!
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

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
                    <p class="text-white/85 text-sm sm:text-base mb-6 leading-relaxed">
                        Complete 100% do seu perfil, expanda suas conexões empresariais e participe dos eventos para somar pontos e conquistar posições de destaque.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ url('/painel/perfil') }}" class="px-6 py-3 rounded-2xl bg-white text-blue-700 font-black text-sm hover:bg-blue-50 transition shadow-lg">
                            <i class="fas fa-user-edit mr-1"></i> Completar Meu Perfil
                        </a>
                        <a href="{{ route('members.index') }}" class="px-6 py-3 rounded-2xl bg-white/20 hover:bg-white/30 text-white font-black text-sm border border-white/30 transition">
                            <i class="fas fa-users mr-1"></i> Conectar com Membros
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>

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
