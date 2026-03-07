@extends('layouts.app')

@section('title', 'Ranking da Comunidade - UNN')

@section('content')
    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen pb-16">
        <!-- Hero Section -->
        <section class="pt-10 md:pt-20 pb-12 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-black mb-4 unn-title-gradient">Ranking da Comunidade</h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Conheça os empreendedores que mais interagem e colaboram dentro do nosso ecossistema.
                    O networking gera poder de influência.
                </p>
                @if(isset($isDemo) && $isDemo)
                    <div class="mt-4">
                        <span
                            class="text-sm text-yellow-600 bg-yellow-50 px-3 py-1 rounded-full font-semibold border border-yellow-200">
                            <i class="fas fa-info-circle mr-1"></i> Dados Demonstrativos
                        </span>
                    </div>
                @endif
            </div>
        </section>

        <!-- Ranking Pódio (Top 3) -->
        @if($podium->isNotEmpty())
            <section class="px-6 md:px-12 lg:px-24 mb-16">
                <div class="max-w-5xl mx-auto">
                    @php
                        $medals = [
                            0 => ['bg' => 'from-yellow-400 to-amber-500', 'ring' => 'ring-yellow-400', 'label' => '🥇 1º lugar', 'icon' => 'fas fa-crown', 'iconColor' => 'text-yellow-400'],
                            1 => ['bg' => 'from-slate-300 to-slate-400', 'ring' => 'ring-slate-300', 'label' => '🥈 2º lugar', 'icon' => 'fas fa-medal', 'iconColor' => 'text-slate-400'],
                            2 => ['bg' => 'from-orange-400 to-amber-600', 'ring' => 'ring-orange-400', 'label' => '🥉 3º lugar', 'icon' => 'fas fa-medal', 'iconColor' => 'text-orange-400'],
                        ];
                    @endphp

                    <div class="grid md:grid-cols-3 gap-6 items-end">
                        @foreach($podium as $rank)
                            @php
                                $pos = $loop->index;
                                $med = $medals[$pos] ?? $medals[2];
                                $userName = optional($rank->user)->name ?? 'Empreendedor';
                                $userAvatar = optional($rank->user)->profile_photo_url ?? null;
                                $userLink = route('social.profile', optional($rank->user)->username ?? '');
                                $isFirst = $pos === 0;
                            @endphp

                            {{-- 1º lugar: card destacado com ouro --}}
                            @if($isFirst)
                                <a href="{{ $userLink }}"
                                    class="block lg:col-start-2 order-first md:order-none bg-gradient-to-b from-amber-50 to-white rounded-3xl p-8 shadow-xl ring-2 ring-yellow-400 relative overflow-hidden flex flex-col items-center text-center transform transition duration-300 hover:-translate-y-2 hover:shadow-2xl">
                                    {{-- Brilho decorativo --}}
                                    <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full opacity-20"
                                        style="background: radial-gradient(circle, #fbbf24, transparent)"></div>

                                    {{-- Corona --}}
                                    <div class="mb-3">
                                        <i class="fas fa-crown text-4xl text-yellow-400 drop-shadow-md"></i>
                                    </div>

                                    {{-- Avatar --}}
                                    @if($userAvatar)
                                        <img src="{{ $userAvatar }}" alt="{{ $userName }}"
                                            class="w-24 h-24 rounded-full object-cover ring-4 ring-yellow-300 shadow-xl mb-4">
                                    @else
                                        <div class="w-24 h-24 rounded-full flex items-center justify-center text-white font-black text-4xl ring-4 ring-yellow-300 shadow-xl mb-4"
                                            style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
                                            {{ strtoupper(substr($userName, 0, 1)) }}
                                        </div>
                                    @endif

                                    <h3 class="font-black text-2xl text-gray-900 group-hover:text-amber-600 transition">{{ $userName }}
                                    </h3>
                                    <p class="text-sm text-amber-600 font-bold mt-1 uppercase tracking-wide">{{ ucfirst($rank->level) }}
                                    </p>

                                    <div class="mt-5 pt-5 border-t border-yellow-200 w-full">
                                        <p class="text-4xl font-black text-amber-500 drop-shadow-sm">
                                            {{ number_format($rank->score, 0, ',', '.') }}
                                        </p>
                                        <p class="text-xs uppercase tracking-widest text-gray-500 mt-1 font-bold">pontos ganhos</p>
                                    </div>

                                    @if($rank->interactions_count > 0)
                                        <div class="flex justify-center gap-3 mt-4 text-xs font-semibold text-gray-400">
                                            <span class="bg-gray-100 px-2 py-1 rounded">
                                                <i class="fas fa-handshake mr-1"></i> {{ $rank->interactions_count }} conexões
                                            </span>
                                            @if($rank->average_rating)
                                                <span class="bg-amber-50 px-2 py-1 rounded text-amber-600">
                                                    {{ number_format($rank->average_rating, 1, ',', '.') }} <i
                                                        class="fas fa-star text-yellow-400"></i>
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    <span
                                        class="absolute top-4 left-4 bg-yellow-400 text-white text-sm font-black px-3 py-1 rounded-full shadow-md">1º
                                        TOP</span>
                                </a>

                                {{-- 2º e 3º lugar: cards menores --}}
                            @else
                                @php $orderClass = $pos === 1 ? 'md:order-first' : 'md:order-last'; @endphp
                                <a href="{{ $userLink }}"
                                    class="block {{ $orderClass }} bg-white rounded-3xl p-6 shadow-md border hover:border-{{ $pos === 1 ? 'slate-400' : 'orange-400' }} transition relative overflow-hidden flex flex-col items-center text-center transform hover:-translate-y-1">

                                    {{-- Número da posição --}}
                                    <span
                                        class="absolute top-4 left-4 bg-{{ $pos === 1 ? 'slate-400' : 'orange-400' }} text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm">{{ $pos + 1 }}º</span>

                                    {{-- Ícone medalha --}}
                                    <div class="mb-3 mt-2">
                                        <i class="{{ $med['icon'] }} text-2xl {{ $med['iconColor'] }} drop-shadow-sm"></i>
                                    </div>

                                    {{-- Avatar --}}
                                    @if($userAvatar)
                                        <img src="{{ $userAvatar }}" alt="{{ $userName }}"
                                            class="w-16 h-16 rounded-full object-cover ring-2 ring-{{ $pos === 1 ? 'slate-300' : 'orange-300' }} mb-3 shadow-md">
                                    @else
                                        <div class="w-16 h-16 rounded-full flex items-center justify-center text-white font-bold text-2xl ring-2 ring-{{ $pos === 1 ? 'slate-300' : 'orange-300' }} mb-3 shadow-md"
                                            style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
                                            {{ strtoupper(substr($userName, 0, 1)) }}
                                        </div>
                                    @endif

                                    <h3 class="font-bold text-lg text-gray-900">{{ $userName }}</h3>
                                    <p class="text-xs text-gray-500 mt-0.5 uppercase font-semibold">{{ ucfirst($rank->level) }}</p>

                                    <div class="mt-4 pt-4 border-t border-slate-100 w-full">
                                        <p class="text-3xl font-black text-{{ $pos === 1 ? 'slate-500' : 'orange-500' }}">
                                            {{ number_format($rank->score, 0, ',', '.') }}
                                        </p>
                                        <p class="text-[10px] tracking-widest uppercase text-gray-400 mt-1 font-bold">pontos ganhos</p>
                                    </div>

                                    @if($rank->interactions_count > 0)
                                        <div class="flex justify-center gap-2 mt-3 text-[10px] font-semibold text-gray-400">
                                            <span class="bg-gray-50 px-1.5 py-0.5 rounded">
                                                <i class="fas fa-handshake"></i> {{ $rank->interactions_count }}
                                            </span>
                                            @if($rank->average_rating)
                                                <span class="bg-yellow-50 px-1.5 py-0.5 rounded text-yellow-600">
                                                    {{ number_format($rank->average_rating, 1, ',', '.') }} <i class="fas fa-star"></i>
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </a>
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

        <!-- Demais Membros da Lista -->
        @if($remaining->isNotEmpty())
            <section class="px-4 md:px-12 lg:px-24">
                <div class="max-w-4xl mx-auto">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-list-ol text-gray-400"></i> Classificação Geral
                    </h3>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        @foreach($remaining as $rank)
                            @php
                                $userName = optional($rank->user)->name ?? 'Empreendedor';
                                $userAvatar = optional($rank->user)->profile_photo_url ?? null;
                                $userLink = route('social.profile', optional($rank->user)->username ?? '');
                                $position = $loop->index + 4; // Contagem após o Top 3
                            @endphp
                            <a href="{{ $userLink }}"
                                class="flex items-center p-4 sm:p-5 border-b border-gray-50 hover:bg-slate-50 transition group">
                                <div class="w-10 text-center flex-shrink-0">
                                    <span
                                        class="text-lg font-black text-gray-400 group-hover:text-blue-500 transition">{{ $position }}º</span>
                                </div>

                                <div class="mx-4">
                                    @if($userAvatar)
                                        <img src="{{ $userAvatar }}" alt="{{ $userName }}"
                                            class="w-12 h-12 rounded-full object-cover ring-2 ring-gray-100">
                                    @else
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg ring-2 ring-gray-100"
                                            style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
                                            {{ strtoupper(substr($userName, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-grow min-w-0">
                                    <h4 class="font-bold text-gray-900 truncate group-hover:text-blue-600 transition">
                                        {{ $userName }}</h4>
                                    <p class="text-xs text-gray-500 truncate mt-0.5">
                                        <span class="uppercase tracking-wider font-semibold">{{ ucfirst($rank->level) }}</span>
                                        @if($rank->interactions_count > 0)
                                            <span class="mx-2 inline-block w-1 h-1 bg-gray-300 rounded-full mb-0.5"></span>
                                            <i class="fas fa-handshake text-gray-400 mr-1"></i> {{ $rank->interactions_count }} conexões
                                        @endif
                                    </p>
                                </div>

                                <div class="text-right pl-4">
                                    <p class="text-xl font-black text-gray-700 decoration-blue-500 text-transparent bg-clip-text"
                                        style="background-image: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
                                        {{ number_format($rank->score, 0, ',', '.') }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mt-0.5">PTS</p>
                                </div>
                                <div class="pl-4 hidden sm:block">
                                    <i
                                        class="fas fa-chevron-right text-gray-300 group-hover:text-blue-400 transition transform group-hover:translate-x-1"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>

    <style>
        .unn-title-gradient {
            background: linear-gradient(90deg, #2E3192 0%, #0071BC 60%, #29ABE2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }
    </style>
@endsection