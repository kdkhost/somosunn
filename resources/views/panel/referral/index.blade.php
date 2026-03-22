@extends('panel.layouts.app')

@section('title', 'Programa de Indicações')

@section('panel_content')
@php
    $pointsRule = \App\Models\PointsRule::where('key', 'referral')->where('active', true)->first();
    $pointsPerReferral = $pointsRule?->points ?? 0;
    $conversionRate = $totalReferred > 0 ? round(($convertedCount / $totalReferred) * 100) : 0;
    $potentialPoints = $pendingCount * $pointsPerReferral;
    $trackingConversion = $trackingSummary['registration_conversion'] ?? 0;
    $purchaseConversion = $trackingSummary['purchase_conversion'] ?? 0;
    $activePanelTab = request('tab');

    if ($errors->has('device_name') || $errors->has('api_tokens') || $apiTokenPlainText) {
        $activePanelTab = 'api';
    } elseif ($errors->has('sandbox') || $errors->has('reason') || $errors->has('requested_domain') || $errors->has('requested_ip')) {
        $activePanelTab = 'materiais';
    } elseif (!in_array($activePanelTab, ['programa', 'api', 'materiais', 'rastreio'], true)) {
        $activePanelTab = 'programa';
    }
@endphp

<div class="space-y-8">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Programa de Indicações</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1 font-medium">
                Compartilhe seu link. Quando alguém que você indicou <strong class="text-slate-700 dark:text-slate-300">assinar um plano pago</strong>, você recebe pontos automaticamente.
            </p>
        </div>
        @if($pointsPerReferral && $pendingCount > 0)
            <div class="shrink-0 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl px-5 py-3 text-center">
                <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold uppercase tracking-wide">Potencial pendente</p>
                <p class="text-2xl font-black text-amber-700 dark:text-amber-300">+{{ number_format($potentialPoints) }} pts</p>
                <p class="text-xs text-amber-600/80 dark:text-amber-500">de {{ $pendingCount }} indicado{{ $pendingCount != 1 ? 's' : '' }} sem plano</p>
            </div>
        @endif
    </div>

    <div class="rounded-3xl border border-white/60 dark:border-slate-800/60 bg-white/70 dark:bg-slate-900/70 backdrop-blur-3xl p-2 shadow-sm">
        <div class="grid gap-2 md:grid-cols-4">
            <button type="button" data-panel-referral-tab-target="programa" class="flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-black transition {{ $activePanelTab === 'programa' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <i class="fas fa-link text-sm"></i>
                <span>Programa</span>
            </button>
            <button type="button" data-panel-referral-tab-target="api" class="flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-black transition {{ $activePanelTab === 'api' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <i class="fas fa-key text-sm"></i>
                <span>API pessoal</span>
            </button>
            <button type="button" data-panel-referral-tab-target="materiais" class="flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-black transition {{ $activePanelTab === 'materiais' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <i class="fas fa-box-open text-sm"></i>
                <span>Materiais e sandbox</span>
            </button>
            <button type="button" data-panel-referral-tab-target="rastreio" class="flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-black transition {{ $activePanelTab === 'rastreio' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <i class="fas fa-chart-line text-sm"></i>
                <span>Rastreio completo</span>
            </button>
        </div>
    </div>

    <div data-panel-referral-tab-panel="programa" class="space-y-8 {{ $activePanelTab === 'programa' ? '' : 'hidden' }}">

    @if(false)
        <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl border border-white/50 dark:border-slate-800/60 rounded-3xl p-6 md:p-8 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] hover:-translate-y-1 transition-all duration-500 space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white">Rastreio completo do link</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Cliques, visitas únicas, cadastros, checkouts, compras confirmadas e compartilhamentos.
                    </p>
                </div>
                <div id="trackingConversionMeta" class="text-sm text-slate-500 dark:text-slate-400">
                    Conversão para cadastro: <strong class="text-slate-700 dark:text-slate-200">{{ $trackingConversion }}%</strong>
                    · Compra: <strong class="text-slate-700 dark:text-slate-200">{{ $purchaseConversion }}%</strong>
                    · Atualizado às <strong id="trackingUpdatedAtLabel" class="text-slate-700 dark:text-slate-200">{{ $trackingUpdatedAtLabel }}</strong>
                </div>
            </div>

            <div id="trackingStatusBanner"
                class="rounded-2xl px-4 py-3 text-sm font-medium {{ $trackingStatusTone === 'success'
                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300'
                    : 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300' }}">
                {{ $trackingStatusMessage }}
            </div>

            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Cliques no link</p>
                    <p id="trackingClicksValue" class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ number_format($trackingSummary['clicks']) }}</p>
                    <p id="trackingClicksMeta" class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($trackingSummary['visits']) }} visitas únicas · {{ number_format($trackingSummary['pageviews']) }} visualizações</p>
                </div>
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Cadastros atribuídos</p>
                    <p id="trackingRegistrationsValue" class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ number_format($trackingSummary['registrations']) }}</p>
                    <p id="trackingRegistrationsMeta" class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($trackingSummary['checkout_starts']) }} checkouts iniciados · {{ $trackingConversion }}% de conversão</p>
                </div>
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Compras confirmadas</p>
                    <p id="trackingPurchasesValue" class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ number_format($trackingSummary['purchases']) }}</p>
                    <p id="trackingRevenueMeta" class="mt-1 text-xs text-slate-500 dark:text-slate-400">R$ {{ number_format($trackingSummary['revenue'], 2, ',', '.') }} rastreados · {{ $purchaseConversion }}% de conversão</p>
                </div>
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Compartilhamentos</p>
                    <p id="trackingSharesValue" class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ number_format($trackingSummary['shares'] + $trackingSummary['reshares'] + $trackingSummary['copies']) }}</p>
                    <p id="trackingSharesMeta" class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ number_format($trackingSummary['shares']) }} novos · {{ number_format($trackingSummary['reshares']) }} reenvios · {{ number_format($trackingSummary['copies']) }} cópias
                    </p>
                </div>
            </div>

            <div class="grid xl:grid-cols-[1.7fr,1fr] gap-6">
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/40 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white">Performance diária</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Últimos 14 dias com visitas, cadastros, checkouts, compras e receita.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900/30 px-3 py-1 text-xs font-bold text-blue-700 dark:text-blue-300">14 dias</span>
                    </div>
                    <div class="mt-5 h-[320px]">
                        <canvas id="referralDailyChart"></canvas>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/40 p-5">
                        <h3 class="font-black text-slate-900 dark:text-white">Aquisição por canal</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Origem das visitas, registros e compras atribuídas.</p>
                        <div class="mt-5 h-[320px]">
                            <canvas id="referralAcquisitionChart"></canvas>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/40 p-5">
                        <h3 class="font-black text-slate-900 dark:text-white">Distribuição de compartilhamentos</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Leitura visual dos canais que mais espalham seu link.</p>
                        <div class="mt-5 h-[260px]">
                            <canvas id="referralSharingChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-[1.6fr,1fr] gap-6">
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="font-black text-slate-900 dark:text-white">Últimas visitas atribuídas</h3>
                    </div>
                    <div id="trackingVisitsEmpty" class="px-5 py-8 text-sm text-slate-500 dark:text-slate-400 {{ $trackedVisits->isEmpty() ? '' : 'hidden' }}">
                        Ainda não há visitas rastreadas para este link.
                    </div>
                    <div id="trackingVisitsTableWrapper" class="overflow-x-auto {{ $trackedVisits->isEmpty() ? 'hidden' : '' }}">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-widest">
                                    <tr>
                                        <th class="text-left px-5 py-3">Visita</th>
                                        <th class="text-left px-5 py-3">Origem</th>
                                        <th class="text-left px-5 py-3">Cadastro</th>
                                        <th class="text-right px-5 py-3">Compra</th>
                                    </tr>
                                </thead>
                                <tbody id="trackingVisitsRows" class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($trackedVisits as $visit)
                                        @php
                                            $sourceLabel = $visit->utm_source ?: ($visit->referrer_url ? parse_url($visit->referrer_url, PHP_URL_HOST) : 'direto');
                                        @endphp
                                        <tr>
                                            <td class="px-5 py-4">
                                                <p class="font-semibold text-slate-900 dark:text-white">{{ $visit->first_visited_at?->format('d/m/Y H:i') ?? '—' }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ number_format($visit->clicks_count) }} clique(s) · {{ number_format($visit->pageviews_count) }} página(s)
                                                </p>
                                            </td>
                                            <td class="px-5 py-4">
                                                <p class="font-medium text-slate-700 dark:text-slate-300">{{ $sourceLabel ?: 'direto' }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[220px]">{{ $visit->landing_page_path ?: '/' }}</p>
                                            </td>
                                            <td class="px-5 py-4">
                                                @if($visit->registeredUser)
                                                    <p class="font-medium text-slate-900 dark:text-white">{{ $visit->registeredUser->name }}</p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $visit->registered_at?->diffForHumans() ?? 'cadastrado' }}</p>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:text-slate-400">Sem cadastro</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                @if($visit->purchases_count > 0)
                                                    <p class="font-semibold text-emerald-600 dark:text-emerald-400">{{ number_format($visit->purchases_count) }} compra(s)</p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400">R$ {{ number_format((float) $visit->total_revenue_amount, 2, ',', '.') }}</p>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:text-slate-400">Sem compra</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
                    <h3 class="font-black text-slate-900 dark:text-white">Canais mais usados</h3>
                    <div id="trackingChannelsList" class="mt-4 space-y-3">
                        @forelse($trackingChannels as $channel)
                            <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $channel->channel }}</span>
                                    <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900/30 px-3 py-1 text-xs font-bold text-blue-700 dark:text-blue-300">{{ number_format($channel->total) }}</span>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <span>{{ number_format($channel->shares) }} novos</span>
                                    <span>·</span>
                                    <span>{{ number_format($channel->reshares) }} reenvios</span>
                                    <span>·</span>
                                    <span>{{ number_format($channel->copies) }} cópias</span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 px-4 py-6 text-sm text-slate-500 dark:text-slate-400">
                                Os compartilhamentos começam a aparecer aqui assim que você usar os botões rápidos ou copiar seu link.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-5 space-y-4">
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Receita rastreada</p>
                            <p id="trackingRevenueCardValue" class="mt-2 text-2xl font-black text-slate-900 dark:text-white">R$ {{ number_format($trackingSummary['revenue'], 2, ',', '.') }}</p>
                            <p id="trackingPurchasesCardValue" class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ number_format($trackingSummary['purchases']) }} compra(s) confirmada(s)</p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Melhor canal atual</p>
                            @php $topChannel = $trackingChannels->first(); @endphp
                            <p id="trackingTopChannelLabel" class="mt-2 {{ $topChannel ? 'text-xl font-black text-slate-900 dark:text-white' : 'text-sm font-semibold text-slate-500 dark:text-slate-400' }}">
                                {{ $topChannel->channel ?? 'Sem ações compartilhadas' }}
                            </p>
                            <p id="trackingTopChannelMeta" class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ $topChannel ? number_format($topChannel->total) . ' ação(ões) registradas' : 'Use os botões rápidos ou copie o link para começar a medir os canais.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div id="referralChannelFunnelSection">
    @include('panel.referral.partials.channel-funnel', [
        'channelFunnels' => $channelFunnels,
        'title' => 'Funil por canal e origem',
        'subtitle' => 'Veja de onde vieram os cliques, quantas visualizações cada origem gerou e onde realmente converte.',
    ])
    </div>

    <div id="referralEventsLogSection">
    @include('panel.referral.partials.events-log', [
        'detailedEvents' => $detailedEvents,
        'exportUrl' => route('panel.referral.export'),
        'title' => 'Log detalhado de cliques, visitas e compartilhamentos',
        'subtitle' => 'Inclui URL de origem exata, landing page, dispositivo, navegador, cidade/país e o resultado de cada ação.',
        'emptyMessage' => 'Ainda não há cliques, visitas ou compartilhamentos detalhados para este afiliado.',
    ])
    </div>
    @endif

    {{-- ===== CARDS RESUMO ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

        {{-- Total indicados --}}
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl sm:rounded-3xl p-4 sm:p-5 text-white shadow-lg shadow-blue-500/20 flex items-center gap-3 hover:-translate-y-1 hover:shadow-[0_20px_50px_rgba(37,99,235,0.3)] transition-all duration-300 relative overflow-hidden group">
            <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-user-plus text-lg sm:text-xl"></i>
            </div>
            <div class="min-w-0">
                <p class="text-white/80 text-xs font-semibold truncate">Total indicados</p>
                <p class="text-2xl sm:text-3xl font-black leading-tight">{{ $totalReferred }}</p>
            </div>
        </div>

        {{-- Convertidos (pagaram) --}}
        <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl sm:rounded-3xl p-4 sm:p-5 text-white shadow-lg shadow-emerald-500/20 flex items-center gap-3 hover:-translate-y-1 hover:shadow-[0_20px_50px_rgba(16,185,129,0.3)] transition-all duration-300 relative overflow-hidden group">
            <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-check-circle text-lg sm:text-xl"></i>
            </div>
            <div class="min-w-0">
                <p class="text-white/80 text-xs font-semibold truncate">Convertidos</p>
                <p class="text-2xl sm:text-3xl font-black leading-tight">{{ $convertedCount }}</p>
                @if($totalReferred > 0)
                    <p class="text-white/70 text-xs">{{ $conversionRate }}%</p>
                @endif
            </div>
        </div>

        {{-- Pontos ganhos com indicações --}}
        <div class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl sm:rounded-3xl p-4 sm:p-5 text-white shadow-lg shadow-amber-500/20 flex items-center gap-3 hover:-translate-y-1 hover:shadow-[0_20px_50px_rgba(245,158,11,0.3)] transition-all duration-300 relative overflow-hidden group">
            <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-coins text-lg sm:text-xl"></i>
            </div>
            <div class="min-w-0">
                <p class="text-white/80 text-xs font-semibold truncate">Pontos ganhos</p>
                <p class="text-2xl sm:text-3xl font-black leading-tight">{{ number_format($totalReferralPoints) }}</p>
            </div>
        </div>

        {{-- Pontos por indicação --}}
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl border border-white/60 dark:border-slate-800/60 rounded-2xl sm:rounded-3xl p-4 sm:p-5 flex items-center gap-3 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:-translate-y-1 hover:shadow-[0_20px_50px_rgba(37,99,235,0.08)] transition-all duration-300 relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-transparent to-green-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 dark:bg-green-900/40 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-gift text-green-600 dark:text-green-400 text-lg sm:text-xl"></i>
            </div>
            <div class="min-w-0">
                <p class="text-slate-500 dark:text-slate-400 text-xs font-semibold truncate">Por indicação</p>
                @if($pointsPerReferral)
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white leading-tight">+{{ $pointsPerReferral }}</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">pontos</p>
                @else
                    <p class="text-sm font-bold text-slate-400 dark:text-slate-500 leading-tight">Não config.</p>
                @endif
            </div>
        </div>

    </div>

    {{-- ===== SEU LINK DE INDICAÇÃO ===== --}}
    <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl border border-white/50 dark:border-slate-800/60 rounded-3xl p-6 md:p-8 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] hover:-translate-y-1 transition-all duration-500 overflow-hidden relative group/link">
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-3xl group-hover/link:bg-blue-500/10 transition-all duration-700 pointer-events-none"></div>
        <div class="relative z-10">
        <h2 class="text-xl font-black text-slate-900 dark:text-white mb-1">Seu link de indicação</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Compartilhe este link. O sistema registra automaticamente quem entrou pelo seu convite.</p>

        {{-- Input + Copiar --}}
        <div class="flex items-center gap-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2.5 mb-6 overflow-hidden">
            <i class="fas fa-link text-slate-400 shrink-0 text-sm"></i>
            <input id="referralLinkInput" type="text" readonly
                   value="{{ $referralLink }}"
                   class="flex-1 min-w-0 bg-transparent text-sm font-mono text-slate-700 dark:text-slate-300 outline-none truncate">
            <button onclick="copyReferralLink()"
                    id="copyBtn"
                    class="shrink-0 flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-sm font-bold px-3 py-2 rounded-xl transition-all whitespace-nowrap">
                <i class="fas fa-copy" id="copyIcon"></i>
                <span id="copyText" class="hidden sm:inline">Copiar</span>
            </button>
        </div>

        {{-- Compartilhamento rápido --}}
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 sm:gap-3 mb-6">
            <a href="https://wa.me/?text={{ urlencode('Ei! Faça parte da maior comunidade de empreendedores e networking do Brasil. Use meu link: ' . $referralLink) }}"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-all active:scale-95">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
            <a href="https://t.me/share/url?url={{ urlencode($referralLink) }}&text={{ urlencode('Entre na plataforma com meu convite e comece a fazer networking!') }}"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-2 bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-all active:scale-95">
                <i class="fab fa-telegram"></i> Telegram
            </a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($referralLink) }}"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-all active:scale-95">
                <i class="fab fa-linkedin"></i> LinkedIn
            </a>
            <a href="mailto:?subject={{ urlencode('Convite para a comunidade UNN') }}&body={{ urlencode('Olá! Quero te convidar para a maior plataforma de networking para empreendedores. Acesse: ' . $referralLink) }}"
               class="inline-flex items-center justify-center gap-2 bg-slate-600 hover:bg-slate-700 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-all active:scale-95">
                <i class="fas fa-envelope"></i> E-mail
            </a>
        </div>

        {{-- Código + aviso --}}
        <div class="flex flex-wrap items-center gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
            <div>
                <p class="text-xs text-slate-400 dark:text-slate-500 mb-1">Seu código único</p>
                <code class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono text-sm px-3 py-1 rounded-lg">{{ $user->referral_code }}</code>
            </div>
            <div class="h-8 w-px bg-slate-200 dark:bg-slate-700 hidden sm:block"></div>
            <p class="text-xs text-slate-400 dark:text-slate-500">
                <i class="fas fa-info-circle mr-1 text-blue-400"></i>
                Pontos são creditados somente após o indicado <strong class="text-slate-600 dark:text-slate-400">assinar um plano pago</strong>.
            </p>
        </div>
    </div>
    </div>

    {{-- ===== LISTA DE INDICADOS ===== --}}
    <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl border border-white/50 dark:border-slate-800/60 rounded-3xl shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] hover:-translate-y-1 transition-all duration-500 overflow-hidden relative group/users">
        <div class="absolute top-0 right-0 w-40 h-40 bg-emerald-500/5 rounded-full -mr-20 -mt-20 blur-3xl group-hover/users:bg-emerald-500/10 transition-all duration-700 pointer-events-none"></div>
        <div class="relative z-10">
        <div class="p-6 md:p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-900 dark:text-white">Pessoas que você indicou</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                    Cadastros realizados com seu link. &nbsp;
                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $convertedCount }} convertido{{ $convertedCount != 1 ? 's' : '' }}</span>
                    &nbsp;·&nbsp;
                    <span class="text-slate-500">{{ $pendingCount }} aguardando pagamento</span>
                </p>
            </div>
            @if($totalReferred > 0)
                <div class="shrink-0 text-right hidden sm:block">
                    <p class="text-xs text-slate-400 mb-1">Taxa de conversão</p>
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $conversionRate }}%"></div>
                        </div>
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $conversionRate }}%</span>
                    </div>
                </div>
            @endif
        </div>

        @if($referredUsers->isEmpty())
            <div class="p-10 text-center">
                <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-plus text-slate-400 text-2xl"></i>
                </div>
                <p class="text-slate-500 dark:text-slate-400 font-semibold mb-1">Nenhuma indicação ainda</p>
                <p class="text-slate-400 dark:text-slate-500 text-sm">Compartilhe seu link para que amigos se cadastrem e você comece a ganhar pontos!</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-widest">
                        <tr>
                            <th class="text-left px-3 py-3 sm:px-6 sm:py-4">Membro indicado</th>
                            <th class="text-left px-3 py-3 sm:px-6 sm:py-4 hidden md:table-cell">Cadastro</th>
                            <th class="text-left px-3 py-3 sm:px-6 sm:py-4 hidden sm:table-cell">Plano / Status</th>
                            <th class="text-right px-3 py-3 sm:px-6 sm:py-4 whitespace-nowrap">Pontos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($referredUsers as $referred)
                            @php
                                $logsFromUser = $referralPointsLogs->filter(function($l) use ($referred) {
                                    $meta = json_decode($l->meta ?? '{}', true);
                                    return ($meta['new_user_id'] ?? null) == $referred->id;
                                });
                                $pointsFromThisUser = $logsFromUser->sum('points');

                                // Status do plano
                                if ($referred->plan_id) {
                                    if (!$referred->plan_expires_at) {
                                        $planStatus = ['label' => 'Vitalício', 'dot' => 'bg-purple-500', 'class' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400'];
                                    } elseif (\Carbon\Carbon::parse($referred->plan_expires_at)->isFuture()) {
                                        $planStatus = ['label' => 'Assinante ativo', 'dot' => 'bg-emerald-500', 'class' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'];
                                    } else {
                                        $planStatus = ['label' => 'Plano expirado', 'dot' => 'bg-yellow-500', 'class' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'];
                                    }
                                    $planName = $plansMap[$referred->plan_id] ?? 'Plano';
                                } else {
                                    $planStatus = ['label' => 'Sem plano', 'dot' => 'bg-slate-400', 'class' => 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'];
                                    $planName = null;
                                }
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-3 py-3 sm:px-6 sm:py-4 max-w-[40vw] sm:max-w-none">
                                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 flex items-center justify-center">
                                            @if($referred->photo)
                                                <img src="{{ asset($referred->photo) }}" alt="{{ $referred->name }}" class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-user text-slate-400 text-xs"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 dark:text-white truncate text-xs sm:text-sm">{{ $referred->name }}</p>
                                            <p class="text-xs text-slate-400 truncate">{{ $referred->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 sm:px-6 sm:py-4 text-slate-500 dark:text-slate-400 hidden md:table-cell">
                                    <p class="text-xs">{{ $referred->created_at->format('d/m/Y') }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $referred->created_at->diffForHumans() }}</p>
                                </td>
                                <td class="px-3 py-3 sm:px-6 sm:py-4 hidden sm:table-cell">
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex w-fit items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $planStatus['class'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $planStatus['dot'] }} shrink-0"></span>
                                            {{ $planStatus['label'] }}
                                        </span>
                                        @if($planName)
                                            <span class="text-xs text-slate-400 dark:text-slate-500">{{ $planName }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 sm:px-6 sm:py-4 text-right">
                                    @if($pointsFromThisUser > 0)
                                        <div class="flex flex-col items-end gap-1">
                                            <span class="inline-flex items-center gap-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-bold px-2 sm:px-3 py-1 rounded-full whitespace-nowrap">
                                                <i class="fas fa-coins"></i> +{{ number_format($pointsFromThisUser) }}
                                            </span>
                                            <span class="text-xs text-slate-400 hidden sm:block">{{ $logsFromUser->count() }} pag.</span>
                                        </div>
                                    @elseif($referred->plan_id)
                                        <span class="text-xs text-yellow-600 dark:text-yellow-500 font-medium whitespace-nowrap">Aguardando</span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-600 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($referredUsers->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $referredUsers->links() }}
                </div>
            @endif
        </div>
    </div>
    </div>

    {{-- ===== HISTÓRICO DE GANHOS ===== --}}
    @if($referralPointsLogs->isNotEmpty())
    <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl border border-white/50 dark:border-slate-800/60 rounded-3xl shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] hover:-translate-y-1 transition-all duration-500 overflow-hidden relative group/history">
        <div class="absolute top-0 right-0 w-40 h-40 bg-amber-500/5 rounded-full -mr-20 -mt-20 blur-3xl group-hover/history:bg-amber-500/10 transition-all duration-700 pointer-events-none"></div>
        <div class="relative z-10">
        <div class="p-6 md:p-8 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-xl font-black text-slate-900 dark:text-white">Histórico de ganhos</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Cada linha corresponde a um pagamento de plano confirmado de um indicado seu.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-widest">
                    <tr>
                        <th class="text-left px-3 py-3 sm:px-6 sm:py-4">Indicado</th>
                        <th class="text-left px-3 py-3 sm:px-6 sm:py-4 hidden md:table-cell">Plano</th>
                        <th class="text-left px-3 py-3 sm:px-6 sm:py-4 hidden lg:table-cell">Pedido</th>
                        <th class="text-left px-3 py-3 sm:px-6 sm:py-4 hidden sm:table-cell">Data</th>
                        <th class="text-right px-3 py-3 sm:px-6 sm:py-4">Pontos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($referralPointsLogs as $log)
                        @php $meta = json_decode($log->meta ?? '{}', true); @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-3 py-3 sm:px-6 sm:py-4 max-w-[40vw] sm:max-w-none">
                                <p class="font-semibold text-slate-900 dark:text-white truncate text-xs sm:text-sm">{{ $meta['new_user_name'] ?? '—' }}</p>
                                <p class="text-xs text-slate-400 sm:hidden">{{ optional($log->created_at)->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-slate-500 dark:text-slate-400 hidden md:table-cell text-xs">
                                {{ $meta['plan_name'] ?? '—' }}
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 hidden lg:table-cell">
                                @if(isset($meta['order_id']))
                                    <code class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs px-2 py-0.5 rounded">#{{ $meta['order_id'] }}</code>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-slate-500 dark:text-slate-400 hidden sm:table-cell">
                                <p class="text-xs whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                                <p class="text-xs text-slate-400">{{ $log->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-right">
                                <span class="inline-flex items-center gap-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-bold px-2 sm:px-3 py-1 rounded-full whitespace-nowrap">
                                    <i class="fas fa-coins"></i> +{{ number_format($log->points) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 dark:bg-slate-800 border-t border-slate-100 dark:border-slate-700">
                        <td colspan="2" class="px-3 py-3 sm:px-6 sm:py-4 text-sm font-bold text-slate-700 dark:text-slate-300">Total acumulado</td>
                        <td class="px-3 py-3 sm:px-6 sm:py-4 text-right">
                            <span class="inline-flex items-center gap-1 bg-amber-200 dark:bg-amber-800/50 text-amber-800 dark:text-amber-300 text-sm font-black px-3 py-1 rounded-full whitespace-nowrap">
                                <i class="fas fa-coins"></i> +{{ number_format($totalReferralPoints) }}
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    </div>
    @endif

    {{-- ===== COMO FUNCIONA ===== --}}
    <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl border border-white/50 dark:border-slate-800/60 rounded-3xl shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] hover:-translate-y-1 transition-all duration-500 p-6 md:p-8 relative overflow-hidden group/how">
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-blue-500/5 rounded-full -ml-20 -mb-20 blur-3xl group-hover/how:bg-blue-500/10 transition-all duration-700 pointer-events-none"></div>
        <div class="relative z-10">
        <h2 class="text-xl font-black text-slate-900 dark:text-white mb-6">Como funciona</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center shrink-0">
                    <span class="text-blue-600 dark:text-blue-400 font-black text-lg">1</span>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white mb-1">Copie seu link</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Cada membro tem um link e código únicos de indicação.</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center shrink-0">
                    <span class="text-purple-600 dark:text-purple-400 font-black text-lg">2</span>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white mb-1">Compartilhe</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Envie para amigos, colegas ou publique em suas redes sociais.</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center shrink-0">
                    <span class="text-teal-600 dark:text-teal-400 font-black text-lg">3</span>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white mb-1">Indicado assina</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">O indicado se cadastra pelo seu link e <strong class="text-teal-600 dark:text-teal-400">paga um plano pago</strong>.</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center shrink-0">
                    <span class="text-amber-600 dark:text-amber-400 font-black text-lg">4</span>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white mb-1">Você ganha pontos</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        @if($pointsPerReferral)
                            <strong class="text-amber-600 dark:text-amber-400">+{{ $pointsPerReferral }} pontos</strong> são creditados automaticamente após confirmação do pagamento.
                        @else
                            Pontos são creditados automaticamente na confirmação do pagamento do plano.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Regras --}}
    <div class="mt-6 bg-slate-50 dark:bg-slate-800 rounded-2xl px-5 py-4 flex items-start gap-3 text-sm text-slate-500 dark:text-slate-400">
        <i class="fas fa-info-circle text-blue-500 mt-0.5 shrink-0"></i>
        <div>
            <strong class="text-slate-700 dark:text-slate-300">Regras do programa: </strong>
            Planos gratuitos não geram pontos · Pontos são creditados somente após confirmação do pagamento pelo sistema · Não há limite de indicações
        </div>
    </div>
    </div>
    </div>

    </div>

    <div data-panel-referral-tab-panel="api" class="space-y-8 {{ $activePanelTab === 'api' ? '' : 'hidden' }}">
        @include('panel.referral.partials.api-tokens', [
            'apiTokens' => $apiTokens,
            'apiTokensEnabled' => $apiTokensEnabled,
            'apiTokenIpTrackingEnabled' => $apiTokenIpTrackingEnabled,
            'apiTokenPlainText' => $apiTokenPlainText,
            'apiTokenDeviceName' => $apiTokenDeviceName,
        ])
    </div>

    <div data-panel-referral-tab-panel="materiais" class="space-y-8 {{ $activePanelTab === 'materiais' ? '' : 'hidden' }}">
        <div id="referralShareKitSection">
            @include('panel.referral.partials.share-kit', [
                'affiliateShareKit' => $affiliateShareKit,
                'sandboxRequests' => $sandboxRequests,
                'sandboxLatestRequest' => $sandboxLatestRequest,
                'sandboxApprovedRequest' => $sandboxApprovedRequest,
                'sandboxAvailable' => $sandboxAvailable,
            ])
        </div>
    </div>

    <div data-panel-referral-tab-panel="rastreio" class="space-y-8 {{ $activePanelTab === 'rastreio' ? '' : 'hidden' }}">
        @include('panel.referral.partials.tracking-dashboard')

        <div id="referralChannelFunnelSection">
            @include('panel.referral.partials.channel-funnel', [
                'channelFunnels' => $channelFunnels,
                'title' => 'Funil por canal e origem',
                'subtitle' => 'Veja de onde vieram os cliques, quantas visualizações cada origem gerou e onde realmente converte.',
            ])
        </div>

        <div id="referralEventsLogSection">
            @include('panel.referral.partials.events-log', [
                'detailedEvents' => $detailedEvents,
                'exportUrl' => route('panel.referral.export'),
                'title' => 'Log detalhado de cliques, visitas e compartilhamentos',
                'subtitle' => 'Inclui URL de origem exata, landing page, dispositivo, navegador, cidade/país e o resultado de cada ação.',
                'emptyMessage' => 'Ainda não há cliques, visitas ou compartilhamentos detalhados para este afiliado.',
            ])
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('vendor/chart.js/js/chart.min.js') }}"></script>
<script>
const referralTrackUrl = @json(route('panel.referral.track'));
const referralStatsUrl = @json(route('panel.referral.stats'));
const referralSandboxPlaygroundUrl = @json(route('panel.referral.playground.execute'));
const referralSandboxBaseUrl = @json(url('/api/v1/sandbox/affiliate'));
const referralTrackToken = @json(csrf_token());
const referralDailyChartData = @json($trackingDailyChart);
const referralAcquisitionChartData = @json($trackingAcquisitionChart);
const referralSharingChartData = @json($trackingSharingChart);
const referralChartCurrency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
const referralNumberFormatter = new Intl.NumberFormat('pt-BR');
const referralCharts = {};
const referralRefreshIntervalMs = 5000;
let referralRefreshHandle = null;
let referralRefreshAbortController = null;

function getActiveReferralTab() {
    const activeButton = document.querySelector('[data-panel-referral-tab-target].bg-blue-600');
    return activeButton?.dataset?.panelReferralTabTarget || @json($activePanelTab);
}

function isReferralTrackingTabVisible() {
    const panel = document.querySelector('[data-panel-referral-tab-panel="rastreio"]');
    return panel && !panel.classList.contains('hidden');
}

function setActiveReferralTab(tabName, shouldSyncUrl = true) {
    const validTabs = ['programa', 'api', 'materiais', 'rastreio'];
    const nextTab = validTabs.includes(tabName) ? tabName : 'programa';

    document.querySelectorAll('[data-panel-referral-tab-target]').forEach((button) => {
        const isActive = button.dataset.panelReferralTabTarget === nextTab;
        button.classList.toggle('bg-blue-600', isActive);
        button.classList.toggle('text-white', isActive);
        button.classList.toggle('shadow-lg', isActive);
        button.classList.toggle('shadow-blue-500/20', isActive);
        button.classList.toggle('text-slate-600', !isActive);
        button.classList.toggle('hover:bg-slate-100', !isActive);
        button.classList.toggle('dark:text-slate-300', !isActive);
        button.classList.toggle('dark:hover:bg-slate-800', !isActive);
    });

    document.querySelectorAll('[data-panel-referral-tab-panel]').forEach((panel) => {
        panel.classList.toggle('hidden', panel.dataset.panelReferralTabPanel !== nextTab);
    });

    if (shouldSyncUrl) {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', nextTab);
        window.history.replaceState({}, '', url.toString());
    }

    if (nextTab === 'rastreio') {
        window.requestAnimationFrame(() => {
            initReferralCharts();
            scheduleReferralRefresh(120);
        });
    }
}

function getReferralChartPalette() {
    const isDark = document.documentElement.classList.contains('dark');

    return {
        grid: isDark ? 'rgba(148, 163, 184, 0.14)' : 'rgba(15, 23, 42, 0.08)',
        ticks: isDark ? '#94a3b8' : '#475569',
        border: isDark ? '#0f172a' : '#ffffff',
        visits: '#3b82f6',
        registrations: '#10b981',
        checkouts: '#f59e0b',
        purchases: '#8b5cf6',
        revenue: '#ef4444',
    };
}

function buildReferralAxisOptions() {
    const palette = getReferralChartPalette();

    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: {
                labels: {
                    color: palette.ticks,
                    usePointStyle: true,
                    boxWidth: 10,
                },
            },
        },
        scales: {
            x: {
                grid: { color: palette.grid },
                ticks: { color: palette.ticks },
            },
            y: {
                beginAtZero: true,
                grid: { color: palette.grid },
                ticks: {
                    color: palette.ticks,
                    precision: 0,
                },
            },
        },
    };
}

