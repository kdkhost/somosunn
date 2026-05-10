@php
    $isDemo = $member->is_demo ?? false;
    $initials = collect(explode(' ', $member->name))->take(2)->map(fn($n) => strtoupper(mb_substr($n, 0, 1)))->join('');
    $conn = $connectionMap[$member->id] ?? null;
    $status = $conn ? ($conn['status'] ?? null) : null;
    $isRequester = $conn && ($conn['requester_id'] ?? null) === (auth()->id() ?? 0);
@endphp

<article class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col {{ $isDemo ? 'ring-1 ring-yellow-300' : '' }}">
    {{-- Header --}}
    <div class="h-14 bg-gradient-to-r from-blue-600 to-indigo-600 relative flex-shrink-0">
        @if($isDemo)
            <span class="absolute top-1.5 right-1.5 bg-yellow-100 text-yellow-800 text-[9px] px-1.5 py-0.5 rounded font-bold">DEMO</span>
        @endif
    </div>

    {{-- Avatar --}}
    <div class="flex justify-center -mt-8 relative z-10">
        @if(isset($member->avatar) && $member->avatar)
            <img src="{{ $member->avatar }}" alt="{{ $member->name }}"
                class="w-16 h-16 rounded-full border-3 border-white object-cover shadow-sm" loading="lazy">
        @else
            <div class="w-16 h-16 rounded-full border-3 border-white bg-blue-600 flex items-center justify-center text-white text-lg font-black shadow-sm">
                {{ $initials }}
            </div>
        @endif
    </div>

    {{-- Body --}}
    <div class="flex-1 flex flex-col pt-2 pb-4 px-3 text-center">
        <h3 class="text-sm font-black text-gray-900 truncate" title="{{ $member->name }}">{{ $member->name }}</h3>

        @if(!empty($member->occupation))
            <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wider truncate mt-0.5">{{ $member->occupation }}</p>
        @endif

        @if(!empty($member->city))
            <p class="text-[10px] text-gray-500 mt-1 flex items-center justify-center gap-0.5">
                <i class="fas fa-map-marker-alt text-[8px] text-blue-500"></i>
                {{ Str::limit($member->city, 20) }}
            </p>
        @endif

        <div class="mt-2 pt-2 border-t border-slate-100">
            <p class="text-lg font-black text-gray-900 leading-none">{{ $member->connections ?? 0 }}</p>
            <p class="text-[9px] uppercase tracking-widest text-gray-400 font-bold">Conexoes</p>
        </div>

        {{-- Actions --}}
        <div class="mt-auto pt-3 space-y-1.5">
            @if(!$isDemo)
                <a href="{{ route('social.profile', $member->id) }}"
                    class="block w-full bg-slate-100 hover:bg-slate-200 text-gray-700 py-2 rounded-lg font-bold text-xs text-center transition">
                    Ver perfil
                </a>

                @if(auth()->check() && auth()->id() !== $member->id)
                    @if($status === 'accepted')
                        <button onclick="openChat({{ $member->id }})"
                            class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-lg font-bold text-xs transition flex items-center justify-center gap-1">
                            <i class="fas fa-comment text-[10px]"></i> Chat
                        </button>
                    @elseif($status === 'pending')
                        @if($isRequester)
                            <button disabled class="w-full bg-gray-100 text-gray-400 py-2 rounded-lg font-bold text-xs cursor-not-allowed">
                                <i class="fas fa-clock text-[10px]"></i> Aguardando
                            </button>
                        @else
                            <button onclick="acceptConnection({{ $member->id }})"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-lg font-bold text-xs transition">
                                <i class="fas fa-check text-[10px]"></i> Aceitar
                            </button>
                        @endif
                    @else
                        <button onclick="requestConnection({{ $member->id }}, this)"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-bold text-xs transition flex items-center justify-center gap-1">
                            <i class="fas fa-user-plus text-[10px]"></i> Conectar
                        </button>
                    @endif

                    <button onclick="blockUser({{ $member->id }})"
                        class="w-full text-[10px] text-gray-400 hover:text-red-500 py-1 transition flex items-center justify-center gap-1">
                        <i class="fas fa-ban text-[8px]"></i> Bloquear
                    </button>
                @elseif(!auth()->check())
                    <a href="{{ route('login') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-bold text-xs text-center transition">
                        Conectar
                    </a>
                @endif
            @else
                <button disabled class="w-full bg-blue-600 text-white py-2 rounded-lg font-bold text-xs opacity-60 cursor-not-allowed">
                    Ver Perfil
                </button>
            @endif
        </div>
    </div>
</article>
