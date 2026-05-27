@extends('layouts.app')

@section('title', 'Ranking da Comunidade - UNN')

@section('content')
    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen pb-16">
        <!-- Hero Section -->
        <section class="pt-16 md:pt-24 pb-12 overflow-x-hidden">
            <div class="unn-container text-center px-4">
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-black mb-6 unn-title-gradient leading-[1.1] tracking-tight">Ranking UNN</h1>
                <p class="text-lg md:text-xl text-slate-500 max-w-2xl mx-auto font-medium leading-relaxed">
                    Conheça a elite da nossa comunidade. Empreendedores que geram valor, conexões reais e aceleram resultados através do networking.
                </p>
                @if(isset($isDemo) && $isDemo)
                    <div class="mt-8">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-50 border border-amber-200 text-amber-600 text-xs font-black uppercase tracking-widest">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></div>
                            Dados Demonstrativos
                        </span>
                    </div>
                @endif
            </div>
        </section>
        <!-- Ranking Pódio (Top 3) -->
        @if($podium->isNotEmpty())
            <section class="pt-10 md:pt-24 pb-20">
                <div class="unn-container px-4">
                    <div class="grid lg:grid-cols-3 gap-8 items-end max-w-5xl mx-auto">
                        @foreach($podium as $rank)
                            @php
                                $pos = $loop->index;
                                $userName = optional($rank->user)->name ?? 'Empreendedor';
                                $userAvatar = optional($rank->user)->profile_photo_url ?? null;
                                $userLink = optional($rank->user)->username ? route('social.profile', $rank->user->username) : '#';
                                $isFirst = $pos === 0;
                            @endphp

                            @if($isFirst)
                                <!-- Primeiro Lugar (Elite) -->
                                <div class="order-first lg:order-none relative group lg:-mb-4">
                                    <div class="absolute -inset-1 bg-gradient-to-r from-amber-400 via-yellow-200 to-amber-500 rounded-[2.5rem] blur opacity-25 group-hover:opacity-100 transition duration-1000 group-hover:duration-200"></div>
                                    <a href="{{ $userLink }}" class="relative block bg-white rounded-[2.5rem] p-10 shadow-2xl transition-all duration-500 hover:-translate-y-2 border border-amber-200 flex flex-col items-center text-center">
                                        {{-- Badge no topo --}}
                                        <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-gradient-to-r from-amber-400 to-yellow-500 text-white px-6 py-2 rounded-full text-xs font-black uppercase tracking-[0.2em] shadow-lg shadow-amber-500/30 flex items-center gap-2 z-10 transition-transform group-hover:scale-110">
                                            <i class="fas fa-crown text-sm"></i> 1º Elite
                                        </div>

                                        {{-- Avatar --}}
                                        <div class="relative mb-8 pt-4">
                                            {{-- Coroa flutuante --}}
                                            <div class="absolute -top-10 left-1/2 -translate-x-1/2 text-amber-500 text-4xl drop-shadow-xl filter group-hover:scale-125 transition-transform duration-500 z-10">
                                                <i class="fas fa-crown"></i>
                                            </div>
                                            <div class="absolute -inset-2 bg-gradient-to-tr from-amber-400 to-yellow-200 rounded-full animate-spin-slow opacity-50"></div>
                                            @if($userAvatar)
                                                <img src="{{ $userAvatar }}" alt="{{ $userName }}" class="relative w-32 h-32 rounded-full object-cover ring-4 ring-white shadow-2xl">
                                            @else
                                                <div class="relative w-32 h-32 rounded-full flex items-center justify-center text-white font-black text-5xl shadow-2xl" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
                                                    {{ strtoupper(substr($userName, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>

                                        <h3 class="font-black text-2xl text-slate-900 group-hover:text-amber-600 transition">{{ $userName }}</h3>
                                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600 mt-2">{{ $rank->level ?? 'Membro Gold' }}</p>

                                        <div class="mt-8 w-full border-t border-slate-50 pt-8">
                                            <div class="text-4xl md:text-5xl font-black bg-gradient-to-r from-amber-500 to-yellow-600 bg-clip-text text-transparent">
                                                {{ number_format($rank->score, 0, ',', '.') }}
                                            </div>
                                            <div class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400 mt-2">Pontuação acumulada</div>
                                        </div>

                                        @if($rank->interactions_count > 0)
                                            <div class="mt-6 flex flex-wrap justify-center gap-3">
                                                <span class="px-4 py-1.5 rounded-full bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-500 border border-slate-100 flex items-center gap-2">
                                                    <i class="fas fa-handshake text-amber-600"></i> {{ $rank->interactions_count }} conexões
                                                </span>
                                            </div>
                                        @endif
                                    </a>
                                </div>
                            @else
                                <!-- Segundo e Terceiro Lugares -->
                                <div class="{{ $pos === 1 ? 'lg:order-first' : 'lg:order-last' }}">
                                    <a href="{{ $userLink }}" class="block bg-white rounded-[2rem] p-8 shadow-xl transition-all duration-500 hover:-translate-y-1 border border-slate-100 flex flex-col items-center text-center group">
                                        {{-- Badge --}}
                                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full {{ $pos === 1 ? 'bg-slate-100 text-slate-500 border border-slate-200' : 'bg-orange-50 text-orange-600 border border-orange-100' }} text-[10px] font-black uppercase tracking-widest mb-6">
                                            @if($pos === 1)
                                                <i class="fas fa-medal text-slate-400"></i> 2º lugar
                                            @else
                                                <i class="fas fa-medal text-orange-400"></i> 3º lugar
                                            @endif
                                        </div>

                                        {{-- Avatar --}}
                                        <div class="mb-6">
                                            @if($userAvatar)
                                                <img src="{{ $userAvatar }}" alt="{{ $userName }}" class="w-20 h-20 rounded-full object-cover ring-4 ring-white shadow-lg">
                                            @else
                                                <div class="w-20 h-20 rounded-full flex items-center justify-center text-white font-black text-3xl shadow-lg" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
                                                    {{ strtoupper(substr($userName, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>

                                        <h3 class="font-black text-xl text-slate-900 group-hover:text-blue-600 transition">{{ $userName }}</h3>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-1">{{ $rank->level ?? 'Iniciante' }}</p>

                                        <div class="mt-6 w-full border-t border-slate-50 pt-6">
                                            <div class="text-3xl font-black text-slate-800">
                                                {{ number_format($rank->score, 0, ',', '.') }}
                                            </div>
                                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-1">PTS</div>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </section>
        @else
            <section class="px-6 pb-20 text-center">
                <i class="fas fa-trophy text-6xl text-gray-200 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-400">Nenhum membro ranqueado no momento.</h3>
            </section>
        @endif

        <!-- Classificação Geral -->
        @if($remaining->isNotEmpty())
            <section class="pb-24">
                <div class="unn-container px-4">
                    <div class="max-w-5xl mx-auto">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="text-2xl font-black text-slate-900 flex items-center gap-3">
                                <i class="fas fa-list-ol text-blue-600"></i> Classificação Geral
                            </h3>
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Todos os Membros</div>
                        </div>

                        <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden">
                            @foreach($remaining as $rank)
                                @php
                                    $userName = optional($rank->user)->name ?? 'Empreendedor';
                                    $userAvatar = optional($rank->user)->profile_photo_url ?? null;
                                    $userLink = optional($rank->user)->username ? route('social.profile', $rank->user->username) : '#';
                                    $position = $loop->index + 4;
                                @endphp
                                <a href="{{ $userLink }}" class="flex items-center p-6 md:p-8 border-b border-slate-50 hover:bg-slate-50/50 transition-all group last:border-0">
                                    <div class="w-10 md:w-16 flex-shrink-0">
                                        <span class="text-xl font-black text-slate-300 group-hover:text-blue-500 transition-colors">{{ str_pad($position, 2, '0', STR_PAD_LEFT) }}</span>
                                    </div>

                                    <div class="relative flex-shrink-0 mr-4 md:mr-6">
                                        @if($userAvatar)
                                            <img src="{{ $userAvatar }}" alt="{{ $userName }}" class="w-12 h-12 md:w-14 md:h-14 rounded-full object-cover ring-2 ring-white shadow-md">
                                        @else
                                            <div class="w-12 h-12 md:w-14 md:h-14 rounded-full flex items-center justify-center text-white font-black text-xl shadow-md" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
                                                {{ strtoupper(substr($userName, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-grow min-w-0 pr-4">
                                        <h4 class="font-black text-slate-900 text-lg truncate group-hover:text-blue-600 transition-colors">{{ $userName }}</h4>
                                        <div class="flex items-center gap-3 mt-1 text-xs font-bold text-slate-400">
                                            <span class="uppercase tracking-widest">{{ $rank->level ?? 'Membro' }}</span>
                                            @if($rank->interactions_count > 0)
                                                <div class="w-1 h-1 rounded-full bg-slate-200"></div>
                                                <span class="flex items-center gap-1"><i class="fas fa-handshake"></i> {{ $rank->interactions_count }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <div class="text-2xl font-black text-slate-900 group-hover:text-blue-600 transition-colors">
                                            {{ number_format($rank->score, 0, ',', '.') }}
                                        </div>
                                        <div class="text-[8px] font-black uppercase tracking-[0.2em] text-slate-400">PTS</div>
                                    </div>
                                    <div class="ml-6 hidden md:block">
                                        <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-blue-600 group-hover:text-white transition-all transform group-hover:translate-x-1">
                                            <i class="fas fa-chevron-right text-xs"></i>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>

    <style>
        .unn-title-gradient {
            background: linear-gradient(90deg, #1A237E 0%, #1F5EDB 50%, #00B0FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin-slow {
            animation: spin-slow 12s linear infinite;
        }
    </style>
@endsection
