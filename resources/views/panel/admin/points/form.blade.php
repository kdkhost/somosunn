@extends('panel.layouts.app')

@section('title', $rule->exists ? 'Editar Regra' : 'Nova Regra')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">
                    {{ $rule->exists ? 'Editar Regra' : 'Nova Regra de Pontos' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Defina como e quando os
                    usuários serão recompensados.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('panel.admin.points-rules.index') }}"
                    class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                    Cancelar
                </a>
                <button type="submit" form="ruleForm"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <span>Salvar Regra</span>
                </button>
            </div>
        </div>

        <form id="ruleForm"
            action="{{ $rule->exists ? route('panel.admin.points-rules.update', $rule) : route('panel.admin.points-rules.store') }}"
            method="POST" class="space-y-6">
            @csrf
            @if($rule->exists) @method('PUT') @endif

            <div
                class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 space-y-6 transition-colors duration-300">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Chave
                            de Identificação
                            (Slug)</label>
                        <input type="text" name="key" value="{{ old('key', $rule->key) }}" required {{ $rule->exists ? 'readonly' : '' }}
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-mono text-sm {{ $rule->exists ? 'opacity-60 cursor-not-allowed' : '' }}"
                            placeholder="Ex: login_diario">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Nome
                            Amigável</label>
                        <input type="text" name="label" value="{{ old('label', $rule->label) }}" required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-bold"
                            placeholder="Ex: Realizar Login Diário">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Pontos</label>
                        <div class="relative">
                            <i class="fas fa-plus absolute left-4 top-1/2 -translate-y-1/2 text-emerald-500"></i>
                            <input type="number" name="points" value="{{ old('points', $rule->points ?: 0) }}" required
                                class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-bold">
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Categoria</label>
                        <select name="category"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-bold">
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}" @selected(old('category', $rule->category) === $key)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Ícone
                            (FontAwesome)</label>
                        <div class="relative">
                            <i
                                class="fas {{ old('icon', $rule->icon ?: 'fa-star') }} absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
                            <input type="text" name="icon" value="{{ old('icon', $rule->icon ?: 'fa-star') }}"
                                class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                                placeholder="fa-star">
                        </div>
                    </div>
                </div>

                <div>
                    <label
                        class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Descrição
                        da Ação</label>
                    <textarea name="description" rows="3"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-600 dark:text-slate-400 font-medium placeholder-slate-400 dark:placeholder-slate-600">{{ old('description', $rule->description) }}</textarea>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 border-t border-slate-100 dark:border-slate-800 transition-colors">
                    <div class="space-y-4">
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase transition-colors">Configurações
                            de Repetição</label>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="repeatable" id="repeatable" value="1" @checked(old('repeatable', $rule->repeatable))
                                class="w-4 h-4 text-blue-600 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 rounded focus:ring-blue-500 transition-colors">
                            <label for="repeatable"
                                class="text-sm font-semibold text-slate-700 dark:text-slate-300 transition-colors">Ação é
                                repetível (ganha
                                pontos toda vez)</label>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-1 transition-colors">Limite
                                Máximo Diário (0
                                = Ilimitado)</label>
                            <input type="number" name="max_daily" value="{{ old('max_daily', $rule->max_daily ?: 0) }}"
                                class="w-32 px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl outline-none focus:border-blue-500 transition-all text-sm font-bold dark:text-white">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase transition-colors">Status
                            do Sistema</label>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="active" id="active" value="1" @checked(old('active', $rule->exists ? $rule->active : true))
                                class="w-4 h-4 text-emerald-600 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 rounded focus:ring-emerald-500 transition-colors">
                            <label for="active"
                                class="text-sm font-semibold text-slate-700 dark:text-slate-300 italic transition-colors">Regra
                                de pontuação
                                ativa</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection