@php
    $isDemo = $member->is_demo ?? false;
    $initials = collect(explode(' ', $member->name))->take(2)->map(fn($n) => strtoupper(mb_substr($n, 0, 1)))->join('');
    $conn = $connectionMap[$member->id] ?? null;
    $status = $conn ? ($conn['status'] ?? null) : null;
    $isRequester = $conn && ($conn['requester_id'] ?? null) === (auth()->id() ?? 0);
    $planBadge = $member->plan_badge ?? null;
    $badgeUpper = strtoupper($planBadge ?? '');
    $coverUrl = $member->cover ?? $member->cover_photo ?? null;
    if ($coverUrl && !str_starts_with($coverUrl, 'http') && !str_starts_with($coverUrl, '/')) {
        $coverUrl = asset($coverUrl);
    }
@endphp

<article class="group bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col {{ $isDemo ? 'ring-2 ring-amber-300' : '' }}">
    {{-- Header / Cover do Perfil (Capa Configurada pelo Usuário) --}}
    <div class="h-20 bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 relative flex-shrink-0 overflow-hidden">
        @if(!empty($coverUrl))
            <img src="{{ $coverUrl }}" alt="Capa de {{ $member->name }}"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                onerror="this.style.display='none'" loading="lazy" decoding="async">
            {{-- Overlay sutil com gradiente para garantir leitura perfeita dos badges e contraste do avatar --}}
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-black/25 pointer-events-none"></div>
        @else
            {{-- Detalhe sutil de brilho no fundo quando o usuário não tiver capa personalizada --}}
            <div class="absolute inset-0 bg-white/5 opacity-40"></div>
        @endif

        @if($isDemo)
            <span class="absolute top-2 right-2 bg-amber-400 text-slate-950 text-[10px] font-black px-2 py-0.5 rounded-full shadow-xs tracking-wider z-10">
                DEMO
            </span>
        @elseif(!empty($planBadge))
            @if(str_contains($badgeUpper, 'ELITE'))
                <span class="absolute top-2 right-2 bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 text-slate-950 text-[10px] font-black px-2.5 py-0.5 rounded-full shadow-sm tracking-wider uppercase ring-1 ring-amber-300/60 flex items-center gap-1 z-10">
                    <i class="fas fa-crown text-[8px]"></i> {{ $planBadge }}
                </span>
            @elseif(str_contains($badgeUpper, 'PRO'))
                <span class="absolute top-2 right-2 bg-white text-blue-700 text-[10px] font-black px-2.5 py-0.5 rounded-full shadow-sm tracking-wider uppercase ring-1 ring-blue-300/40 flex items-center gap-1 z-10">
                    <i class="fas fa-gem text-[8px]"></i> {{ $planBadge }}
                </span>
            @else
                <span class="absolute top-2 right-2 bg-white/95 text-slate-700 text-[10px] font-black px-2 py-0.5 rounded-full shadow-xs tracking-wider uppercase z-10">
                    {{ $planBadge }}
                </span>
            @endif
        @endif
    </div>

    {{-- Avatar com Anel Branco e Sombra --}}
    <div class="flex justify-center -mt-10 relative z-10">
        @if(isset($member->avatar) && $member->avatar)
            <img src="{{ $member->avatar }}" alt="{{ $member->name }}"
                class="w-18 h-18 rounded-full ring-4 ring-white object-cover shadow-md transition-transform duration-300 group-hover:scale-105" loading="lazy">
        @else
            <div class="w-18 h-18 rounded-full ring-4 ring-white bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center text-white text-lg font-black shadow-md transition-transform duration-300 group-hover:scale-105">
                {{ $initials }}
            </div>
        @endif
    </div>

    {{-- Body com Hierarquia Visual Refinada --}}
    <div class="flex-1 flex flex-col pt-2.5 pb-4 px-3.5 text-center">
        <h3 class="text-sm font-black text-slate-900 group-hover:text-blue-600 transition-colors truncate" title="{{ $member->name }}">
            {{ $member->name }}
        </h3>

        @if(!empty($member->occupation))
            <p class="text-[11px] font-bold text-blue-600 uppercase tracking-wider truncate mt-0.5" title="{{ $member->occupation }}">
                {{ $member->occupation }}
            </p>
        @else
            <p class="text-[11px] font-medium text-slate-400 mt-0.5 italic">
                Membro UNN
            </p>
        @endif

        {{-- Empresa / Localidade --}}
        <div class="mt-1 flex flex-wrap items-center justify-center gap-2 text-[11px] text-slate-500 min-h-[20px]">
            @if(!empty($member->company))
                <span class="inline-flex items-center gap-1 truncate max-w-[140px]" title="{{ $member->company }}">
                    <i class="fas fa-building text-[9px] text-slate-400"></i>
                    <span class="truncate">{{ $member->company }}</span>
                </span>
            @endif
            @if(!empty($member->city))
                <span class="inline-flex items-center gap-1 truncate max-w-[120px]" title="{{ $member->city }}">
                    <i class="fas fa-map-marker-alt text-[9px] text-blue-500"></i>
                    <span class="truncate">{{ Str::limit($member->city, 18) }}</span>
                </span>
            @endif
        </div>

        {{-- Conexões com Badge Elegante --}}
        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-center gap-1.5">
            <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-[10px]">
                <i class="fas fa-user-friends"></i>
            </span>
            <span class="text-sm font-black text-slate-800">{{ $member->connections ?? 0 }}</span>
            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">conexões</span>
        </div>

        {{-- Botões de Ação --}}
        <div class="mt-auto pt-3 space-y-1.5">
            @if(!$isDemo)
                <a href="{{ route('social.profile', $member->id) }}"
                    class="block w-full bg-slate-100 hover:bg-slate-200 text-slate-700 py-2 rounded-xl font-bold text-xs text-center transition-colors">
                    Ver perfil
                </a>

                @if(auth()->check() && auth()->id() !== $member->id)
                    @if($status === 'accepted')
                        <button type="button" onclick="openChat({{ $member->id }})"
                            class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-xl font-bold text-xs transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                            <i class="fas fa-comment text-[10px]"></i> Chat
                        </button>
                    @elseif($status === 'pending')
                        @if($isRequester)
                            <button type="button" disabled class="w-full bg-slate-100 text-slate-400 py-2 rounded-xl font-bold text-xs cursor-not-allowed">
                                <i class="fas fa-clock text-[10px]"></i> Aguardando
                            </button>
                        @else
                            <button type="button" onclick="acceptConnection({{ $member->id }})"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-xl font-bold text-xs transition-colors shadow-xs">
                                <i class="fas fa-check text-[10px]"></i> Aceitar
                            </button>
                        @endif
                    @else
                        <button type="button" onclick="requestConnection({{ $member->id }}, this)"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white py-2 rounded-xl font-bold text-xs transition-all shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fas fa-user-plus text-[10px]"></i> Conectar
                        </button>
                    @endif

                    <button type="button" onclick="blockUser({{ $member->id }})"
                        class="w-full text-[10px] text-slate-400 hover:text-red-500 py-0.5 transition-colors flex items-center justify-center gap-1 cursor-pointer">
                        <i class="fas fa-ban text-[8px]"></i> Bloquear
                    </button>
                @elseif(!auth()->check())
                    <a href="{{ route('login') }}" class="block w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white py-2 rounded-xl font-bold text-xs text-center transition-all shadow-xs">
                        Conectar
                    </a>
                @endif
            @else
                <button type="button" disabled class="w-full bg-blue-600 text-white py-2 rounded-xl font-bold text-xs opacity-60 cursor-not-allowed">
                    Ver Perfil
                </button>
            @endif
        </div>
    </div>
</article>
</article>
