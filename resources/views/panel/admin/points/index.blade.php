@extends('panel.layouts.app')

@section('title', 'Regras de Pontuacao')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.points-rules.index') }}" class="hover:underline">Gamificacao</a>
@endsection

@php
    $coinName = (string) ($exchangeSettings['coin_name'] ?? 'UNNBIT');
    $unitValue = (float) ($exchangeSettings['unit_value_brl'] ?? $exchangeSettings['point_value'] ?? 0.01);
    $valuationTable = $exchangeSettings['valuation_table'] ?? [];
    $rulesTotal = (int) ($totalRules ?? $rulesGrouped->flatten(1)->count());
@endphp

@section('panel_content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300">
                    <i class="fas fa-coins text-[10px]"></i>
                    {{ $coinName }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Regras de pontuacao e cotacao</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 transition-colors">
                        Administre como os membros ganham {{ $coinName }} e quanto cada unidade vale em reais.
                    </p>
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

        <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <div class="space-y-4">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">
                            <i class="fas fa-money-bill-wave text-[10px]"></i>
                            Cotacao do {{ $coinName }}
                        </div>
                        <h2 class="mt-3 text-lg font-black text-slate-900 dark:text-white">Tabela de valores administravel</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            O admin define o valor em reais do {{ $coinName }} e pode revisar a referencia em dolar conforme inflacao/deflacao do mercado.
                        </p>
                    </div>

                    <form action="{{ route('panel.admin.points-rules.exchange-settings') }}" method="POST" class="grid gap-4 md:grid-cols-2">
                        @csrf
                        <label class="space-y-2">
                            <span class="block text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Lote de referencia</span>
                            <input type="number" name="base_points" min="1" value="{{ old('base_points', (int) ($exchangeSettings['base_points'] ?? 100)) }}"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                {{ $canManageExchange ? '' : 'readonly' }}>
                        </label>

                        <label class="space-y-2">
                            <span class="block text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Valor de 1 {{ $coinName }} (R$)</span>
                            <input type="text" name="unit_value" value="{{ old('unit_value', number_format($unitValue, 4, ',', '.')) }}"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                {{ $canManageExchange ? '' : 'readonly' }}>
                        </label>

                        <label class="space-y-2">
                            <span class="block text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Cotacao USD/BRL usada como referencia</span>
                            <input type="text" name="usd_reference_rate" value="{{ old('usd_reference_rate', number_format((float) ($exchangeSettings['usd_reference_rate'] ?? 1), 4, ',', '.')) }}"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                {{ $canManageExchange ? '' : 'readonly' }}>
                        </label>

                        <div class="space-y-2">
                            <span class="block text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Resultado do lote</span>
                            <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-black text-blue-700 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-300">
                                {{ number_format((int) ($exchangeSettings['base_points'] ?? 0), 0, ',', '.') }} {{ $coinName }} = R$ {{ number_format((float) ($exchangeSettings['base_amount'] ?? 0), 2, ',', '.') }}
                            </div>
                        </div>

                        <label class="space-y-2 md:col-span-2">
                            <span class="block text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Observacao de mercado</span>
                            <textarea name="market_note" rows="3"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                {{ $canManageExchange ? '' : 'readonly' }}>{{ old('market_note', $exchangeSettings['market_note'] ?? '') }}</textarea>
                        </label>

                        <div class="md:col-span-2 flex items-center justify-between gap-4">
                            <div class="text-xs text-slate-400 dark:text-slate-500">
                                @if(!empty($exchangeSettings['last_repriced_at']))
                                    Ultima revisao: {{ \Carbon\Carbon::parse($exchangeSettings['last_repriced_at'])->format('d/m/Y H:i') }}
                                @else
                                    Ainda sem revisao registrada.
                                @endif
                            </div>

                            @if($canManageExchange)
                                <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-700">
                                    <i class="fas fa-save"></i>
                                    Atualizar cotacao
                                </button>
                            @else
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-center text-sm font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                                    Somente o admin altera a cotacao
                                </div>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Tabela de equivalencia</p>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50 dark:border-slate-800 dark:bg-slate-950/50">
                                    <th class="px-4 py-3 text-left font-black text-slate-500 dark:text-slate-400">{{ $coinName }}</th>
                                    <th class="px-4 py-3 text-right font-black text-slate-500 dark:text-slate-400">Valor em reais</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($valuationTable as $row)
                                    <tr>
                                        <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">{{ number_format((int) $row['units'], 0, ',', '.') }} {{ $coinName }}</td>
                                        <td class="px-4 py-3 text-right font-black text-blue-600 dark:text-blue-300">R$ {{ number_format((float) $row['amount'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

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
            @endphp

            <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-5 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            <i class="{{ $categoryIcon }}"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ $categoryLabel }}</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $rules->count() }} {{ $rules->count() === 1 ? 'regra' : 'regras' }}</p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($rules as $rule)
                        <article class="flex flex-col gap-4 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-black text-slate-900 dark:text-white">{{ $rule->label }}</h3>
                                    <code class="rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-300">{{ $rule->key }}</code>
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.2em] {{ $rule->active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                        {{ $rule->active ? 'Ativa' : 'Inativa' }}
                                    </span>
                                    @if($rule->repeatable)
                                        <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                            Repetivel{{ $rule->max_daily ? ' · max ' . $rule->max_daily . '/dia' : '' }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                    {{ $rule->description ?: 'Sem descricao informada para esta regra.' }}
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <div class="rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20">
                                    {{ ($rule->points > 0 ? '+' : '') . (int) $rule->points }} {{ $coinName }}
                                </div>
                                <a href="{{ route('panel.admin.points-rules.edit', $rule) }}"
                                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 transition-all hover:bg-slate-100 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800">
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
                    Crie a primeira regra para definir como os usuarios acumulam {{ $coinName }} em cada tipo de interacao.
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
