@extends('panel.layouts.app')

@section('title', 'Meus Pontos')

@section('panel_content')
    @php
        $categoryColors = [
            'engajamento' => ['bg' => 'bg-blue-100 dark:bg-blue-900/40', 'text' => 'text-blue-600 dark:text-blue-400', 'icon' => 'fas fa-heart'],
            'aprendizado' => ['bg' => 'bg-green-100 dark:bg-green-900/40', 'text' => 'text-green-600 dark:text-green-400', 'icon' => 'fas fa-graduation-cap'],
            'comunidade'  => ['bg' => 'bg-purple-100 dark:bg-purple-900/40', 'text' => 'text-purple-600 dark:text-purple-400', 'icon' => 'fas fa-users'],
            'conquistas'  => ['bg' => 'bg-amber-100 dark:bg-amber-900/40', 'text' => 'text-amber-600 dark:text-amber-400', 'icon' => 'fas fa-trophy'],
            'bonus'       => ['bg' => 'bg-pink-100 dark:bg-pink-900/40', 'text' => 'text-pink-600 dark:text-pink-400', 'icon' => 'fas fa-gift'],
        ];
        $defaultColor = ['bg' => 'bg-slate-100 dark:bg-slate-800', 'text' => 'text-slate-500', 'icon' => 'fas fa-star'];
    @endphp

    <div class="space-y-8">

        {{-- ===== HEADER ===== --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Meus Pontos</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1 font-medium">Acompanhe sua jornada e evolua no ranking.</p>
            </div>
            <a href="{{ route('panel.redemptions.shop') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-blue-500/20 transition-all active:scale-95 shrink-0">
                <i class="fas fa-gift"></i>
                Trocar Pontos por Prêmios
            </a>
        </div>

        {{-- ===== CARDS DE RESUMO ===== --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

            {{-- Total de pontos --}}
            <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-3xl p-6 text-white shadow-lg shadow-blue-500/20 flex items-center gap-5">
                <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
                    <i class="fas fa-coins text-2xl"></i>
                </div>
                <div>
                    <div class="text-3xl font-black leading-none">{{ number_format($user->points ?? 0, 0, ',', '.') }}</div>
                    <div class="text-blue-100 font-semibold mt-1 text-sm">Pontos acumulados</div>
                </div>
            </div>

            {{-- Posição no ranking --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 flex items-center gap-5 shadow-sm">
                <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-2xl flex items-center justify-center shrink-0">
                    <i class="fas fa-trophy text-2xl text-amber-500"></i>
                </div>
                <div>
                    <div class="text-3xl font-black text-slate-900 dark:text-white leading-none">#{{ number_format($rankPosition, 0, ',', '.') }}</div>
                    <div class="text-slate-500 font-semibold mt-1 text-sm">
                        no ranking geral
                        @if($totalRanked > 0)
                            <span class="text-xs text-slate-400">de {{ number_format($totalRanked, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Pontos este mês --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 flex items-center gap-5 shadow-sm">
                <div class="w-14 h-14 bg-green-100 dark:bg-green-900/40 rounded-2xl flex items-center justify-center shrink-0">
                    <i class="fas fa-chart-line text-2xl text-green-500"></i>
                </div>
                <div>
                    <div class="text-3xl font-black text-slate-900 dark:text-white leading-none">{{ number_format($pontosEsteMes, 0, ',', '.') }}</div>
                    <div class="text-slate-500 font-semibold mt-1 text-sm">pontos em {{ now()->isoFormat('MMMM') }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- ===== HISTÓRICO ===== --}}
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">Histórico de Pontos</h2>
                        <p class="text-sm text-slate-400 mt-0.5">Todas as ações que geraram pontos para você.</p>
                    </div>

                    @forelse($logs as $log)
                        @php
                            $rule   = $rules->get($log->action_key);
                            $cat    = $rule->category ?? null;
                            $colors = $categoryColors[$cat] ?? $defaultColor;
                            $icon   = $rule->icon ?? $colors['icon'];
                            $label  = $rule->label ?? ucfirst(str_replace('_', ' ', $log->action_key));
                        @endphp
                        <div class="flex items-center gap-4 px-8 py-5 border-b border-slate-50 dark:border-slate-800/60 last:border-0 hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            {{-- Ícone da categoria --}}
                            <div class="w-11 h-11 {{ $colors['bg'] }} rounded-2xl flex items-center justify-center shrink-0">
                                <i class="{{ $icon }} {{ $colors['text'] }}"></i>
                            </div>

                            {{-- Descrição --}}
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $label }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    {{ $log->created_at->diffForHumans() }}
                                    &nbsp;·&nbsp;
                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>

                            {{-- Pontos --}}
                            <div class="shrink-0 text-right">
                                <span class="text-lg font-black text-blue-600 dark:text-blue-400">
                                    +{{ number_format($log->points, 0, ',', '.') }}
                                </span>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">pts</div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-20 text-center px-8">
                            <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-star text-2xl text-slate-300 dark:text-slate-600"></i>
                            </div>
                            <p class="font-bold text-slate-500 dark:text-slate-400">Nenhum ponto registrado ainda</p>
                            <p class="text-sm text-slate-400 mt-1 max-w-xs">Realize ações na plataforma — publique, conclua cursos, participe de eventos — para começar a acumular pontos.</p>
                        </div>
                    @endforelse

                    {{-- Paginação --}}
                    @if($logs->hasPages())
                        <div class="px-8 py-5 border-t border-slate-100 dark:border-slate-800">
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- ===== SIDEBAR: TOP 10 RANKING ===== --}}
            <div class="space-y-5">
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-ranking-star text-amber-500"></i>
                            Top 10 Ranking
                        </h3>
                    </div>
                    <div class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        @foreach($topUsers as $i => $topUser)
                            @php
                                $pos   = $i + 1;
                                $isMe  = $topUser->id === $user->id;
                                $medal = match($pos) { 1 => 'bg-amber-400 text-white', 2 => 'bg-slate-300 text-slate-700', 3 => 'bg-amber-600 text-white', default => 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' };
                            @endphp
                            <div class="flex items-center gap-3 px-6 py-4 {{ $isMe ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors' }}">
                                <span class="w-7 h-7 rounded-xl flex items-center justify-center text-xs font-black shrink-0 {{ $medal }}">
                                    {{ $pos <= 3 ? ['🥇','🥈','🥉'][$pos - 1] : $pos }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-sm text-slate-900 dark:text-white truncate {{ $isMe ? 'text-blue-700 dark:text-blue-400' : '' }}">
                                        {{ $topUser->name }}
                                        @if($isMe)<span class="text-[10px] bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 px-1.5 py-0.5 rounded-full font-black ml-1">Você</span>@endif
                                    </div>
                                </div>
                                <span class="font-black text-sm text-slate-700 dark:text-slate-300 shrink-0">
                                    {{ number_format($topUser->points ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach

                        {{-- Minha posição se não estiver no top 10 --}}
                        @if($rankPosition > 10)
                            <div class="px-6 py-3 text-center text-xs text-slate-400">
                                <i class="fas fa-ellipsis-v mb-2 block"></i>
                            </div>
                            <div class="flex items-center gap-3 px-6 py-4 bg-blue-50 dark:bg-blue-900/20">
                                <span class="w-7 h-7 bg-blue-100 dark:bg-blue-900/60 rounded-xl flex items-center justify-center text-xs font-black text-blue-600 dark:text-blue-400 shrink-0">
                                    {{ $rankPosition }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-sm text-blue-700 dark:text-blue-400 truncate">
                                        {{ $user->name }}
                                        <span class="text-[10px] bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 px-1.5 py-0.5 rounded-full font-black ml-1">Você</span>
                                    </div>
                                </div>
                                <span class="font-black text-sm text-blue-700 dark:text-blue-400 shrink-0">
                                    {{ number_format($user->points ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Como ganhar mais pontos --}}
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 dark:from-slate-800 dark:to-slate-900 rounded-[2rem] p-6 text-white shadow-sm border border-slate-700/50">
                    <h3 class="font-black text-sm mb-4 flex items-center gap-2"><i class="fas fa-bolt text-yellow-400"></i> Como ganhar mais pontos</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li class="flex items-start gap-2 text-slate-300">
                            <i class="fas fa-check-circle text-green-400 mt-0.5 shrink-0"></i>
                            Faça login <strong class="text-white">diariamente</strong> para pontos de streak
                        </li>
                        <li class="flex items-start gap-2 text-slate-300">
                            <i class="fas fa-check-circle text-green-400 mt-0.5 shrink-0"></i>
                            <strong class="text-white">Complete cursos</strong> e ganhe certificados
                        </li>
                        <li class="flex items-start gap-2 text-slate-300">
                            <i class="fas fa-check-circle text-green-400 mt-0.5 shrink-0"></i>
                            <strong class="text-white">Publique</strong> no feed e engaje a comunidade
                        </li>
                        <li class="flex items-start gap-2 text-slate-300">
                            <i class="fas fa-check-circle text-green-400 mt-0.5 shrink-0"></i>
                            <strong class="text-white">Indique amigos</strong> com seu link de referral
                        </li>
                        <li class="flex items-start gap-2 text-slate-300">
                            <i class="fas fa-check-circle text-green-400 mt-0.5 shrink-0"></i>
                            Entre no <strong class="text-white">Top 10</strong> e ganhe bônus semanal
                        </li>
                    </ul>
                    <a href="{{ route('panel.profile.edit') }}"
                       class="mt-5 w-full inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-colors">
                        <i class="fas fa-user-edit"></i> Completar meu perfil (+30 pts)
                    </a>
                </div>
            </div>

        </div>
    </div>
@endsection
