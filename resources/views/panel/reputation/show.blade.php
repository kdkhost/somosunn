@extends('panel.layouts.app')

@section('title', 'Minha Reputacao')

@section('panel_breadcrumb')
    <span class="text-slate-400">Minha Reputacao</span>
@endsection

@section('panel_content')
    @php
        $score = (int) ($reputationData['score'] ?? 50);
        $badge = $reputationData['badge'] ?? ['label' => 'Regular', 'color' => '#22C55E', 'icon' => 'circle'];
        $dimensions = $reputationData['dimensions'] ?? [
            'delivery_rate' => 0,
            'relationship_score' => 0,
            'interaction_score' => 0,
            'engagement_score' => 0,
        ];
        $hasSellerStore = (bool) ($reputationData['has_seller_store'] ?? false);
        $calculatedAt = $reputationData['calculated_at'] ?? null;

        $deliveryRate = (float) ($dimensions['delivery_rate'] ?? 0);
        $relationshipScore = (float) ($dimensions['relationship_score'] ?? 0);
        $interactionScore = (float) ($dimensions['interaction_score'] ?? 0);
        $engagementScore = (float) ($dimensions['engagement_score'] ?? 0);

        $tips = [];
        if ($hasSellerStore && $deliveryRate < 50) {
            $tips[] = [
                'icon' => 'fa-truck',
                'color' => 'amber',
                'title' => 'Entrega',
                'text' => 'Entregue seus pedidos no prazo combinado para melhorar este indice.',
            ];
        }
        if ($relationshipScore < 50) {
            $tips[] = [
                'icon' => 'fa-handshake',
                'color' => 'rose',
                'title' => 'Relacionamento',
                'text' => 'Mantenha boas relacoes com outros membros e evite denuncias.',
            ];
        }
        if ($interactionScore < 50) {
            $tips[] = [
                'icon' => 'fa-comments',
                'color' => 'sky',
                'title' => 'Interacao',
                'text' => 'Participe mais da comunidade — poste, comente e participe de eventos.',
            ];
        }
        if ($engagementScore < 50) {
            $tips[] = [
                'icon' => 'fa-fire',
                'color' => 'orange',
                'title' => 'Engajamento',
                'text' => 'Faca login regularmente e complete cursos para aumentar seu engajamento.',
            ];
        }

        $historyData = $history->map(function ($h) {
            return [
                'date' => \Carbon\Carbon::parse($h->recorded_at)->format('d/m'),
                'fullDate' => \Carbon\Carbon::parse($h->recorded_at)->format('d/m/Y'),
                'score' => (int) $h->overall_score,
            ];
        })->values()->all();

        $maxHistoryScore = !empty($historyData) ? max(array_column($historyData, 'score')) : 100;
        $minHistoryScore = !empty($historyData) ? min(array_column($historyData, 'score')) : 0;
    @endphp

    <div class="space-y-6">
        {{-- HERO CARD --}}
        <div class="relative overflow-hidden rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800"
             style="background: linear-gradient(135deg, {{ $badge['color'] }}15 0%, {{ $badge['color'] }}05 100%);">
            <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full opacity-20 blur-3xl"
                 style="background-color: {{ $badge['color'] }};"></div>

            <div class="relative p-6 md:p-8">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                    {{-- Badge grande --}}
                    <div class="flex flex-col items-center text-center">
                        <div class="w-32 h-32 rounded-3xl flex items-center justify-center shadow-xl border-4"
                             style="background-color: {{ $badge['color'] }}; border-color: {{ $badge['color'] }}40;">
                            @php
                                $iconClass = match ($badge['icon'] ?? '') {
                                    'star' => 'fa-star',
                                    'shield' => 'fa-shield-alt',
                                    'circle' => 'fa-check-circle',
                                    'triangle' => 'fa-exclamation-triangle',
                                    'exclamation' => 'fa-exclamation-circle',
                                    default => 'fa-circle',
                                };
                            @endphp
                            <i class="fas {{ $iconClass }} text-white text-5xl"></i>
                        </div>
                        <div class="mt-3">
                            <div class="text-4xl font-black text-slate-900 dark:text-white">{{ $score }}</div>
                            <div class="text-xs font-bold uppercase tracking-widest mt-1"
                                 style="color: {{ $badge['color'] }};">{{ $badge['label'] }}</div>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Reputacao</p>
                        <h1 class="mt-1 text-2xl md:text-3xl font-black text-slate-900 dark:text-white">
                            Sua Reputacao na Plataforma
                        </h1>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 max-w-2xl">
                            Seu score de reputacao reflete sua confiabilidade na plataforma e e calculado a partir de
                            quatro dimensoes: Entrega, Relacionamento, Interacao e Engajamento.
                            @if(!$hasSellerStore)
                                <span class="block mt-1 text-xs italic">Voce nao possui loja, entao a dimensao "Entrega" nao se aplica e seu peso e redistribuido.</span>
                            @endif
                        </p>

                        {{-- Barra de progresso geral --}}
                        <div class="mt-5">
                            <div class="flex items-center justify-between text-xs font-bold mb-1">
                                <span class="text-slate-500">Score geral</span>
                                <span class="text-slate-900 dark:text-white">{{ $score }}/100</span>
                            </div>
                            <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500"
                                     style="width: {{ $score }}%; background: linear-gradient(90deg, {{ $badge['color'] }}, {{ $badge['color'] }}cc);"></div>
                            </div>
                        </div>

                        @if($calculatedAt)
                            <p class="mt-3 text-[11px] text-slate-400">
                                <i class="fas fa-clock"></i>
                                Ultimo calculo: {{ \Carbon\Carbon::parse($calculatedAt)->diffForHumans() }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- DIMENSOES --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 md:p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-black text-slate-900 dark:text-white">Detalhamento por Dimensao</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Cada dimensao contribui com um peso para o score final</p>
                </div>
            </div>

            <div class="space-y-5">
                {{-- Entrega --}}
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 p-5 {{ !$hasSellerStore ? 'opacity-60' : '' }}">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <i class="fas fa-truck text-amber-600 dark:text-amber-400"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">Entrega</p>
                                <p class="text-[11px] text-slate-400">Peso: 40% (apenas vendedores)</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-black text-slate-900 dark:text-white">
                                {{ number_format($deliveryRate, 0) }}<span class="text-sm font-bold text-slate-400">/100</span>
                            </div>
                        </div>
                    </div>
                    <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bg-amber-500 transition-all duration-500"
                             style="width: {{ max(0, min(100, $deliveryRate)) }}%;"></div>
                    </div>
                    @if(!$hasSellerStore)
                        <p class="mt-2 text-[11px] text-slate-400 italic">Nao se aplica — voce nao possui loja.</p>
                    @endif
                </div>

                {{-- Relacionamento --}}
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                                <i class="fas fa-handshake text-rose-600 dark:text-rose-400"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">Relacionamento</p>
                                <p class="text-[11px] text-slate-400">Peso: 25%</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-black text-slate-900 dark:text-white">
                                {{ number_format($relationshipScore, 0) }}<span class="text-sm font-bold text-slate-400">/100</span>
                            </div>
                        </div>
                    </div>
                    <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bg-rose-500 transition-all duration-500"
                             style="width: {{ max(0, min(100, $relationshipScore)) }}%;"></div>
                    </div>
                </div>

                {{-- Interacao --}}
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center">
                                <i class="fas fa-comments text-sky-600 dark:text-sky-400"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">Interacao</p>
                                <p class="text-[11px] text-slate-400">Peso: 20%</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-black text-slate-900 dark:text-white">
                                {{ number_format($interactionScore, 0) }}<span class="text-sm font-bold text-slate-400">/100</span>
                            </div>
                        </div>
                    </div>
                    <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bg-sky-500 transition-all duration-500"
                             style="width: {{ max(0, min(100, $interactionScore)) }}%;"></div>
                    </div>
                </div>

                {{-- Engajamento --}}
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                <i class="fas fa-fire text-orange-600 dark:text-orange-400"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">Engajamento</p>
                                <p class="text-[11px] text-slate-400">Peso: 15%</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-black text-slate-900 dark:text-white">
                                {{ number_format($engagementScore, 0) }}<span class="text-sm font-bold text-slate-400">/100</span>
                            </div>
                        </div>
                    </div>
                    <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bg-orange-500 transition-all duration-500"
                             style="width: {{ max(0, min(100, $engagementScore)) }}%;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DICAS --}}
        @if(count($tips) > 0)
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 md:p-8">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <i class="fas fa-lightbulb text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Dicas para Melhorar</h2>
                        <p class="text-xs text-slate-400">Acoes para aumentar sua reputacao</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($tips as $tip)
                        @php
                            $colorMap = [
                                'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'border' => 'border-amber-200 dark:border-amber-800', 'text' => 'text-amber-700 dark:text-amber-300', 'icon' => 'text-amber-600 dark:text-amber-400'],
                                'rose' => ['bg' => 'bg-rose-50 dark:bg-rose-900/20', 'border' => 'border-rose-200 dark:border-rose-800', 'text' => 'text-rose-700 dark:text-rose-300', 'icon' => 'text-rose-600 dark:text-rose-400'],
                                'sky' => ['bg' => 'bg-sky-50 dark:bg-sky-900/20', 'border' => 'border-sky-200 dark:border-sky-800', 'text' => 'text-sky-700 dark:text-sky-300', 'icon' => 'text-sky-600 dark:text-sky-400'],
                                'orange' => ['bg' => 'bg-orange-50 dark:bg-orange-900/20', 'border' => 'border-orange-200 dark:border-orange-800', 'text' => 'text-orange-700 dark:text-orange-300', 'icon' => 'text-orange-600 dark:text-orange-400'],
                            ];
                            $c = $colorMap[$tip['color']] ?? $colorMap['sky'];
                        @endphp
                        <div class="rounded-2xl border {{ $c['border'] }} {{ $c['bg'] }} p-4">
                            <div class="flex items-start gap-3">
                                <i class="fas {{ $tip['icon'] }} {{ $c['icon'] }} mt-1"></i>
                                <div class="flex-1">
                                    <p class="font-bold text-slate-900 dark:text-white text-sm mb-1">{{ $tip['title'] }}</p>
                                    <p class="text-[13px] {{ $c['text'] }}">{{ $tip['text'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-3xl p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500 flex items-center justify-center shrink-0">
                        <i class="fas fa-check text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="font-black text-emerald-900 dark:text-emerald-200">Otimo trabalho!</p>
                        <p class="text-sm text-emerald-700 dark:text-emerald-300">Suas dimensoes estao saudaveis. Continue assim para manter sua reputacao alta.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- HISTORICO --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 md:p-8">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                    <i class="fas fa-chart-line text-indigo-600 dark:text-indigo-400"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 dark:text-white">Historico (Ultimos 6 meses)</h2>
                    <p class="text-xs text-slate-400">Evolucao do seu score ao longo do tempo</p>
                </div>
            </div>

            @if(count($historyData) > 0)
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 p-5 bg-slate-50/50 dark:bg-slate-950/30">
                    <div class="flex items-end justify-between gap-1 md:gap-2" style="height: 200px;">
                        @foreach($historyData as $point)
                            @php
                                $heightPct = max(2, min(100, ($point['score'] / 100) * 100));
                                $color = $point['score'] >= 90 ? '#FFD700' : ($point['score'] >= 70 ? '#1F5EDB' : ($point['score'] >= 50 ? '#22C55E' : ($point['score'] >= 30 ? '#F59E0B' : '#EF4444')));
                            @endphp
                            <div class="flex-1 flex flex-col items-center justify-end group relative min-w-0">
                                <div class="absolute -top-8 left-1/2 -translate-x-1/2 hidden group-hover:block z-10">
                                    <div class="bg-slate-900 text-white text-[11px] font-bold px-2 py-1 rounded-md whitespace-nowrap shadow-lg">
                                        {{ $point['fullDate'] }} - {{ $point['score'] }}
                                    </div>
                                </div>
                                <div class="w-full rounded-t-md transition-all duration-300 hover:opacity-80 cursor-default"
                                     style="height: {{ $heightPct }}%; background-color: {{ $color }};"
                                     title="{{ $point['fullDate'] }}: {{ $point['score'] }}/100">
                                </div>
                                <div class="mt-2 text-[10px] font-bold text-slate-500 dark:text-slate-400 truncate w-full text-center">
                                    {{ $point['date'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span><i class="fas fa-arrow-down text-rose-500"></i> Min: {{ $minHistoryScore }}</span>
                        <span><i class="fas fa-arrow-up text-emerald-500"></i> Max: {{ $maxHistoryScore }}</span>
                        <span class="font-bold">{{ count($historyData) }} {{ count($historyData) === 1 ? 'registro' : 'registros' }}</span>
                    </div>
                </div>
            @else
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 p-10 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                        <i class="fas fa-chart-line text-2xl text-slate-300 dark:text-slate-600"></i>
                    </div>
                    <h3 class="font-black text-slate-700 dark:text-slate-200 mb-1">Sem historico ainda</h3>
                    <p class="text-sm text-slate-500 max-w-md mx-auto">
                        O historico do seu score sera registrado a cada calculo. Continue ativo na plataforma para ver sua evolucao aqui.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection
