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
        $hasLogs = $logs->count() > 0;
    @endphp

    <div class="space-y-6">
        {{-- Header com KPIs integrados --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 md:p-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Pontos</p>
                    <h1 class="mt-1 text-2xl font-black text-slate-900 dark:text-white">Meus {{ $coinName }}</h1>
                </div>
                <a href="{{ route('panel.redemptions.shop') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow hover:bg-blue-700 transition active:scale-95">
                    <i class="fas fa-gift"></i> Trocar por premios
                </a>
            </div>

            {{-- KPIs em linha --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 p-4 text-white">
                    <div class="text-2xl md:text-3xl font-black leading-none">{{ number_format($user->points ?? 0, 0, ',', '.') }}</div>
                    <div class="mt-1 text-xs font-bold text-blue-200">{{ $coinName }} total</div>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 p-4">
                    <div class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white leading-none">#{{ $rankPosition }}</div>
                    <div class="mt-1 text-xs font-bold text-slate-500">Ranking{{ $totalRanked > 0 ? ' de ' . $totalRanked : '' }}</div>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 p-4">
                    <div class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white leading-none">{{ number_format($unnbitThisMonth, 0, ',', '.') }}</div>
                    <div class="mt-1 text-xs font-bold text-slate-500">Este mes</div>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 p-4">
                    <div class="text-xs font-bold text-slate-500 mb-1">Cotacao</div>
                    <div class="text-sm font-black text-slate-900 dark:text-white">1 {{ $coinName }}</div>
                    <div class="text-xs font-bold text-blue-600 dark:text-blue-400">= R$ {{ number_format($unitValue, 4, ',', '.') }}</div>
                </div>
            </div>
        </div>

        {{-- Layout principal --}}
        <div class="grid grid-cols-1 gap-6 {{ $hasLogs ? 'lg:grid-cols-3' : '' }}">
            {{-- Historico --}}
            <div class="{{ $hasLogs ? 'lg:col-span-2' : '' }}">
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                    <div class="border-b border-slate-100 dark:border-slate-800 px-6 py-5 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-black text-slate-900 dark:text-white">Historico</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Movimentacoes de {{ $coinName }}</p>
                        </div>
                        @if($hasLogs)
                            <span class="text-xs font-bold text-slate-400 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full">
                                {{ $logs->total() }} {{ $logs->total() === 1 ? 'registro' : 'registros' }}
                            </span>
                        @endif
                    </div>

                    @forelse($logs as $log)
                        @php
                            $rule = $rules->get($log->action_key);
                            $cat = $rule->category ?? null;
                            $colors = $categoryColors[$cat] ?? $defaultColor;
                            $icon = $rule->icon ?? $colors['icon'];
                            $label = $rule->label ?? ucfirst(str_replace('_', ' ', $log->action_key));
                        @endphp
                        <div class="flex items-center gap-3 border-b border-slate-50 dark:border-slate-800/60 px-6 py-4 last:border-0 hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $colors['bg'] }}">
                                <i class="{{ $icon }} text-sm {{ $colors['text'] }}"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $label }}</div>
                                <div class="text-[11px] text-slate-400">{{ $log->created_at->diffForHumans() }}</div>
                            </div>
                            <span class="shrink-0 text-base font-black {{ $log->points > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                                {{ $log->points > 0 ? '+' : '' }}{{ number_format($log->points, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                                <i class="fas fa-coins text-2xl text-slate-300 dark:text-slate-600"></i>
                            </div>
                            <h3 class="font-black text-slate-700 dark:text-slate-200 mb-1">Comece a acumular {{ $coinName }}</h3>
                            <p class="text-sm text-slate-500 max-w-sm">Faca login diariamente, complete cursos, publique no feed e indique amigos para ganhar pontos.</p>
                            <div class="mt-6 flex flex-wrap gap-2 justify-center">
                                <a href="{{ route('panel.profile.edit') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-2 text-xs font-bold text-white transition">
                                    <i class="fas fa-user-edit"></i> Completar perfil (+30)
                                </a>
                                <a href="{{ route('panel.referral.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                    <i class="fas fa-user-plus"></i> Indicar amigos (+100)
                                </a>
                            </div>
                        </div>
                    @endforelse

                    @if($logs->hasPages())
                        <div class="border-t border-slate-100 dark:border-slate-800 px-6 py-4">
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar (Ranking + Dicas) --}}
            <div class="space-y-5">
                {{-- Top 10 --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
                    <div class="border-b border-slate-100 dark:border-slate-800 px-5 py-4">
                        <h3 class="flex items-center gap-2 text-sm font-black text-slate-900 dark:text-white">
                            <i class="fas fa-ranking-star text-amber-500"></i> Top 10
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
                            <div class="flex items-center gap-2.5 px-5 py-3 {{ $isMe ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors' }}">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-[10px] font-black {{ $medal }}">{{ $pos }}</span>
                                <div class="min-w-0 flex-1 truncate text-sm font-bold {{ $isMe ? 'text-blue-700 dark:text-blue-400' : 'text-slate-900 dark:text-white' }}">
                                    {{ Str::limit($topUser->name, 18) }}
                                    @if($isMe) <span class="text-[9px] text-blue-500">(voce)</span> @endif
                                </div>
                                <span class="shrink-0 text-xs font-black text-slate-600 dark:text-slate-300">{{ number_format($topUser->points ?? 0, 0, ',', '.') }}</span>
                            </div>
                        @endforeach

                        @if($rankPosition > 10)
                            <div class="px-5 py-2 text-center"><i class="fas fa-ellipsis text-slate-300 text-xs"></i></div>
                            <div class="flex items-center gap-2.5 px-5 py-3 bg-blue-50 dark:bg-blue-900/20">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-[10px] font-black text-blue-600 dark:bg-blue-900/60 dark:text-blue-400">{{ $rankPosition }}</span>
                                <div class="min-w-0 flex-1 truncate text-sm font-bold text-blue-700 dark:text-blue-400">{{ Str::limit($user->name, 18) }} <span class="text-[9px]">(voce)</span></div>
                                <span class="shrink-0 text-xs font-black text-blue-700 dark:text-blue-400">{{ number_format($user->points ?? 0, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Dicas --}}
                <div class="rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 dark:from-slate-800 dark:to-slate-900 border border-slate-700/50 p-5 text-white shadow-sm">
                    <h3 class="mb-3 flex items-center gap-2 text-xs font-black uppercase tracking-wider">
                        <i class="fas fa-bolt text-yellow-400"></i> Como ganhar {{ $coinName }}
                    </h3>
                    <ul class="space-y-2 text-[13px]">
                        <li class="flex items-start gap-2 text-slate-300"><i class="fas fa-check-circle mt-0.5 shrink-0 text-green-400 text-[10px]"></i> Login <strong class="text-white">diario</strong> = bonus streak</li>
                        <li class="flex items-start gap-2 text-slate-300"><i class="fas fa-check-circle mt-0.5 shrink-0 text-green-400 text-[10px]"></i> <strong class="text-white">Cursos</strong> completos = certificados</li>
                        <li class="flex items-start gap-2 text-slate-300"><i class="fas fa-check-circle mt-0.5 shrink-0 text-green-400 text-[10px]"></i> <strong class="text-white">Publicar</strong> no feed = engajamento</li>
                        <li class="flex items-start gap-2 text-slate-300"><i class="fas fa-check-circle mt-0.5 shrink-0 text-green-400 text-[10px]"></i> <strong class="text-white">Indicar</strong> amigos = +100 cada</li>
                        <li class="flex items-start gap-2 text-slate-300"><i class="fas fa-check-circle mt-0.5 shrink-0 text-green-400 text-[10px]"></i> <strong class="text-white">Top 10</strong> = bonus semanal</li>
                    </ul>
                    <a href="{{ route('panel.profile.edit') }}"
                       class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white transition-colors hover:bg-white/20">
                        <i class="fas fa-user-edit"></i> Completar perfil (+30 {{ $coinName }})
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
