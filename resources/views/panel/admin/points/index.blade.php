@extends('panel.layouts.app')

@section('title', 'Regras de Pontuação')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.points-rules.index') }}" class="hover:underline">Gamificação</a>
@endsection

@section('panel_content')
@php
    $rulesTotal = (int) ($totalRules ?? $rulesGrouped->flatten(1)->count());

    $categoryThemes = [
        'engajamento' => [
            'icon_wrap' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300',
            'section_ring' => 'border-blue-200/80 dark:border-blue-800/80',
            'section_bg' => 'bg-white dark:bg-slate-900',
            'section_head' => 'bg-gradient-to-r from-blue-50 via-white to-white dark:from-blue-950/40 dark:via-slate-900 dark:to-slate-900',
            'row_accent' => 'bg-blue-500',
            'row_hover' => 'hover:bg-blue-50/50 dark:hover:bg-blue-950/20',
            'badge' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border-blue-100 dark:border-blue-800/60',
            'points' => 'bg-blue-600 text-white shadow-blue-500/20',
            'action' => 'text-blue-600 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30',
        ],
        'aprendizado' => [
            'icon_wrap' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-300',
            'section_ring' => 'border-emerald-200/80 dark:border-emerald-800/80',
            'section_bg' => 'bg-white dark:bg-slate-900',
            'section_head' => 'bg-gradient-to-r from-emerald-50 via-white to-white dark:from-emerald-950/40 dark:via-slate-900 dark:to-slate-900',
            'row_accent' => 'bg-emerald-500',
            'row_hover' => 'hover:bg-emerald-50/50 dark:hover:bg-emerald-950/20',
            'badge' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border-emerald-100 dark:border-emerald-800/60',
            'points' => 'bg-emerald-600 text-white shadow-emerald-500/20',
            'action' => 'text-emerald-600 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30',
        ],
        'comunidade' => [
            'icon_wrap' => 'bg-cyan-50 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-300',
            'section_ring' => 'border-cyan-200/80 dark:border-cyan-800/80',
            'section_bg' => 'bg-white dark:bg-slate-900',
            'section_head' => 'bg-gradient-to-r from-cyan-50 via-white to-white dark:from-cyan-950/40 dark:via-slate-900 dark:to-slate-900',
            'row_accent' => 'bg-cyan-500',
            'row_hover' => 'hover:bg-cyan-50/50 dark:hover:bg-cyan-950/20',
            'badge' => 'bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300 border-cyan-100 dark:border-cyan-800/60',
            'points' => 'bg-cyan-600 text-white shadow-cyan-500/20',
            'action' => 'text-cyan-600 dark:text-cyan-300 hover:bg-cyan-50 dark:hover:bg-cyan-900/30',
        ],
        'conquistas' => [
            'icon_wrap' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-300',
            'section_ring' => 'border-amber-200/80 dark:border-amber-800/80',
            'section_bg' => 'bg-white dark:bg-slate-900',
            'section_head' => 'bg-gradient-to-r from-amber-50 via-white to-white dark:from-amber-950/40 dark:via-slate-900 dark:to-slate-900',
            'row_accent' => 'bg-amber-500',
            'row_hover' => 'hover:bg-amber-50/50 dark:hover:bg-amber-950/20',
            'badge' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border-amber-100 dark:border-amber-800/60',
            'points' => 'bg-amber-500 text-slate-950 shadow-amber-500/20',
            'action' => 'text-amber-600 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-900/30',
        ],
        'bonus' => [
            'icon_wrap' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-300',
            'section_ring' => 'border-rose-200/80 dark:border-rose-800/80',
            'section_bg' => 'bg-white dark:bg-slate-900',
            'section_head' => 'bg-gradient-to-r from-rose-50 via-white to-white dark:from-rose-950/40 dark:via-slate-900 dark:to-slate-900',
            'row_accent' => 'bg-rose-500',
            'row_hover' => 'hover:bg-rose-50/50 dark:hover:bg-rose-950/20',
            'badge' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border-rose-100 dark:border-rose-800/60',
            'points' => 'bg-rose-600 text-white shadow-rose-500/20',
            'action' => 'text-rose-600 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-900/30',
        ],
        'outros' => [
            'icon_wrap' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300',
            'section_ring' => 'border-slate-200 dark:border-slate-800',
            'section_bg' => 'bg-white dark:bg-slate-900',
            'section_head' => 'bg-gradient-to-r from-slate-50 via-white to-white dark:from-slate-800/70 dark:via-slate-900 dark:to-slate-900',
            'row_accent' => 'bg-slate-500',
            'row_hover' => 'hover:bg-slate-50/60 dark:hover:bg-slate-800/50',
            'badge' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
            'points' => 'bg-slate-700 text-white shadow-slate-500/20',
            'action' => 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800',
        ],
    ];

    $statusBadge = [
        true => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border-emerald-100 dark:border-emerald-800/60',
        false => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300">
                <i class="fas fa-star text-[10px]"></i>
                Gamificação
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Regras de Pontuação</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 transition-colors">Uma lista visual por tipo para diferenciar melhor as ações, bônus e recorrências.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    <i class="fas fa-list-check"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Total</p>
                    <p class="text-lg font-black text-slate-900 dark:text-white">{{ $rulesTotal }}</p>
                </div>
            </div>
            <a href="{{ route('panel.admin.ranking.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-amber-800 dark:hover:bg-amber-900/20 dark:hover:text-amber-300">
                <i class="fas fa-trophy"></i>
                Ver Ranking
            </a>
            <a href="{{ route('panel.admin.points-rules.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-700 hover:shadow-blue-500/30">
                <i class="fas fa-plus"></i>
                Nova Regra
            </a>
        </div>
    </div>

    @if($rulesTotal > 0)
        <div class="flex flex-wrap gap-2">
            @foreach($rulesGrouped as $category => $rules)
                @php
                    $categoryKey = is_string($category) && $category !== '' ? $category : 'outros';
                    $categoryMeta = $categories[$categoryKey] ?? null;
                    $categoryLabel = is_array($categoryMeta)
                        ? ($categoryMeta['label'] ?? ucfirst($categoryKey))
                        : (is_string($categoryMeta) ? $categoryMeta : ucfirst($categoryKey));
                    $categoryIcon = is_array($categoryMeta)
                        ? ($categoryMeta['icon'] ?? 'fas fa-tags')
                        : 'fas fa-tags';
                    $theme = $categoryThemes[$categoryKey] ?? $categoryThemes['outros'];
                @endphp
                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-bold {{ $theme['badge'] }}">
                    <i class="{{ $categoryIcon }}"></i>
                    {{ $categoryLabel }}
                    <span class="rounded-full bg-white/80 px-2 py-0.5 text-[10px] dark:bg-slate-950/60">{{ $rules->count() }}</span>
                </span>
            @endforeach
        </div>
    @endif

    @forelse($rulesGrouped as $category => $rules)
        @php
            $categoryKey = is_string($category) && $category !== '' ? $category : 'outros';
            $categoryMeta = $categories[$categoryKey] ?? null;
            $categoryLabel = is_array($categoryMeta)
                ? ($categoryMeta['label'] ?? ucfirst($categoryKey))
                : (is_string($categoryMeta) ? $categoryMeta : ucfirst($categoryKey));
            $categoryIcon = is_array($categoryMeta)
                ? ($categoryMeta['icon'] ?? 'fas fa-tags')
                : 'fas fa-tags';
            $theme = $categoryThemes[$categoryKey] ?? $categoryThemes['outros'];
        @endphp

        <section class="overflow-hidden rounded-[2rem] border {{ $theme['section_ring'] }} {{ $theme['section_bg'] }} shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 dark:border-slate-800 md:flex-row md:items-center md:justify-between {{ $theme['section_head'] }}">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $theme['icon_wrap'] }}">
                        <i class="{{ $categoryIcon }} text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ $categoryLabel }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Regras deste tipo para destacar ações semelhantes no programa de pontos.</p>
                    </div>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full border border-white/60 bg-white/80 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200">
                    <i class="fas fa-layer-group text-slate-400 dark:text-slate-500"></i>
                    {{ $rules->count() }} {{ $rules->count() === 1 ? 'regra' : 'regras' }}
                </div>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($rules as $rule)
                    <article class="group relative flex min-w-0 gap-0 transition-colors {{ $theme['row_hover'] }}">
                        <div class="w-1.5 shrink-0 {{ $theme['row_accent'] }}"></div>
                        <div class="flex min-w-0 flex-1 flex-col gap-5 px-5 py-5 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $theme['icon_wrap'] }}">
                                                <i class="fas {{ $rule->icon ?: 'fa-star' }}"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <h3 class="truncate text-base font-black text-slate-900 dark:text-white">{{ $rule->label }}</h3>
                                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                                    <code class="rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-300">{{ $rule->key }}</code>
                                                    <span class="rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.2em] {{ $statusBadge[$rule->active] }}">
                                                        {{ $rule->active ? 'Ativa' : 'Inativa' }}
                                                    </span>
                                                    @if($rule->repeatable)
                                                        <span class="rounded-full border border-indigo-100 bg-indigo-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-700 dark:border-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                                                            Repetível
                                                        </span>
                                                    @endif
                                                    @if($rule->max_daily)
                                                        <span class="rounded-full border border-amber-100 bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-amber-700 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                                            Máx {{ $rule->max_daily }}/dia
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <p class="mt-4 max-w-3xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                                            {{ $rule->description ?: 'Sem descrição informada para esta regra.' }}
                                        </p>
                                    </div>

                                    @php
                                        $pointsValue = (int) $rule->points;
                                        $pointsLabel = ($pointsValue > 0 ? '+' : '') . $pointsValue . ' pts';
                                    @endphp
                                    <div class="lg:pl-6">
                                        <div class="inline-flex min-w-[110px] items-center justify-center rounded-2xl px-4 py-3 text-sm font-black shadow-lg {{ $theme['points'] }}">
                                            {{ $pointsLabel }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-wrap items-center gap-2 lg:pl-4">
                                <a href="{{ route('panel.admin.points-rules.edit', $rule) }}"
                                   class="inline-flex items-center gap-2 rounded-xl border border-transparent px-3 py-2 text-xs font-bold transition-all {{ $theme['action'] }}">
                                    <i class="fas fa-pen"></i>
                                    Editar
                                </a>
                                <form action="{{ route('panel.admin.points-rules.destroy', $rule) }}" method="POST" onsubmit="return confirmAction(event, 'Excluir regra?', 'Excluir regra?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-xl border border-transparent px-3 py-2 text-xs font-bold text-slate-500 transition-all hover:bg-red-50 hover:text-red-600 dark:text-slate-400 dark:hover:bg-red-900/30 dark:hover:text-red-300">
                                        <i class="fas fa-trash"></i>
                                        Remover
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <div class="rounded-[2rem] border border-dashed border-slate-300 bg-white px-8 py-16 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                <i class="fas fa-star text-2xl"></i>
            </div>
            <h3 class="mt-5 text-lg font-black text-slate-900 dark:text-white">Nenhuma regra cadastrada</h3>
            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500 dark:text-slate-400">
                Crie a primeira regra para definir como os usuários acumulam pontos em cada tipo de interação.
            </p>
            <a href="{{ route('panel.admin.points-rules.create') }}"
               class="mt-6 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-700">
                <i class="fas fa-plus"></i>
                Criar primeira regra
            </a>
        </div>
    @endforelse

    @if(isset($rulesPaginator) && $rulesPaginator->hasPages())
        <div class="rounded-[2rem] border border-slate-200 bg-white px-6 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            {{ $rulesPaginator->links() }}
        </div>
    @endif
</div>
@endsection