function destroyReferralCharts() {
    Object.values(referralCharts).forEach((chart) => chart?.destroy());
    Object.keys(referralCharts).forEach((key) => delete referralCharts[key]);
}

function renderReferralDailyChart() {
    const canvas = document.getElementById('referralDailyChart');
    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    const palette = getReferralChartPalette();
    const options = buildReferralAxisOptions();
    options.scales.yRevenue = {
        beginAtZero: true,
        position: 'right',
        grid: { drawOnChartArea: false },
        ticks: {
            color: palette.ticks,
            callback: (value) => referralChartCurrency.format(value),
        },
    };
    options.plugins.tooltip = {
        callbacks: {
            label: (context) => {
                if (context.dataset.yAxisID === 'yRevenue') {
                    return `${context.dataset.label}: ${referralChartCurrency.format(context.parsed.y || 0)}`;
                }

                return `${context.dataset.label}: ${context.parsed.y || 0}`;
            },
        },
    };

    referralCharts.daily = new Chart(canvas, {
        data: {
            labels: referralDailyChartData.labels,
            datasets: [
                {
                    type: 'line',
                    label: 'Visitas',
                    data: referralDailyChartData.visits,
                    borderColor: palette.visits,
                    backgroundColor: 'rgba(59, 130, 246, 0.15)',
                    borderWidth: 2,
                    tension: 0.35,
                },
                {
                    type: 'line',
                    label: 'Cadastros',
                    data: referralDailyChartData.registrations,
                    borderColor: palette.registrations,
                    backgroundColor: 'rgba(16, 185, 129, 0.15)',
                    borderWidth: 2,
                    tension: 0.35,
                },
                {
                    type: 'line',
                    label: 'Checkouts',
                    data: referralDailyChartData.checkouts,
                    borderColor: palette.checkouts,
                    backgroundColor: 'rgba(245, 158, 11, 0.15)',
                    borderWidth: 2,
                    tension: 0.35,
                },
                {
                    type: 'line',
                    label: 'Compras',
                    data: referralDailyChartData.purchases,
                    borderColor: palette.purchases,
                    backgroundColor: 'rgba(139, 92, 246, 0.15)',
                    borderWidth: 2,
                    tension: 0.35,
                },
                {
                    type: 'bar',
                    label: 'Receita',
                    data: referralDailyChartData.revenue,
                    backgroundColor: 'rgba(239, 68, 68, 0.18)',
                    borderColor: palette.revenue,
                    borderWidth: 1.5,
                    borderRadius: 10,
                    yAxisID: 'yRevenue',
                },
            ],
        },
        options,
    });
}

