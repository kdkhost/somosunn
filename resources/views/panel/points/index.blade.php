@extends('panel.layouts.app')

@section('title', 'Meus UNNBIT')

@section('panel_content')
    @php
        $coinName = (string) ($exchangeSettings['coin_name'] ?? 'UNNBIT');
        $unitValue = (float) ($exchangeSettings['unit_value_brl'] ?? $exchangeSettings['point_value'] ?? 0.01);
        $categoryColors = [
            'engajamento' => ['bg' => 'bg-blue-100 dark:bg-blue-900/40', 'text' => 'text-blue-600 dark:text-blue-400', 'icon' => 'fas fa-heart'],
            'aprendizado' => ['bg' => 'bg-green-100 dark:bg-green-900/40', 'text' => 'text-green-600 dark:text-green-400', 'icon' => 'fas fa-graduation-cap'],
            'comunidade' => ['bg' => 'bg-purple-100 dark:bg-purple-900/40', 'text' => 'text-purple-600 dark:text-purple-400', 'icon' => 'fas fa-users'],
            'conquistas' => ['bg' => 'bg-amber-100 dark:bg-amber-900/40', 'text' => 'text-amber-600 dark:text-amber-400', 'icon' => 'fas fa-trophy'],
            'bonus' => ['bg' => 'bg-pink-100 dark:bg-pink-900/40', 'text' => 'text-pink-600 dark:text-pink-400', 'icon' => 'fas fa-gift'],
        ];
        $defaultColor = ['bg' => 'bg-slate-100 dark:bg-slate-800', 'text' => 'text-slate-500', 'icon' => 'fas fa-star'];
    @endphp

    <div class="space-y-8">
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Meus {{ $coinName }}</h1>
                <p class="mt-1 font-medium text-slate-500 dark:text-slate-400">Acompanhe seu saldo, a cotacao atual e sua evolucao no ranking.</p>
            </div>
            <a href="{{ route('panel.redemptions.shop') }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 font-bold text-white shadow-lg shadow-blue-500/20 transition-all active:scale-95 hover:bg-blue-700">
                <i class="fas fa-gift"></i>
                Trocar {{ $coinName }} por premios
            </a>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="flex items-center gap-5 rounded-3xl bg-gradient-to-br from-blue-600 to-blue-700 p-6 text-white shadow-lg shadow-blue-500/20">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20">
                    <i class="fas fa-coins text-2xl"></i>
                </div>
                <div>
                    <div class="text-3xl font-black leading-none">{{ number_format($user->points ?? 0, 0, ',', '.') }}</div>
                    <div class="mt-1 text-sm font-semibold text-blue-100">{{ $coinName }} acumulados</div>
                </div>
            </div>

            <div class="flex items-center gap-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-100 dark:bg-amber-900/40">
                    <i class="fas fa-trophy text-2xl text-amber-500"></i>
                </div>
                <div>
                    <div class="text-3xl font-black leading-none text-slate-900 dark:text-white">#{{ number_format($rankPosition, 0, ',', '.') }}</div>
                    <div class="mt-1 text-sm font-semibold text-slate-500">
                        no ranking geral
                        @if($totalRanked > 0)
                            <span class="text-xs text-slate-400">de {{ number_format($totalRanked, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-green-100 dark:bg-green-900/40">
                    <i class="fas fa-chart-line text-2xl text-green-500"></i>
                </div>
                <div>
                    <div class="text-3xl font-black leading-none text-slate-900 dark:text-white">{{ number_format($unnbitThisMonth, 0, ',', '.') }}</div>
                    <div class="mt-1 text-sm font-semibold text-slate-500">{{ $coinName }} em {{ now()->isoFormat('MMMM') }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-blue-100 bg-blue-50/70 px-6 py-4 text-sm text-blue-800 dark:border-blue-900/40 dark:bg-blue-950/20 dark:text-blue-200">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="font-black">Cotacao atual:</span>
                    1 {{ $coinName }} = R$ {{ number_format($unitValue, 4, ',', '.') }}
                </div>
                <div class="text-blue-700/80 dark:text-blue-300/80">
                    O saldo de membros e os resgates da plataforma usam {{ $coinName }} como moeda interna.
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-100 px-8 py-6 dark:border-slate-800">
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">Historico de {{ $coinName }}</h2>
                        <p class="mt-0.5 text-sm text-slate-400">Todas as acoes que movimentaram {{ $coinName }} para voce.</p>
                    </div>

                    @forelse($logs as $log)
                        @php
                            $rule = $rules->get($log->action_key);
                            $cat = $rule->category ?? null;
                            $colors = $categoryColors[$cat] ?? $defaultColor;
                            $icon = $rule->icon ?? $colors['icon'];
                            $label = $rule->label ?? ucfirst(str_replace('_', ' ', $log->action_key));
                        @endphp
                        <div class="flex items-center gap-4 border-b border-slate-50 px-8 py-5 transition-colors last:border-0 hover:bg-slate-50/50 dark:border-slate-800/60 dark:hover:bg-slate-800/20">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $colors['bg'] }}">
                                <i class="{{ $icon }} {{ $colors['text'] }}"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $label }}</div>
                                <div class="mt-0.5 text-xs text-slate-400">
                                    {{ $log->created_at->diffForHumans() }}
                                    &nbsp;&middot;&nbsp;
                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <span class="text-lg font-black text-blue-600 dark:text-blue-400">
                                    {{ $log->points > 0 ? '+' : '' }}{{ number_format($log->points, 0, ',', '.') }}
                                </span>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $coinName }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center px-8 py-20 text-center">
                            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                                <i class="fas fa-star text-2xl text-slate-300 dark:text-slate-600"></i>
                            </div>
                            <p class="font-bold text-slate-500 dark:text-slate-400">Nenhum {{ $coinName }} registrado ainda</p>
                            <p class="mt-1 max-w-xs text-sm text-slate-400">Realize acoes na plataforma para comecar a acumular {{ $coinName }}.</p>
                        </div>
                    @endforelse

                    @if($logs->hasPages())
                        <div class="border-t border-slate-100 px-8 py-5 dark:border-slate-800">
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-5">
                <div class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-800">
                        <h3 class="flex items-center gap-2 text-base font-black text-slate-900 dark:text-white">
                            <i class="fas fa-ranking-star text-amber-500"></i>
                            Top 10 Ranking
                        </h3>
                    </div>
                    <div class="divide-y divide-slate-50 dark:divide-slate-800/60">
                        @foreach($topUsers as $i => $topUser)
                            @php
                                $pos = $i + 1;
                                $isMe = $topUser->id === $user->id;
                                $medal = match($pos) {
                                    1 => 'bg-amber-400 text-white',
                                    2 => 'bg-slate-300 text-slate-700',
                                    3 => 'bg-amber-600 text-white',
                                    default => 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400',
                                };
                            @endphp
                            <div class="flex items-center gap-3 px-6 py-4 {{ $isMe ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors' }}">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl text-xs font-black {{ $medal }}">
                                    {{ $pos }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-bold text-slate-900 dark:text-white {{ $isMe ? 'text-blue-700 dark:text-blue-400' : '' }}">
                                        {{ $topUser->name }}
                                        @if($isMe)
                                            <span class="ml-1 rounded-full bg-blue-100 px-1.5 py-0.5 text-[10px] font-black text-blue-600 dark:bg-blue-900/50 dark:text-blue-400">Voce</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="shrink-0 text-sm font-black text-slate-700 dark:text-slate-300">
                                    {{ number_format($topUser->points ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach

                        @if($rankPosition > 10)
                            <div class="px-6 py-3 text-center text-xs text-slate-400">
                                <i class="fas fa-ellipsis-v mb-2 block"></i>
                            </div>
                            <div class="flex items-center gap-3 bg-blue-50 px-6 py-4 dark:bg-blue-900/20">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-xs font-black text-blue-600 dark:bg-blue-900/60 dark:text-blue-400">
                                    {{ $rankPosition }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-bold text-blue-700 dark:text-blue-400">
                                        {{ $user->name }}
                                        <span class="ml-1 rounded-full bg-blue-100 px-1.5 py-0.5 text-[10px] font-black text-blue-600 dark:bg-blue-900/50 dark:text-blue-400">Voce</span>
                                    </div>
                                </div>
                                <span class="shrink-0 text-sm font-black text-blue-700 dark:text-blue-400">
                                    {{ number_format($user->points ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-[2rem] border border-slate-700/50 bg-gradient-to-br from-slate-900 to-slate-800 p-6 text-white shadow-sm dark:from-slate-800 dark:to-slate-900">
                    <h3 class="mb-4 flex items-center gap-2 text-sm font-black"><i class="fas fa-bolt text-yellow-400"></i> Como ganhar mais {{ $coinName }}</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li class="flex items-start gap-2 text-slate-300">
                            <i class="fas fa-check-circle mt-0.5 shrink-0 text-green-400"></i>
                            Faca login <strong class="text-white">diariamente</strong> para bonus de streak
                        </li>
                        <li class="flex items-start gap-2 text-slate-300">
                            <i class="fas fa-check-circle mt-0.5 shrink-0 text-green-400"></i>
                            <strong class="text-white">Complete cursos</strong> e ganhe certificados
                        </li>
                        <li class="flex items-start gap-2 text-slate-300">
                            <i class="fas fa-check-circle mt-0.5 shrink-0 text-green-400"></i>
                            <strong class="text-white">Publique</strong> no feed e engaje a comunidade
                        </li>
                        <li class="flex items-start gap-2 text-slate-300">
                            <i class="fas fa-check-circle mt-0.5 shrink-0 text-green-400"></i>
                            <strong class="text-white">Indique amigos</strong> com seu link de referral
                        </li>
                        <li class="flex items-start gap-2 text-slate-300">
                            <i class="fas fa-check-circle mt-0.5 shrink-0 text-green-400"></i>
                            Entre no <strong class="text-white">Top 10</strong> e ganhe bonus semanal
                        </li>
                    </ul>
                    <a href="{{ route('panel.profile.edit') }}"
                       class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white transition-colors hover:bg-white/20">
                        <i class="fas fa-user-edit"></i> Completar meu perfil (+30 {{ $coinName }})
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
