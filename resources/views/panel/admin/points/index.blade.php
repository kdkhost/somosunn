@extends('panel.layouts.app')

@section('title', 'Regras de Pontuação')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.points-rules.index') }}" class="hover:underline">Gamificação</a>
@endsection

@section('panel_content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Gamificação: Regras</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Configure como os usuários ganham pontos ao interagir com a plataforma.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('panel.admin.ranking.index') }}" 
               class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm font-bold rounded-xl transition-all flex items-center gap-2">
                <i class="fas fa-trophy"></i>
                <span>Ver Ranking</span>
            </a>
            <a href="{{ route('panel.admin.points-rules.create') }}" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Nova Regra</span>
            </a>
        </div>
    </div>

    {{-- Rules List --}}
    <div class="space-y-8">
        @foreach($rulesGrouped as $category => $rules)
        <div class="space-y-4">
            <div class="flex items-center gap-3 px-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs transition-colors">
                    <i class="fas fa-tags"></i>
                </div>
                <h3 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">
                    {{ $categories[$category] ?? ucfirst($category) }}
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($rules as $rule)
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
                    {{-- Points Badge --}}
                    <div class="absolute top-0 right-0 p-4">
                        <div class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-sm font-bold shadow-sm">
                            +{{ $rule->points }} pts
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                            <i class="fas {{ $rule->icon ?: 'fa-star' }} text-xl"></i>
                        </div>
                        <div class="flex-1 pr-16">
                            <h4 class="font-bold text-slate-900 mb-1">{{ $rule->label }}</h4>
                            <p class="text-xs text-slate-400 leading-relaxed font-medium">{{ $rule->description ?: 'Sem descrição informada.' }}</p>
                            
                            <div class="mt-4 flex flex-wrap gap-2">
                                @if($rule->active)
                                    <span class="text-[9px] font-bold text-emerald-500 dark:text-emerald-400 uppercase tracking-tighter bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded border border-emerald-100/50 dark:border-emerald-800/50 transition-colors">Ativa</span>
                                @else
                                    <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-tighter bg-slate-50 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700 transition-colors">Inativa</span>
                                @endif

                                @if($rule->repeatable)
                                    <span class="text-[9px] font-bold text-indigo-500 uppercase tracking-tighter bg-indigo-50 px-2 py-0.5 rounded">Repetível</span>
                                @endif
                                
                                @if($rule->max_daily)
                                    <span class="text-[9px] font-bold text-amber-600 uppercase tracking-tighter bg-amber-50 px-2 py-0.5 rounded">Máx: {{ $rule->max_daily }}/dia</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between border-t border-slate-50 pt-4">
                        <code class="text-[10px] text-slate-400 font-mono">{{ $rule->key }}</code>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('panel.admin.points-rules.edit', $rule) }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">Editar</a>
                            <form action="{{ route('panel.admin.points-rules.destroy', $rule) }}" method="POST" onsubmit="return confirm('Excluir regra?')">
                                @csrf @method('DELETE')
                                <button class="text-xs font-bold text-slate-400 hover:text-red-500 transition-colors">Remover</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