function renderReferralAcquisitionChart() {
    const canvas = document.getElementById('referralAcquisitionChart');
    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    const palette = getReferralChartPalette();
    const options = buildReferralAxisOptions();
    options.indexAxis = 'y';
    options.plugins.tooltip = {
        callbacks: {
            label: (context) => {
                if (context.dataset.label === 'Receita') {
                    return `${context.dataset.label}: ${referralChartCurrency.format(context.parsed.x || 0)}`;
                }

                return `${context.dataset.label}: ${context.parsed.x || 0}`;
            },
        },
    };

    referralCharts.acquisition = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: referralAcquisitionChartData.labels,
            datasets: [
                {
                    label: 'Visitas',
                    data: referralAcquisitionChartData.visits,
                    backgroundColor: 'rgba(59, 130, 246, 0.82)',
                    borderRadius: 10,
                },
                {
                    label: 'Cadastros',
                    data: referralAcquisitionChartData.registrations,
                    backgroundColor: 'rgba(16, 185, 129, 0.82)',
                    borderRadius: 10,
                },
                {
                    label: 'Compras',
                    data: referralAcquisitionChartData.purchases,
                    backgroundColor: 'rgba(139, 92, 246, 0.82)',
                    borderRadius: 10,
                },
                {
                    label: 'Receita',
                    data: referralAcquisitionChartData.revenue,
                    backgroundColor: 'rgba(239, 68, 68, 0.78)',
                    borderRadius: 10,
                },
            ],
        },
        options,
    });
}

