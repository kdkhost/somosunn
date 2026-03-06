@php
    $title = $title ?? 'Funil por canal';
    $subtitle = $subtitle ?? 'Entenda por onde entram os cliques e em qual etapa cada canal converte melhor.';
@endphp

<section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ $title }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-cyan-700 dark:border-cyan-900/60 dark:bg-cyan-950/40 dark:text-cyan-300">
            <i class="fas fa-filter text-[10px]"></i>
            Conversão por origem
        </div>
    </div>

    @if($channelFunnels->isEmpty())
        <div class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
            Nenhum canal rastreado ainda. Assim que os acessos começarem a entrar pelo link, o funil aparece aqui.
        </div>
    @else
        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[920px] text-sm">
                <thead class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:border-slate-800 dark:text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Canal</th>
                        <th class="px-4 py-3 text-right">Cliques</th>
                        <th class="px-4 py-3 text-right">Visitas</th>
                        <th class="px-4 py-3 text-right">Visualizações</th>
                        <th class="px-4 py-3 text-right">Cadastros</th>
                        <th class="px-4 py-3 text-right">Checkouts</th>
                        <th class="px-4 py-3 text-right">Compras</th>
                        <th class="px-4 py-3 text-right">Receita</th>
                        <th class="px-4 py-3 text-right">Conv. cadastro</th>
                        <th class="px-4 py-3 text-right">Conv. compra</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($channelFunnels as $funnel)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-300">
                                        <i class="fas fa-broadcast-tower"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $funnel->channel }}</p>
                                        <div class="mt-1 flex h-2 w-44 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                            <span class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, $funnel->registration_conversion) }}%"></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-right font-semibold text-slate-700 dark:text-slate-300">{{ number_format($funnel->clicks) }}</td>
                            <td class="px-4 py-4 text-right font-semibold text-slate-700 dark:text-slate-300">{{ number_format($funnel->visits) }}</td>
                            <td class="px-4 py-4 text-right font-semibold text-slate-700 dark:text-slate-300">{{ number_format($funnel->pageviews) }}</td>
                            <td class="px-4 py-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">{{ number_format($funnel->registrations) }}</td>
                            <td class="px-4 py-4 text-right font-semibold text-amber-600 dark:text-amber-400">{{ number_format($funnel->checkouts) }}</td>
                            <td class="px-4 py-4 text-right font-semibold text-violet-600 dark:text-violet-400">{{ number_format($funnel->purchases) }}</td>
                            <td class="px-4 py-4 text-right font-bold text-slate-900 dark:text-white">R$ {{ number_format($funnel->revenue, 2, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right">
                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300">
                                    {{ $funnel->registration_conversion }}%
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <span class="inline-flex rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700 dark:bg-violet-900/20 dark:text-violet-300">
                                    {{ $funnel->purchase_conversion }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
