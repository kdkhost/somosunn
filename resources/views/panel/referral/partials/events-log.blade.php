@php
    $title = $title ?? 'Log detalhado de cliques e visitas';
    $subtitle = $subtitle ?? 'Cada linha mostra a ação rastreada, origem exata, landing page, dispositivo, localização e resultado.';
    $showReferrer = $showReferrer ?? false;
    $emptyMessage = $emptyMessage ?? 'Ainda não há eventos rastreados para este escopo.';
@endphp

<section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ $title }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
        </div>
        @isset($exportUrl)
            <a href="{{ $exportUrl }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                <i class="fas fa-file-csv"></i>
                Exportar CSV
            </a>
        @endisset
    </div>

    @if($detailedEvents->isEmpty())
        <div class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
            {{ $emptyMessage }}
        </div>
    @else
        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[1380px] text-sm">
                <thead class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:border-slate-800 dark:text-slate-500">
                    <tr>
                        @if($showReferrer)
                            <th class="px-4 py-3 text-left">Afiliado</th>
                        @endif
                        <th class="px-4 py-3 text-left">Data e ação</th>
                        <th class="px-4 py-3 text-left">Origem exata</th>
                        <th class="px-4 py-3 text-left">Landing / URL</th>
                        <th class="px-4 py-3 text-left">Dispositivo</th>
                        <th class="px-4 py-3 text-left">Localização</th>
                        <th class="px-4 py-3 text-left">Resultado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($detailedEvents as $event)
                        <tr class="align-top hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            @if($showReferrer)
                                <td class="px-4 py-4">
                                    <p class="font-bold text-slate-900 dark:text-white">{{ $event->referrer_name }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $event->referral_code }}</p>
                                </td>
                            @endif
                            <td class="px-4 py-4">
                                <div class="space-y-2">
                                    <p class="font-bold text-slate-900 dark:text-white">{{ $event->occurred_at_label }}</p>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $event->event_badge_class }}">
                                        {{ $event->event_label }}
                                    </span>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $event->occurred_at_human }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $event->channel_label }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 break-all">{{ $event->source_url }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $event->landing_page_path }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 break-all">{{ $event->landing_page_url }}</p>
                                @if($event->tracked_page_url !== '—')
                                    <p class="mt-2 text-[11px] text-slate-400 dark:text-slate-500 break-all">
                                        URL rastreada: {{ $event->tracked_page_url }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $event->device_label }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $event->browser_label }} · {{ $event->os_label }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $event->location_label }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $event->result_value_label }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $event->result_user_label }}</p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($detailedEvents->hasPages())
            <div class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800">
                {{ $detailedEvents->links() }}
            </div>
        @endif
    @endif
</section>