function renderReferralSharingChart() {
    const canvas = document.getElementById('referralSharingChart');
    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    const palette = getReferralChartPalette();
    const shareTotals = referralSharingChartData.labels.map((_, index) =>
        (referralSharingChartData.shares[index] || 0)
        + (referralSharingChartData.reshares[index] || 0)
        + (referralSharingChartData.copies[index] || 0)
    );

    referralCharts.sharing = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: referralSharingChartData.labels,
            datasets: [
                {
                    data: shareTotals,
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.88)',
                        'rgba(14, 165, 233, 0.88)',
                        'rgba(249, 115, 22, 0.88)',
                        'rgba(59, 130, 246, 0.88)',
                        'rgba(168, 85, 247, 0.88)',
                        'rgba(244, 63, 94, 0.88)',
                    ],
                    borderColor: palette.border,
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: palette.ticks,
                        usePointStyle: true,
                        boxWidth: 10,
                    },
                },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.label}: ${context.parsed || 0} ação(ões)`,
                    },
                },
            },
        },
    });
}

function initReferralCharts() {
    if (!isReferralTrackingTabVisible()) {
        return;
    }

    destroyReferralCharts();
    renderReferralDailyChart();
    renderReferralAcquisitionChart();
    renderReferralSharingChart();
}

function replaceReferralChartData(target, payload, keys) {
    keys.forEach((key) => {
        target[key] = Array.isArray(payload?.[key]) ? payload[key] : [];
    });
}

function formatReferralNumber(value) {
    return referralNumberFormatter.format(Number(value || 0));
}

function formatReferralCurrency(value) {
    return referralChartCurrency.format(Number(value || 0));
}

function escapeReferralHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function replaceReferralSectionHtml(elementId, html) {
    const element = document.getElementById(elementId);
    if (!element || typeof html !== 'string' || html.trim() === '') {
        return;
    }

    element.innerHTML = html;
}

function buildReferralStatsRequestUrl() {
    const url = new URL(referralStatsUrl, window.location.origin);
    const params = new URLSearchParams(window.location.search);

    ['events_page'].forEach((key) => {
        if (params.has(key)) {
            url.searchParams.set(key, params.get(key));
        }
    });

    return url.toString();
}

async function copyReferralMaterial(button) {
    const text = button?.dataset?.copyText || '';
    const channel = button?.dataset?.trackChannel || 'copy';
    const targetUrl = button?.dataset?.targetUrl || referralStatsUrl;

    if (!text) {
        return;
    }

    const originalHtml = button.innerHTML;

    const done = () => {
        trackReferralAction('copy', channel, targetUrl);
        button.innerHTML = '<i class="fas fa-check"></i> Copiado';
        window.setTimeout(() => {
            button.innerHTML = originalHtml;
        }, 1800);
    };

    try {
        if (navigator.clipboard) {
            await navigator.clipboard.writeText(text);
            done();
            return;
        }

        const helper = document.createElement('textarea');
        helper.value = text;
        helper.setAttribute('readonly', 'readonly');
        helper.style.position = 'absolute';
        helper.style.left = '-9999px';
        document.body.appendChild(helper);
        helper.select();
        document.execCommand('copy');
        document.body.removeChild(helper);
        done();
    } catch (error) {
        console.error('Falha ao copiar material do afiliado.', error);
    }
}

function applyTrackingStatusBanner(tone, message) {
    const banner = document.getElementById('trackingStatusBanner');
    if (!banner) {
        return;
    }

    banner.textContent = message || 'Atualização automática a cada 5 segundos.';
    banner.className = `rounded-2xl px-4 py-3 text-sm font-medium ${
        tone === 'success'
            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300'
            : 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300'
    }`;
}

function renderTrackingChannelsList(channels) {
    const container = document.getElementById('trackingChannelsList');
    if (!container) {
        return;
    }

    if (!Array.isArray(channels) || channels.length === 0) {
        container.innerHTML = `
            <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 px-4 py-6 text-sm text-slate-500 dark:text-slate-400">
                Os compartilhamentos começam a aparecer aqui assim que você usar os botões rápidos ou copiar seu link.
            </div>
        `;
        return;
    }

    container.innerHTML = channels.map((channel) => `
        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 px-4 py-3">
            <div class="flex items-center justify-between gap-3">
                <span class="font-medium text-slate-700 dark:text-slate-300">${escapeReferralHtml(channel.channel)}</span>
                <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900/30 px-3 py-1 text-xs font-bold text-blue-700 dark:text-blue-300">${formatReferralNumber(channel.total)}</span>
            </div>
            <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500 dark:text-slate-400">
                <span>${formatReferralNumber(channel.shares)} novos</span>
                <span>·</span>
                <span>${formatReferralNumber(channel.reshares)} reenvios</span>
                <span>·</span>
                <span>${formatReferralNumber(channel.copies)} cópias</span>
            </div>
        </div>
    `).join('');
}

function renderTrackingVisitsRows(visits) {
    const rows = document.getElementById('trackingVisitsRows');
    const emptyState = document.getElementById('trackingVisitsEmpty');
    const tableWrapper = document.getElementById('trackingVisitsTableWrapper');

    if (!rows || !emptyState || !tableWrapper) {
        return;
    }

    if (!Array.isArray(visits) || visits.length === 0) {
        rows.innerHTML = '';
        emptyState.classList.remove('hidden');
        tableWrapper.classList.add('hidden');
        return;
    }

    rows.innerHTML = visits.map((visit) => `
        <tr>
            <td class="px-5 py-4">
                <p class="font-semibold text-slate-900 dark:text-white">${escapeReferralHtml(visit.first_visited_at)}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    ${formatReferralNumber(visit.clicks_count)} clique(s) · ${formatReferralNumber(visit.pageviews_count)} página(s)
                </p>
            </td>
            <td class="px-5 py-4">
                <p class="font-medium text-slate-700 dark:text-slate-300">${escapeReferralHtml(visit.source_label || 'Direto')}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[220px]">${escapeReferralHtml(visit.landing_page_path || '/')}</p>
            </td>
            <td class="px-5 py-4">
                ${visit.registered_user_name
                    ? `
                        <p class="font-medium text-slate-900 dark:text-white">${escapeReferralHtml(visit.registered_user_name)}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">${escapeReferralHtml(visit.registered_at_human || 'cadastrado')}</p>
                    `
                    : '<span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:text-slate-400">Sem cadastro</span>'
                }
            </td>
            <td class="px-5 py-4 text-right">
                ${Number(visit.purchases_count || 0) > 0
                    ? `
                        <p class="font-semibold text-emerald-600 dark:text-emerald-400">${formatReferralNumber(visit.purchases_count)} compra(s)</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">R$ ${escapeReferralHtml(visit.revenue_amount_formatted || '0,00')}</p>
                    `
                    : '<span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:text-slate-400">Sem compra</span>'
                }
            </td>
        </tr>
    `).join('');

    emptyState.classList.add('hidden');
    tableWrapper.classList.remove('hidden');
}

function applyReferralTrackingPayload(payload) {
    if (!payload) {
        return;
    }

    const summary = payload.trackingSummary || {};
    const totalShares = Number(summary.shares || 0) + Number(summary.reshares || 0) + Number(summary.copies || 0);

    applyTrackingStatusBanner(payload.trackingStatusTone, payload.trackingStatusMessage);

    const conversionMeta = document.getElementById('trackingConversionMeta');
    if (conversionMeta) {
        conversionMeta.innerHTML = `
            Conversão para cadastro: <strong class="text-slate-700 dark:text-slate-200">${formatReferralNumber(summary.registration_conversion || 0)}%</strong>
            · Compra: <strong class="text-slate-700 dark:text-slate-200">${formatReferralNumber(summary.purchase_conversion || 0)}%</strong>
            · Atualizado às <strong id="trackingUpdatedAtLabel" class="text-slate-700 dark:text-slate-200">${escapeReferralHtml(payload.trackingUpdatedAtLabel || '--:--:--')}</strong>
        `;
    }

    const map = {
        trackingClicksValue: formatReferralNumber(summary.clicks),
        trackingClicksMeta: `${formatReferralNumber(summary.visits)} visitas únicas · ${formatReferralNumber(summary.pageviews)} visualizações`,
        trackingRegistrationsValue: formatReferralNumber(summary.registrations),
        trackingRegistrationsMeta: `${formatReferralNumber(summary.checkout_starts)} checkouts iniciados · ${formatReferralNumber(summary.registration_conversion || 0)}% de conversão`,
        trackingPurchasesValue: formatReferralNumber(summary.purchases),
        trackingRevenueMeta: `${formatReferralCurrency(summary.revenue)} rastreados · ${formatReferralNumber(summary.purchase_conversion || 0)}% de conversão`,
        trackingSharesValue: formatReferralNumber(totalShares),
        trackingSharesMeta: `${formatReferralNumber(summary.shares)} novos · ${formatReferralNumber(summary.reshares)} reenvios · ${formatReferralNumber(summary.copies)} cópias`,
        trackingRevenueCardValue: formatReferralCurrency(summary.revenue),
        trackingPurchasesCardValue: `${formatReferralNumber(summary.purchases)} compra(s) confirmada(s)`,
    };

    Object.entries(map).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    });

    const topChannel = Array.isArray(payload.trackingChannels) && payload.trackingChannels.length > 0
        ? payload.trackingChannels[0]
        : null;

    const topChannelLabel = document.getElementById('trackingTopChannelLabel');
    const topChannelMeta = document.getElementById('trackingTopChannelMeta');
    if (topChannel) {
        if (topChannelLabel) {
            topChannelLabel.textContent = topChannel.channel || 'Outro';
            topChannelLabel.classList.remove('text-sm', 'font-semibold', 'text-slate-500', 'dark:text-slate-400');
            topChannelLabel.classList.add('text-xl', 'font-black', 'text-slate-900', 'dark:text-white');
        }

        if (topChannelMeta) {
            topChannelMeta.textContent = `${formatReferralNumber(topChannel.total)} ação(ões) registradas`;
            topChannelMeta.className = 'mt-1 text-xs text-slate-500 dark:text-slate-400';
        }
    } else {
        if (topChannelLabel) {
            topChannelLabel.textContent = 'Sem ações compartilhadas';
            topChannelLabel.classList.remove('text-xl', 'font-black', 'text-slate-900', 'dark:text-white');
            topChannelLabel.classList.add('text-sm', 'font-semibold', 'text-slate-500', 'dark:text-slate-400');
        }

        if (topChannelMeta) {
            topChannelMeta.textContent = 'Use os botões rápidos ou copie o link para começar a medir os canais.';
            topChannelMeta.className = 'mt-1 text-xs text-slate-500 dark:text-slate-400';
        }
    }

    renderTrackingChannelsList(payload.trackingChannels || []);
    renderTrackingVisitsRows(payload.trackedVisitsFeed || []);
    replaceReferralSectionHtml('referralChannelFunnelSection', payload.channelFunnelsHtml);
    replaceReferralSectionHtml('referralEventsLogSection', payload.detailedEventsHtml);

    replaceReferralChartData(referralDailyChartData, payload.trackingDailyChart || {}, ['labels', 'visits', 'registrations', 'checkouts', 'purchases', 'revenue']);
    replaceReferralChartData(referralAcquisitionChartData, payload.trackingAcquisitionChart || {}, ['labels', 'visits', 'registrations', 'purchases', 'revenue']);
    replaceReferralChartData(referralSharingChartData, payload.trackingSharingChart || {}, ['labels', 'shares', 'reshares', 'copies']);

    initReferralCharts();
}

async function refreshReferralTracking() {
    if (!referralStatsUrl) {
        return;
    }

    if (referralRefreshAbortController) {
        referralRefreshAbortController.abort();
    }

    referralRefreshAbortController = new AbortController();

    try {
        const response = await fetch(buildReferralStatsRequestUrl(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal: referralRefreshAbortController.signal,
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const payload = await response.json();
        if (payload?.ok) {
            applyReferralTrackingPayload(payload);
        }
    } catch (error) {
        if (error?.name !== 'AbortError') {
            console.error('Falha ao atualizar o rastreio de indicações.', error);
        }
    } finally {
        referralRefreshAbortController = null;
    }
}

function scheduleReferralRefresh(delay = 800) {
    window.clearTimeout(window.referralRefreshTimeout);
    window.referralRefreshTimeout = window.setTimeout(() => {
        refreshReferralTracking();
    }, delay);
}

function startReferralTrackingPolling() {
    if (referralRefreshHandle) {
        window.clearInterval(referralRefreshHandle);
    }

    referralRefreshHandle = window.setInterval(() => {
        if (!document.hidden) {
            refreshReferralTracking();
        }
    }, referralRefreshIntervalMs);
}

function trackReferralAction(action, channel, targetUrl = null) {
    const payload = new FormData();
    payload.append('_token', referralTrackToken);
    payload.append('action', action);

    if (channel) {
        payload.append('channel', channel);
    }

    if (targetUrl) {
        payload.append('target_url', targetUrl);
    }

    payload.append('context', 'panel_referral');

    if (navigator.sendBeacon) {
        navigator.sendBeacon(referralTrackUrl, payload);
        scheduleReferralRefresh();
        return;
    }

    fetch(referralTrackUrl, {
        method: 'POST',
        body: payload,
        credentials: 'same-origin',
        keepalive: true,
    }).then(() => {
        scheduleReferralRefresh();
    }).catch(() => {});
}

function copyReferralLink() {
    const input = document.getElementById('referralLinkInput');
    const btn = document.getElementById('copyBtn');
    const icon = document.getElementById('copyIcon');
    const text = document.getElementById('copyText');

    const doCopy = () => {
        trackReferralAction('copy', 'copy', input.value);
        icon.className = 'fas fa-check';
        text.textContent = 'Copiado!';
        btn.classList.replace('bg-blue-600', 'bg-green-600');
        btn.classList.replace('hover:bg-blue-700', 'hover:bg-green-700');
        setTimeout(() => {
            icon.className = 'fas fa-copy';
            text.textContent = 'Copiar';
            btn.classList.replace('bg-green-600', 'bg-blue-600');
            btn.classList.replace('hover:bg-green-700', 'hover:bg-blue-700');
        }, 2500);
    };

    if (navigator.clipboard) {
        navigator.clipboard.writeText(input.value).then(doCopy).catch(() => {
            input.select();
            document.execCommand('copy');
            doCopy();
        });
    } else {
        input.select();
        document.execCommand('copy');
        doCopy();
    }
}

function buildAffiliateSandboxRequestUrl() {
    const endpoint = document.getElementById('affiliateSandboxEndpoint')?.value || 'overview';
    const perPage = document.getElementById('affiliateSandboxPerPage')?.value || '10';
    const visitLimit = document.getElementById('affiliateSandboxVisitLimit')?.value || '5';

    if (endpoint === 'analytics') {
        const url = new URL(`${referralSandboxBaseUrl}/analytics`, window.location.origin);
        url.searchParams.set('per_page', perPage);
        url.searchParams.set('visit_limit', visitLimit);

        return url.toString();
    }

    return `${referralSandboxBaseUrl}/${endpoint}`;
}

function updateAffiliateSandboxPreview() {
    const requestUrl = buildAffiliateSandboxRequestUrl();
    const requestUrlElement = document.getElementById('affiliateSandboxRequestUrl');
    const curlElement = document.getElementById('affiliateSandboxCurlSnippet');

    if (requestUrlElement) {
        requestUrlElement.textContent = requestUrl;
    }

    if (curlElement) {
        curlElement.textContent = `curl "${requestUrl}" -H "Accept: application/json" -H "Authorization: Bearer SEU_TOKEN"`;
    }
}

function copyAffiliateSandboxCurl(button) {
    const curlElement = document.getElementById('affiliateSandboxCurlSnippet');
    const requestUrlElement = document.getElementById('affiliateSandboxRequestUrl');

    if (!curlElement) {
        return;
    }

    button.dataset.copyText = curlElement.textContent.trim();
    button.dataset.trackChannel = 'sandbox-curl';
    button.dataset.targetUrl = requestUrlElement?.textContent?.trim() || referralSandboxBaseUrl;

    copyReferralMaterial(button);
}

async function runAffiliateSandboxPlayground(button) {
    const endpoint = document.getElementById('affiliateSandboxEndpoint')?.value || 'overview';
    const perPage = document.getElementById('affiliateSandboxPerPage')?.value || '10';
    const visitLimit = document.getElementById('affiliateSandboxVisitLimit')?.value || '5';
    const metaElement = document.getElementById('affiliateSandboxResponseMeta');
    const payloadElement = document.getElementById('affiliateSandboxResponsePayload');
    const originalHtml = button.innerHTML;

    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Executando';

    if (metaElement) {
        metaElement.textContent = 'Executando...';
    }

    try {
        const body = new URLSearchParams();
        body.set('endpoint', endpoint);

        if (endpoint === 'analytics') {
            body.set('per_page', perPage);
            body.set('visit_limit', visitLimit);
        }

        const response = await fetch(referralSandboxPlaygroundUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-CSRF-TOKEN': referralTrackToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body.toString(),
        });

        const result = await response.json();

        if (!response.ok || !result?.ok) {
            throw result;
        }

        if (metaElement) {
            metaElement.textContent = `${result.endpoint} · ${result.duration_ms} ms`;
        }

        if (payloadElement) {
            payloadElement.textContent = JSON.stringify({
                request_url: result.request_url,
                payload: result.payload,
            }, null, 2);
        }
    } catch (error) {
        if (metaElement) {
            metaElement.textContent = 'Falha na execução';
        }

        if (payloadElement) {
            payloadElement.textContent = JSON.stringify({
                ok: false,
                message: error?.message || 'Não foi possível executar o playground.',
                errors: error?.errors || null,
            }, null, 2);
        }
    } finally {
        button.disabled = false;
        button.innerHTML = originalHtml;
    }
}

document.querySelectorAll('a[href^="https://wa.me/"], a[href^="https://t.me/share/"], a[href^="https://www.linkedin.com/sharing/"], a[href^="mailto:"]').forEach((link) => {
    link.addEventListener('click', () => {
        let channel = 'other';

        if (link.href.startsWith('https://wa.me/')) {
            channel = 'whatsapp';
        } else if (link.href.startsWith('https://t.me/share/')) {
            channel = 'telegram';
        } else if (link.href.startsWith('https://www.linkedin.com/sharing/')) {
            channel = 'linkedin';
        } else if (link.href.startsWith('mailto:')) {
            channel = 'email';
        }

        trackReferralAction('share', channel, link.href);
    });
});

document.querySelectorAll('[data-panel-referral-tab-target]').forEach((button) => {
    button.addEventListener('click', () => {
        setActiveReferralTab(button.dataset.panelReferralTabTarget);
    });
});

setActiveReferralTab(@json($activePanelTab), false);
updateAffiliateSandboxPreview();
initReferralCharts();
startReferralTrackingPolling();
scheduleReferralRefresh(300);

document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        scheduleReferralRefresh(150);
    }
});

window.addEventListener('focus', () => scheduleReferralRefresh(150));

new MutationObserver(() => {
    initReferralCharts();
}).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
</script>
@endpush

@endsection
