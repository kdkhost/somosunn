@extends('layouts.app')

@section('title', 'Ranking da Comunidade - SOMOS UNN')

@section('content')
    {{-- Estilos e Animações Hyper Modernas --}}
    <style>
        @keyframes spinSlow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.55; transform: scale(1); }
            50% { opacity: 0.95; transform: scale(1.05); }
        }
        @keyframes floatCrown {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .animate-spin-slow {
            animation: spinSlow 8s linear infinite;
        }
        .animate-pulse-glow {
            animation: pulseGlow 3s ease-in-out infinite;
        }
        .animate-float-crown {
            animation: floatCrown 2.5s ease-in-out infinite;
        }
        .conic-gold-ring {
            background: conic-gradient(from 0deg, #d97706, #fbbf24, #fef08a, #f59e0b, #d97706);
        }
        .conic-silver-ring {
            background: conic-gradient(from 0deg, #64748b, #cbd5e1, #f8fafc, #94a3b8, #64748b);
        }
        .conic-bronze-ring {
            background: conic-gradient(from 0deg, #b45309, #f59e0b, #ffedd5, #d97706, #b45309);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.9);
        }
        .podium-first-glow {
            box-shadow: 0 20px 50px -10px rgba(245, 158, 11, 0.25), 0 10px 30px -10px rgba(31, 94, 219, 0.1);
        }
    </style>

    <div class="relative overflow-hidden bg-[#FAFCFF] min-h-screen pb-24">
        {{-- Elementos Decorativos de Fundo (Luzes Difusas / Glows) --}}
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[900px] h-[450px] bg-gradient-to-b from-blue-400/10 via-amber-300/10 to-transparent rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-96 -left-40 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-[600px] -right-40 w-96 h-96 bg-amber-400/5 rounded-full blur-3xl pointer-events-none"></div>

        <!-- ========================================== -->
        <!-- HERO SECTION                               -->
        <!-- ========================================== -->
        <section class="relative pt-12 md:pt-16 pb-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto text-center">
                {{-- Badge Luminosa --}}
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-black tracking-wide text-amber-900 bg-amber-100/90 border border-amber-300/80 mb-4 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <i class="fas fa-trophy text-amber-600"></i>
                    <span>GAMIFICAÇÃO & LIDERANÇA EMPRESARIAL</span>
                </div>

                {{-- Título Principal --}}
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black tracking-tight text-slate-900 mb-4">
                    Ranking da <span class="unn-title-gradient">Comunidade</span>
                </h1>
                <p class="text-sm sm:text-base md:text-lg text-slate-600 max-w-2xl mx-auto font-medium leading-relaxed">
                    Reconhecimento oficial aos membros que mais geram conexões qualificadas, compartilham valor e impulsionam o ecossistema SOMOS UNN.
                </p>

                {{-- Pilares Rápidos de Pontuação em Cards Modernos --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 max-w-4xl mx-auto mt-8 text-left">
                    <div class="glass-card p-4 rounded-2xl shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 text-sm shadow-2xs">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-900">+50 PTS</p>
                            <p class="text-[11px] text-slate-500 truncate">Nova conexão aceita</p>
                        </div>
                    </div>

                    <div class="glass-card p-4 rounded-2xl shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 text-sm shadow-2xs">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-900">+100 PTS</p>
                            <p class="text-[11px] text-slate-500 truncate">Perfil 100% completo</p>
                        </div>
                    </div>

                    <div class="glass-card p-4 rounded-2xl shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 text-sm shadow-2xs">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-900">+20 PTS</p>
                            <p class="text-[11px] text-slate-500 truncate">Interação no feed</p>
                        </div>
                    </div>

                    <div class="glass-card p-4 rounded-2xl shadow-2xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 text-sm shadow-2xs">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-900">+20 PTS</p>
                            <p class="text-[11px] text-slate-500 truncate">Consistência semanal</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="#regras-pontuacao" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 transition group">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        <span>Entenda a dinâmica de pontuação e decaimento por inatividade</span>
                        <i class="fas fa-arrow-down text-[10px] group-hover:translate-y-0.5 transition-transform"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- O PÓDIO DOS CAMPEÕES (TOP 3)               -->
        <!-- ========================================== -->
        @if($podium->isNotEmpty())
            <section class="relative pt-2 pb-12 px-4 sm:px-6 lg:px-8">
                <div class="max-w-5xl mx-auto">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                        @php
                            $first = $podium->get(0);
                            $second = $podium->get(1);
                            $third = $podium->get(2);
                        @endphp

                        {{-- ========================================== --}}
                        {{-- 2º LUGAR (PRATA) - ESQUERDA NO DESKTOP     --}}
                        {{-- ========================================== --}}
                        @if($second)
                            @php
                                $secondLink = $second->is_demo ? '#' : route('social.profile', $second->user_id);
                            @endphp
                            <div class="order-2 md:order-1">
                                <a href="{{ $secondLink }}" class="group block glass-card rounded-3xl p-6 sm:p-7 shadow-md hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-300 border border-slate-200/90 text-center relative overflow-hidden">
                                    {{-- Faixa superior prata --}}
                                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-slate-300 via-slate-400 to-slate-300"></div>

                                    {{-- Badge Prata --}}
                                    <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-slate-100 text-slate-800 border border-slate-300/80 text-[11px] font-black uppercase tracking-wider mb-4 shadow-2xs">
                                        <i class="fas fa-medal text-slate-500"></i> 2º Lugar • Prata
                                    </div>

                                    {{-- Avatar com Anel Prateado Giratório --}}
                                    <div class="relative flex justify-center mb-4 pt-1">
                                        <div class="relative w-22 h-22 flex items-center justify-center">
                                            <div class="absolute -inset-1 rounded-full conic-silver-ring animate-spin-slow opacity-90 shadow-sm"></div>
                                            <div class="absolute inset-0.5 rounded-full bg-white"></div>
                                            <div class="relative z-10 w-20 h-20 rounded-full overflow-hidden shadow-md" style="width: 80px; height: 80px;">
                                                @if($second->avatar)
                                                    <img src="{{ $second->avatar }}" alt="{{ $second->name }}"
                                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform"
                                                        style="width: 100%; height: 100%; object-fit: cover;"
                                                        onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';" loading="lazy">
                                                @else
                                                    <div class="w-full h-full bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center text-white text-xl font-black">
                                                        {{ $second->initials }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <h3 class="font-black text-lg text-slate-900 group-hover:text-blue-600 transition-colors truncate" title="{{ $second->name }}">
                                        {{ $second->name }}
                                    </h3>
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-0.5 truncate">{{ $second->occupation ?: ($second->level ?? 'Membro UNN') }}</p>

                                    @if($second->company)
                                        <p class="text-xs text-slate-400 mt-1 truncate">
                                            <i class="fas fa-building text-[10px] mr-1"></i>{{ $second->company }}
                                        </p>
                                    @endif

                                    @if($second->plan_badge)
                                        <span class="inline-block mt-2.5 px-3 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $second->plan_badge }}
                                        </span>
                                    @endif

                                    <div class="mt-4 pt-4 border-t border-slate-100">
                                        <p class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($second->score, 0, ',', '.') }}</p>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-0.5">Pontos</p>
                                    </div>

                                    @php
                                        $secondConns = (int) ($second->connections_count ?? $second->interactions_count ?? 0);
                                    @endphp
                                    <div class="mt-3 inline-flex items-center justify-center gap-1.5 px-3.5 py-1 rounded-full bg-slate-100/90 text-slate-700 border border-slate-200/80 text-xs font-bold shadow-2xs">
                                        <i class="fas fa-handshake text-blue-600"></i>
                                        <span>{{ $secondConns }} {{ $secondConns === 1 ? 'conexão' : 'conexões' }}</span>
                                    </div>
                                </a>
                            </div>
                        @endif

                        {{-- ========================================== --}}
                        {{-- 1º LUGAR (OURO - CAMPEÃO) - CENTRO ELEVADO --}}
                        {{-- ========================================== --}}
                        @if($first)
                            @php
                                $firstLink = $first->is_demo ? '#' : route('social.profile', $first->user_id);
                            @endphp
                            <div class="order-1 md:order-2 md:-mb-4 z-20">
                                <div class="relative group">
                                    {{-- Aura Luminosa Dourada --}}
                                    <div class="absolute -inset-1.5 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 rounded-[2.5rem] blur-lg opacity-45 group-hover:opacity-75 transition duration-500"></div>

                                    <a href="{{ $firstLink }}" class="relative block glass-card rounded-[2rem] p-7 sm:p-9 podium-first-glow hover:-translate-y-2 transition-all duration-300 border-2 border-amber-300 text-center overflow-hidden">
                                        {{-- Faixa superior ouro reluzente --}}
                                        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500"></div>

                                        {{-- Badge Ouro Flutuante com Coroa --}}
                                        <div class="inline-flex items-center gap-2 px-5 py-1.5 rounded-full bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 text-slate-950 text-xs font-black uppercase tracking-wider mb-4 shadow-md shadow-amber-400/30">
                                            <i class="fas fa-crown text-xs"></i> 1º Lugar • Campeão
                                        </div>

                                        {{-- Avatar Ouro com Coroa Flutuante e Anel Giratório Conic Glow --}}
                                        <div class="relative flex justify-center mb-5 pt-4">
                                            {{-- Coroa flutuante animada no topo com glow dourado --}}
                                            <div class="absolute -top-4 text-amber-500 text-3xl filter drop-shadow-md z-30 animate-float-crown">
                                                <i class="fas fa-crown"></i>
                                            </div>

                                            {{-- Anel Giratório Contínuo Dourado Conic --}}
                                            <div class="relative w-28 h-28 sm:w-32 sm:h-32 flex items-center justify-center">
                                                {{-- Glow pulsante dourado --}}
                                                <div class="absolute -inset-2 rounded-full bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 opacity-60 blur-md animate-pulse-glow"></div>
                                                {{-- Borda giratória contínua em 360 graus com gradiente cônico vivo --}}
                                                <div class="absolute -inset-1.5 rounded-full conic-gold-ring animate-spin-slow shadow-md"></div>
                                                {{-- Borda interna branca para contraste nítido --}}
                                                <div class="absolute inset-0.5 rounded-full bg-white"></div>

                                                {{-- Foto do Campeão --}}
                                                <div class="relative z-10 w-24 h-24 sm:w-28 sm:h-28 rounded-full overflow-hidden shadow-xl" style="width: 104px; height: 104px;">
                                                    @if($first->avatar)
                                                        <img src="{{ $first->avatar }}" alt="{{ $first->name }}"
                                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform"
                                                            style="width: 100%; height: 100%; object-fit: cover;"
                                                            onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';" loading="lazy">
                                                    @else
                                                        <div class="w-full h-full bg-gradient-to-br from-amber-500 via-yellow-500 to-amber-600 flex items-center justify-center text-white text-3xl font-black">
                                                            {{ $first->initials }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Nome e Cargo --}}
                                        <h3 class="font-black text-xl sm:text-2xl text-slate-900 group-hover:text-amber-600 transition-colors truncate" title="{{ $first->name }}">
                                            {{ $first->name }}
                                        </h3>
                                        <p class="text-xs font-bold text-amber-700 uppercase tracking-wider mt-1 truncate">{{ $first->occupation ?: ($first->level ?? 'Elite UNN') }}</p>

                                        @if($first->company)
                                            <p class="text-xs text-slate-500 mt-1 truncate flex items-center justify-center gap-1 font-medium">
                                                <i class="fas fa-building text-[10px] text-slate-400"></i> {{ $first->company }}
                                            </p>
                                        @endif

                                        {{-- Pontuação Monumental e 100% Legível --}}
                                        <div class="mt-5 pt-5 border-t border-amber-100">
                                            <p class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight leading-none">
                                                {{ number_format($first->score, 0, ',', '.') }}
                                            </p>
                                            <div class="inline-flex items-center gap-1 text-[11px] font-black uppercase tracking-widest text-amber-600 mt-1.5">
                                                <i class="fas fa-star text-[10px]"></i>
                                                <span>Pontos Acumulados</span>
                                            </div>
                                        </div>

                                        @php
                                            $firstConns = (int) ($first->connections_count ?? $first->interactions_count ?? 0);
                                        @endphp
                                        <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-amber-50 text-amber-900 text-xs font-bold border border-amber-200 shadow-2xs">
                                                <i class="fas fa-handshake text-amber-600"></i>
                                                <span>{{ $firstConns }} {{ $firstConns === 1 ? 'conexão' : 'conexões' }}</span>
                                            </span>
                                            @if($first->plan_badge)
                                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-slate-900 text-amber-400 text-xs font-black uppercase tracking-wider shadow-xs">
                                                    <i class="fas fa-gem text-[10px]"></i> {{ $first->plan_badge }}
                                                </span>
                                            @endif
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- ========================================== --}}
                        {{-- 3º LUGAR (BRONZE) - DIREITA NO DESKTOP     --}}
                        {{-- ========================================== --}}
                        @if($third)
                            @php
                                $thirdLink = $third->is_demo ? '#' : route('social.profile', $third->user_id);
                            @endphp
                            <div class="order-3">
                                <a href="{{ $thirdLink }}" class="group block glass-card rounded-3xl p-6 sm:p-7 shadow-md hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-300 border border-slate-200/90 text-center relative overflow-hidden">
                                    {{-- Faixa superior bronze --}}
                                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-600 via-amber-700 to-amber-600"></div>

                                    {{-- Badge Bronze --}}
                                    <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-amber-50 text-amber-900 border border-amber-200 text-[11px] font-black uppercase tracking-wider mb-4 shadow-2xs">
                                        <i class="fas fa-medal text-amber-700"></i> 3º Lugar • Bronze
                                    </div>

                                    {{-- Avatar com Anel Bronze Giratório --}}
                                    <div class="relative flex justify-center mb-4 pt-1">
                                        <div class="relative w-22 h-22 flex items-center justify-center">
                                            <div class="absolute -inset-1 rounded-full conic-bronze-ring animate-spin-slow opacity-90 shadow-sm"></div>
                                            <div class="absolute inset-0.5 rounded-full bg-white"></div>
                                            <div class="relative z-10 w-20 h-20 rounded-full overflow-hidden shadow-md" style="width: 80px; height: 80px;">
                                                @if($third->avatar)
                                                    <img src="{{ $third->avatar }}" alt="{{ $third->name }}"
                                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform"
                                                        style="width: 100%; height: 100%; object-fit: cover;"
                                                        onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';" loading="lazy">
                                                @else
                                                    <div class="w-full h-full bg-gradient-to-br from-amber-700 to-amber-900 flex items-center justify-center text-white text-xl font-black">
                                                        {{ $third->initials }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <h3 class="font-black text-lg text-slate-900 group-hover:text-blue-600 transition-colors truncate" title="{{ $third->name }}">
                                        {{ $third->name }}
                                    </h3>
                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-0.5 truncate">{{ $third->occupation ?: ($third->level ?? 'Membro UNN') }}</p>

                                    @if($third->company)
                                        <p class="text-xs text-slate-400 mt-1 truncate">
                                            <i class="fas fa-building text-[10px] mr-1"></i>{{ $third->company }}
                                        </p>
                                    @endif

                                    @if($third->plan_badge)
                                        <span class="inline-block mt-2.5 px-3 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200">
                                            {{ $third->plan_badge }}
                                        </span>
                                    @endif

                                    <div class="mt-4 pt-4 border-t border-slate-100">
                                        <p class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($third->score, 0, ',', '.') }}</p>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-0.5">Pontos</p>
                                    </div>

                                    @php
                                        $thirdConns = (int) ($third->connections_count ?? $third->interactions_count ?? 0);
                                    @endphp
                                    <div class="mt-3 inline-flex items-center justify-center gap-1.5 px-3.5 py-1 rounded-full bg-amber-50/80 text-amber-900 border border-amber-200/80 text-xs font-bold shadow-2xs">
                                        <i class="fas fa-handshake text-amber-700"></i>
                                        <span>{{ $thirdConns }} {{ $thirdConns === 1 ? 'conexão' : 'conexões' }}</span>
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

        <!-- ========================================== -->
        <!-- CLASSIFICAÇÃO GERAL (4º LUGAR EM DIANTE)   -->
        <!-- ========================================== -->
        @if($remaining->isNotEmpty())
            <section class="pb-12 px-4 sm:px-6 lg:px-8">
                <div class="max-w-5xl mx-auto">
                    {{-- Header da Tabela com Busca em Tempo Real e Filtros --}}
                    <div class="glass-card rounded-3xl p-5 md:p-6 shadow-sm border border-slate-200/90 mb-4 flex flex-col md:flex-row items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-900 flex items-center gap-2.5">
                                <span class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-sm shadow-2xs">
                                    <i class="fas fa-list-ol"></i>
                                </span>
                                <span>Classificação Geral</span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-2 font-medium">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Em tempo real
                                </span>
                                <span>Exibindo <strong>{{ $totalRanked }}</strong> membros ativos na comunidade</span>
                            </p>
                        </div>

                        {{-- Barra de Busca Instantânea com Estilo Pill Moderno --}}
                        <div class="w-full md:w-80 relative flex items-center h-12 bg-slate-50 hover:bg-slate-100/80 focus-within:bg-white rounded-full px-4 border border-slate-200 transition-all focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-500/10 shadow-2xs">
                            <i class="fas fa-search text-slate-400 text-sm mr-3 shrink-0"></i>
                            <input type="text" id="ranking-search-input" placeholder="Buscar por nome, cargo ou empresa..."
                                class="w-full bg-transparent border-none outline-none text-slate-800 text-xs font-semibold placeholder:text-slate-400">
                            <button type="button" id="ranking-search-clear" class="hidden text-slate-400 hover:text-slate-600 text-xs cursor-pointer ml-1 p-1" title="Limpar busca">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Lista Estilizada de Membros Ranqueados --}}
                    <div class="glass-card rounded-3xl shadow-sm border border-slate-200/90 divide-y divide-slate-100 overflow-hidden" id="ranking-list-container">
                        @foreach($remaining as $rank)
                            @php
                                $position = $loop->index + 4;
                                $memberLink = $rank->is_demo ? '#' : route('social.profile', $rank->user_id);
                                $isTopTen = $position <= 10;
                            @endphp
                            <div class="ranking-item flex items-center p-4 md:p-5 hover:bg-blue-50/40 transition-all duration-200 group"
                                data-name="{{ mb_strtolower($rank->name) }}"
                                data-occupation="{{ mb_strtolower($rank->occupation ?? '') }}"
                                data-company="{{ mb_strtolower($rank->company ?? '') }}">

                                {{-- Posição do Membro --}}
                                <div class="w-12 sm:w-16 flex-shrink-0 text-center">
                                    <span class="inline-flex items-center justify-center text-xs sm:text-sm font-black px-2.5 py-1 rounded-xl transition-colors {{ $isTopTen ? 'bg-blue-50 text-blue-700 border border-blue-200/60 font-black' : 'text-slate-400 group-hover:text-slate-700' }}">
                                        #{{ str_pad($position, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>

                                {{-- Avatar com Dimensões Estritas e Status --}}
                                <div class="relative flex-shrink-0 mr-3.5 sm:mr-4">
                                    @if($rank->avatar)
                                        <img src="{{ $rank->avatar }}" alt="{{ $rank->name }}"
                                            class="w-12 h-12 rounded-full object-cover ring-2 ring-slate-100 shadow-xs shrink-0 group-hover:ring-blue-300 transition-all"
                                            style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; object-fit: cover;"
                                            onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';" loading="lazy">
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 via-indigo-600 to-blue-700 flex items-center justify-center text-white font-black text-sm shadow-xs shrink-0 group-hover:scale-105 transition-transform"
                                            style="width: 48px; height: 48px; min-width: 48px; min-height: 48px;">
                                            {{ $rank->initials }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Informações do Membro --}}
                                <div class="flex-1 min-w-0 pr-3 sm:pr-4">
                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                        <a href="{{ $memberLink }}" class="font-black text-slate-900 text-sm sm:text-base truncate group-hover:text-blue-600 transition-colors hover:underline" title="{{ $rank->name }}">
                                            {{ $rank->name }}
                                        </a>

                                        @if($rank->plan_badge)
                                            <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full {{ str_contains(strtoupper($rank->plan_badge), 'ELITE') ? 'bg-amber-100 text-amber-900 border border-amber-200' : (str_contains(strtoupper($rank->plan_badge), 'PRO') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600') }}">
                                                {{ $rank->plan_badge }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 mt-1 text-xs text-slate-500">
                                        <span class="truncate max-w-[200px] font-medium text-slate-600 flex items-center gap-1.5">
                                            @if(!empty($rank->occupation))
                                                <i class="fas fa-briefcase text-[10px] text-blue-500 shrink-0"></i>
                                                <span class="truncate">{{ $rank->occupation }}</span>
                                            @else
                                                <i class="fas fa-user-check text-[10px] text-slate-400 shrink-0"></i>
                                                <span>{{ $rank->level ?? 'Membro UNN' }}</span>
                                            @endif
                                        </span>

                                        @if(!empty($rank->company))
                                            <span class="inline-flex items-center gap-1 text-slate-500 text-[11px] truncate max-w-[170px] bg-slate-50 px-2 py-0.5 rounded-md border border-slate-100">
                                                <i class="fas fa-building text-[9px] text-slate-400 shrink-0"></i> {{ $rank->company }}
                                            </span>
                                        @endif

                                        @if(!empty($rank->city))
                                            <span class="hidden md:inline-flex items-center gap-1 text-slate-400 text-[11px] truncate max-w-[150px]">
                                                <i class="fas fa-map-marker-alt text-[9px] text-slate-400 shrink-0"></i> {{ $rank->city }}
                                            </span>
                                        @endif

                                        @php
                                            $conns = (int) ($rank->connections_count ?? $rank->interactions_count ?? 0);
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 text-blue-600 font-bold text-[11px] bg-blue-50/80 border border-blue-200/50 px-2.5 py-0.5 rounded-md shadow-2xs shrink-0">
                                            <i class="fas fa-handshake text-[10px]"></i>
                                            <span>{{ $conns }} {{ $conns === 1 ? 'conexão' : 'conexões' }}</span>
                                        </span>
                                    </div>
                                </div>

                                {{-- Pontuação em Destaque --}}
                                <div class="text-right flex-shrink-0 mr-3 sm:mr-5">
                                    <p class="text-lg sm:text-xl font-black text-slate-900 group-hover:text-blue-600 transition-colors tracking-tight">
                                        {{ number_format($rank->score, 0, ',', '.') }}
                                    </p>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">PTS</p>
                                </div>

                                {{-- Ação Rápida --}}
                                <div class="flex-shrink-0">
                                    <a href="{{ $memberLink }}"
                                        class="w-9 h-9 rounded-full bg-slate-100 group-hover:bg-blue-600 group-hover:text-white text-slate-400 flex items-center justify-center text-xs transition-all duration-200 shadow-2xs group-hover:scale-110"
                                        title="Ver perfil de {{ $rank->name }}">
                                        <i class="fas fa-chevron-right text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Estado Vazio ao Buscar --}}
                    <div id="ranking-no-results" class="hidden glass-card rounded-3xl p-12 text-center border border-slate-200/90 shadow-sm mt-3">
                        <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400 text-xl">
                            <i class="fas fa-search"></i>
                        </div>
                        <p class="font-black text-slate-800 text-base">Nenhum membro encontrado</p>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Não encontramos nenhum membro com o termo pesquisado. Tente buscar por outro nome, cargo ou empresa.</p>
                    </div>
                </div>
            </section>
        @endif

        <!-- ========================================== -->
        <!-- DINÂMICA DE PONTUAÇÃO (COMO FUNCIONA)      -->
        <!-- ========================================== -->
        <section id="regras-pontuacao" class="pb-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto glass-card rounded-3xl p-6 md:p-9 shadow-sm border border-slate-200/90 text-left">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center text-sm shrink-0 shadow-2xs">
                            <i class="fas fa-balance-scale"></i>
                        </span>
                        <div>
                            <h3 class="font-black text-lg text-slate-900">Como Funciona a Pontuação</h3>
                            <p class="text-xs text-slate-500">Mecanismo oficial de pontuação ativa e decaimento programado por inatividade</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-[10px] uppercase font-bold tracking-wider text-slate-600 bg-slate-100 px-3 py-1.5 rounded-full w-fit">
                        <i class="fas fa-rotate text-blue-500"></i> Atualização Automática
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                    {{-- Ganhos (+) --}}
                    <div class="p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 shadow-2xs">
                        <p class="font-black text-emerald-900 flex items-center gap-2 mb-3.5 text-sm">
                            <i class="fas fa-arrow-trend-up text-emerald-600"></i> Como Ganhar Pontos Recorrentes (+)
                        </p>
                        <ul class="space-y-2.5 text-emerald-950 font-medium text-xs">
                            <li class="flex items-center justify-between py-1 border-b border-emerald-100/70">
                                <span>• Login diário na plataforma</span>
                                <strong class="text-emerald-700 font-black">+5 a +10 pts</strong>
                            </li>
                            <li class="flex items-center justify-between py-1 border-b border-emerald-100/70">
                                <span>• Consistência ativa semanal (3+ dias)</span>
                                <strong class="text-emerald-700 font-black">+20 pts</strong>
                            </li>
                            <li class="flex items-center justify-between py-1 border-b border-emerald-100/70">
                                <span>• Sequência de 7 e 30 dias seguidos</span>
                                <strong class="text-emerald-700 font-black">+50 a +200 pts</strong>
                            </li>
                            <li class="flex items-center justify-between py-1 border-b border-emerald-100/70">
                                <span>• Cada nova conexão aceita</span>
                                <strong class="text-emerald-700 font-black">+50 pts</strong>
                            </li>
                            <li class="flex items-center justify-between py-1 border-b border-emerald-100/70">
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
                    <div class="p-5 rounded-2xl bg-amber-50/70 border border-amber-200/80 shadow-2xs">
                        <p class="font-black text-amber-950 flex items-center gap-2 mb-3.5 text-sm">
                            <i class="fas fa-arrow-trend-down text-amber-600"></i> Decaimento por Inatividade (-)
                        </p>
                        <ul class="space-y-2.5 text-amber-950 font-medium text-xs">
                            <li class="flex items-center justify-between py-1 border-b border-amber-100/70">
                                <span>• 7 dias sem acessar a plataforma</span>
                                <strong class="text-amber-800 font-black">-10 pts/sem</strong>
                            </li>
                            <li class="flex items-center justify-between py-1 border-b border-amber-100/70">
                                <span>• 15 dias sem login consecutivo</span>
                                <strong class="text-amber-800 font-black">-25 pts/sem</strong>
                            </li>
                            <li class="flex items-center justify-between py-1 border-b border-amber-100/70">
                                <span>• 30+ dias sem login na rede</span>
                                <strong class="text-rose-700 font-black">-50 pts/sem</strong>
                            </li>
                            <li class="flex items-center justify-between py-1 border-b border-amber-100/70">
                                <span>• 14 dias sem postar ou interagir</span>
                                <strong class="text-amber-800 font-black">-15 pts/quinzena</strong>
                            </li>
                            <li class="pt-2 text-[11px] text-amber-900/90 leading-relaxed font-semibold">
                                <i class="fas fa-shield-alt text-amber-600 mr-1"></i>
                                <span>Garantia de Piso Zero:</span> Sua pontuação nunca fica negativa. Ao retomar seus acessos e interações, seus pontos voltam a subir normalmente!
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- CTA DE SUBIDA DE RANKING                   -->
        <!-- ========================================== -->
        <section class="px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto rounded-3xl p-8 sm:p-12 text-center text-white relative overflow-hidden shadow-2xl"
                style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
                <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 w-60 h-60 bg-blue-400/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 max-w-2xl mx-auto">
                    <span class="inline-flex items-center gap-1.5 px-4 py-1 rounded-full bg-white/15 border border-white/20 text-xs font-black uppercase tracking-wider text-white mb-3 shadow-2xs">
                        <i class="fas fa-rocket text-amber-300"></i> Acelere seu Posicionamento
                    </span>
                    <h3 class="text-2xl sm:text-3xl md:text-4xl font-black mb-3.5 tracking-tight">
                        Quer alcançar o topo do Ranking UNN?
                    </h3>
                    <p class="text-white/85 text-sm sm:text-base mb-8 leading-relaxed font-medium">
                        Complete 100% do seu perfil, expanda suas conexões empresariais e participe dos eventos para somar pontos e conquistar posições de grande destaque.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3.5">
                        <a href="{{ url('/painel/perfil') }}" class="px-7 py-3.5 rounded-2xl bg-white text-blue-700 font-black text-sm hover:bg-blue-50 transition shadow-lg hover:shadow-xl hover:-translate-y-0.5 duration-200">
                            <i class="fas fa-user-edit mr-1.5"></i> Completar Meu Perfil
                        </a>
                        <a href="{{ url('/membros') }}" class="px-7 py-3.5 rounded-2xl bg-white/15 hover:bg-white/25 text-white font-black text-sm border border-white/30 transition hover:-translate-y-0.5 duration-200 backdrop-blur-sm">
                            <i class="fas fa-users mr-1.5"></i> Explorar Membros
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
