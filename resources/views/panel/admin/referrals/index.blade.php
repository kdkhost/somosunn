@extends('panel.layouts.app')

@section('title', 'Afiliados')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.referrals.index') }}" class="hover:underline">Afiliados</a>
@endsection

@section('panel_content')
@php
    $trackingSummary = $trackingSummary ?? [];
    $selectedScopeLabel = $selectedReferrer?->name ? 'Visão filtrada do afiliado' : 'Visão global da plataforma';
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 rounded-full border border-fuchsia-100 bg-fuchsia-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-fuchsia-700 dark:border-fuchsia-900/60 dark:bg-fuchsia-950/40 dark:text-fuchsia-300">
                <i class="fas fa-bullhorn text-[10px]"></i>
                Afiliados
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Rastreio global de indicações</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $selectedScopeLabel }} com cliques, origens, compartilhamentos, checkouts e compras.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if($selectedReferrer)
                <div class="inline-flex items-center gap-3 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm dark:border-blue-900/60 dark:bg-blue-950/30">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-blue-600 dark:bg-slate-900 dark:text-blue-300">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <p class="font-black text-slate-900 dark:text-white">{{ $selectedReferrer->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $selectedReferrer->referral_code ?: 'Sem código' }}</p>
                    </div>
                </div>
                <a href="{{ route('panel.admin.referrals.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:border-slate-300 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                    <i class="fas fa-filter-circle-xmark"></i>
                    Limpar filtro
                </a>
            @endif
            <a href="{{ route('panel.admin.referrals.export', ['referrer' => $selectedReferrer?->id]) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-700">
                <i class="fas fa-file-csv"></i>
                Exportar CSV
            </a>
        </div>
    </div>

    <div id="trackingStatusBanner"
        class="rounded-2xl px-4 py-3 text-sm font-medium {{ $trackingStatusTone === 'success'
            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300'
            : 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300' }}">
        {{ $trackingStatusMessage }}
    </div>

    <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-5">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Cliques</p>
            <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ number_format($trackingSummary['clicks'] ?? 0) }}</p>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ number_format($trackingSummary['visits'] ?? 0) }} visitas únicas · {{ number_format($trackingSummary['pageviews'] ?? 0) }} visualizações</p>
        </div>
        <div class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Cadastros</p>
            <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ number_format($trackingSummary['registrations'] ?? 0) }}</p>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ number_format($trackingSummary['registration_conversion'] ?? 0) }}% de conversão</p>
        </div>
        <div class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Checkouts</p>
            <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ number_format($trackingSummary['checkout_starts'] ?? 0) }}</p>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Inícios de pagamento atribuídos</p>
        </div>
        <div class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Compras</p>
            <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ number_format($trackingSummary['purchases'] ?? 0) }}</p>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ number_format($trackingSummary['purchase_conversion'] ?? 0) }}% de conversão</p>
        </div>
        <div class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Receita</p>
            <p class="mt-3 text-3xl font-black text-slate-900 dark:text-white">R$ {{ number_format((float) ($trackingSummary['revenue'] ?? 0), 2, ',', '.') }}</p>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Atualizado às {{ $trackingUpdatedAtLabel }}</p>
        </div>
    </div>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900 dark:text-white">Ranking de afiliados</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Todos os usuários com tráfego ou receita atribuída via link de indicação.</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300">
                <i class="fas fa-ranking-star text-[10px]"></i>
                Top afiliados
            </div>
        </div>

        @if($affiliateLeaderboard->isEmpty())
            <div class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                Ainda não há afiliados com dados rastreados.
            </div>
        @else
            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[1120px] text-sm">
                    <thead class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:border-slate-800 dark:text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Afiliado</th>
                            <th class="px-4 py-3 text-right">Cliques</th>
                            <th class="px-4 py-3 text-right">Visitas</th>
                            <th class="px-4 py-3 text-right">Cadastros</th>
                            <th class="px-4 py-3 text-right">Compras</th>
                            <th class="px-4 py-3 text-right">Receita</th>
                            <th class="px-4 py-3 text-right">Compart.</th>
                            <th class="px-4 py-3 text-right">Pontos</th>
                            <th class="px-4 py-3 text-left">Última atividade</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($affiliateLeaderboard as $affiliate)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl bg-slate-100 dark:bg-slate-800">
                                            @if($affiliate->photo)
                                                <img src="{{ asset($affiliate->photo) }}" alt="{{ $affiliate->name }}" class="h-full w-full object-cover">
                                            @else
                                                <i class="fas fa-user text-slate-400"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-white">{{ $affiliate->name }}</p>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $affiliate->email }}</p>
                                            <p class="mt-1 text-[11px] font-semibold text-blue-600 dark:text-blue-300">{{ $affiliate->referral_code ?: 'Sem código' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-right font-semibold text-slate-700 dark:text-slate-300">{{ number_format($affiliate->clicks) }}</td>
                                <td class="px-4 py-4 text-right font-semibold text-slate-700 dark:text-slate-300">{{ number_format($affiliate->visits) }}</td>
                                <td class="px-4 py-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">{{ number_format($affiliate->registrations) }}</td>
                                <td class="px-4 py-4 text-right font-semibold text-violet-600 dark:text-violet-400">{{ number_format($affiliate->purchases) }}</td>
                                <td class="px-4 py-4 text-right font-bold text-slate-900 dark:text-white">R$ {{ number_format($affiliate->revenue, 2, ',', '.') }}</td>
                                <td class="px-4 py-4 text-right font-semibold text-slate-700 dark:text-slate-300">{{ number_format($affiliate->shares_total) }}</td>
                                <td class="px-4 py-4 text-right font-semibold text-amber-600 dark:text-amber-400">{{ number_format($affiliate->referral_points) }}</td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $affiliate->last_activity_label }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $affiliate->last_activity_human }}</p>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <a href="{{ route('panel.admin.referrals.index', ['referrer' => $affiliate->id]) }}"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-sm transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                                        <i class="fas fa-chart-line"></i>
                                        Ver detalhes
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($affiliateLeaderboard->hasPages())
                <div class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800">
                    {{ $affiliateLeaderboard->links() }}
                </div>
            @endif
        @endif
    </section>

    @include('panel.referral.partials.channel-funnel', [
        'channelFunnels' => $channelFunnels,
        'title' => $selectedReferrer ? 'Funil por canal do afiliado' : 'Funil por canal da plataforma',
        'subtitle' => $selectedReferrer
            ? 'Canal por canal, acompanhe onde este afiliado converte melhor.'
            : 'Consolidação global dos canais com mais cliques, cadastros, checkouts e compras.',
    ])

    @include('panel.referral.partials.events-log', [
        'detailedEvents' => $detailedEvents,
        'showReferrer' => true,
        'exportUrl' => route('panel.admin.referrals.export', ['referrer' => $selectedReferrer?->id]),
        'title' => $selectedReferrer ? 'Log detalhado do afiliado' : 'Log detalhado global de afiliados',
        'subtitle' => 'Inclui evento a evento com URL de origem, landing page, dispositivo, localização e desfecho.',
        'emptyMessage' => 'Ainda não há eventos rastreados para este escopo.',
    ])
</div>
@endsection
