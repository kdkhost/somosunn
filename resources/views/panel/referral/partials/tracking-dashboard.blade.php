<div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm space-y-6">
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
