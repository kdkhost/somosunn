{{-- ==========================================================================
     PÓDIO OFICIAL DO RANKING SOMOS UNN (TOP 3)
     Compartilhado entre a Home (site.index) e a Página Oficial (/ranking).
     ========================================================================== --}}
@if(isset($podium) && $podium->isNotEmpty())
    <div class="ranking-podium-wrapper max-w-5xl mx-auto w-full">
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
                background: rgba(255, 255, 255, 0.92);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1px solid rgba(226, 232, 240, 0.9);
            }
            .podium-first-glow {
                box-shadow: 0 20px 50px -10px rgba(245, 158, 11, 0.25), 0 10px 30px -10px rgba(31, 94, 219, 0.1);
            }
        </style>

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
                    $secondLink = (!empty($second->is_demo)) ? '#' : route('social.profile', $second->user_id);
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
                                    @if(!empty($second->avatar))
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

                        @if(!empty($second->company))
                            <p class="text-xs text-slate-400 mt-1 truncate">
                                <i class="fas fa-building text-[10px] mr-1"></i>{{ $second->company }}
                            </p>
                        @endif

                        @if(!empty($second->plan_badge))
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
                    $firstLink = (!empty($first->is_demo)) ? '#' : route('social.profile', $first->user_id);
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
                                        @if(!empty($first->avatar))
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

                            @if(!empty($first->company))
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
                                @if(!empty($first->plan_badge))
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
                    $thirdLink = (!empty($third->is_demo)) ? '#' : route('social.profile', $third->user_id);
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
                                    @if(!empty($third->avatar))
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

                        @if(!empty($third->company))
                            <p class="text-xs text-slate-400 mt-1 truncate">
                                <i class="fas fa-building text-[10px] mr-1"></i>{{ $third->company }}
                            </p>
                        @endif

                        @if(!empty($third->plan_badge))
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
@endif
