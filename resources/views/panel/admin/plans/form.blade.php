@extends('panel.layouts.app')

@section('title', ($plan->id ? 'Editar' : 'Novo') . ' Plano')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.plans.index') }}" class="hover:underline transition-all">Planos</a>
    <span class="mx-2 text-slate-300 dark:text-slate-700 transition-colors">/</span>
    <span class="text-slate-500 dark:text-slate-400 transition-colors">{{ $plan->id ? 'Editar' : 'Novo' }}</span>
@endsection

@section('panel_content')
@php
    $planFeatures = $planFeatures ?? \App\Models\Plan::siteFeatureLabels();
    $planFeatureGroups = $planFeatureGroups ?? \App\Models\Plan::siteFeatureGroups();
    $periodLabels = \App\Models\Plan::periodLabels();
    $pricePeriods = old('price_periods', method_exists($plan, 'resolvedPricePeriods') ? $plan->resolvedPricePeriods() : ($plan->price_periods ?? []));
    $periodSettings = old('period_settings', method_exists($plan, 'resolvedPeriodSettings') ? $plan->resolvedPeriodSettings() : []);
    $selectedFeatures = old('permissions', $plan->permissions ?? []);
    if (!is_array($selectedFeatures)) {
        $selectedFeatures = [];
    }
    $benefits = old('benefits', is_array($plan->benefits) ? implode("\n", $plan->benefits) : $plan->benefits);
@endphp

<form action="{{ $plan->id ? route('panel.admin.plans.update', $plan) : route('panel.admin.plans.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($plan->id)
        @method('PUT')
    @endif

    <div class="space-y-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
            <div>
                <h2 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight transition-colors">
                    {{ $plan->id ? 'Editar' : 'Novo' }} Plano
                </h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm transition-colors mt-1 font-medium">
                    Ajuste regras comerciais, periodos de cobranca e permissoes do assinante.
                </p>
            </div>
            <div class="flex gap-3 w-full sm:w-auto">
                <a href="{{ route('panel.admin.plans.index') }}"
                    class="flex-1 sm:flex-none text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-bold py-3 px-6 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm">
                    Cancelar
                </a>
                <button type="submit"
                    class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-2xl shadow-xl shadow-blue-500/20 transition transform hover:scale-[1.02] active:scale-[0.98]">
                    <i class="fas fa-save mr-2"></i> Salvar Plano
                </button>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/20 text-red-600 dark:text-red-400 px-6 py-4 rounded-3xl flex items-start gap-4 transition-all">
                <i class="fas fa-exclamation-triangle mt-1 text-lg"></i>
                <div>
                    <p class="font-bold text-lg mb-1 tracking-tight">Existem campos que precisam de correcao.</p>
                    <ul class="list-disc list-inside text-sm font-medium opacity-90">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-50 dark:border-slate-800 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h3 class="font-bold text-xl text-slate-800 dark:text-white transition-colors">Informacoes gerais</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Nome do plano</label>
                            <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                                class="w-full px-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-semibold text-slate-800 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Preco base</label>
                            <input type="text" name="price" value="{{ old('price', number_format((float) ($plan->price ?? 0), 2, ',', '.')) }}" required
                                class="w-full px-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-slate-800 dark:text-white mask-money">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Periodo padrao</label>
                            <select name="period"
                                class="w-full px-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-semibold text-slate-800 dark:text-white">
                                @foreach(array_merge($periodLabels, ['vitalicio' => 'Vitalicio']) as $value => $label)
                                    <option value="{{ $value }}" {{ old('period', $plan->period) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Slug</label>
                            <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}"
                                class="w-full px-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-mono text-slate-800 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Ciclo recorrente (meses)</label>
                            <input type="number" name="billing_cycle" min="1" max="12" value="{{ old('billing_cycle', $plan->billing_cycle ?? 1) }}"
                                class="w-full px-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-semibold text-slate-800 dark:text-white">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Descricao</label>
                            <textarea name="description" rows="3"
                                class="w-full px-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">{{ old('description', $plan->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-50 dark:border-slate-800 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-cyan-50 dark:bg-cyan-900/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-xl text-slate-800 dark:text-white transition-colors">Periodos de cobranca</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Ligue ou desligue mensal, trimestral, semestral e anual sem perder os precos salvos.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach($periodLabels as $periodKey => $label)
                            @php
                                $enabled = old('period_settings.' . $periodKey . '.enabled', data_get($periodSettings, $periodKey . '.enabled', $periodKey === 'mensal'));
                                $priceValue = old('price_periods.' . $periodKey, $pricePeriods[$periodKey] ?? ($periodKey === 'mensal' ? $plan->price : ''));
                            @endphp
                            <div class="grid grid-cols-1 md:grid-cols-[160px,140px,1fr] gap-4 items-center p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50">
                                <div>
                                    <p class="text-sm font-black text-slate-800 dark:text-white">{{ $label }}</p>
                                    <p class="text-xs text-slate-400 mt-1">{{ $periodKey === 'mensal' ? 'Base principal do plano' : 'Pode ficar desligado e ser reativado depois' }}</p>
                                </div>
                                <div>
                                    <input type="hidden" name="period_settings[{{ $periodKey }}][enabled]" value="0">
                                    <label class="flex items-center justify-between cursor-pointer group p-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                        <span class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Ativo</span>
                                        <div class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="period_settings[{{ $periodKey }}][enabled]" value="1" class="sr-only peer" {{ $enabled ? 'checked' : '' }}>
                                            <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 rounded-full peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                        </div>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Preco total</label>
                                    <input type="number" step="0.01" min="0" name="price_periods[{{ $periodKey }}]" value="{{ $priceValue }}"
                                        class="w-full px-5 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm font-bold dark:text-white transition-all focus:border-blue-500 outline-none">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-50 dark:border-slate-800 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-xl text-slate-800 dark:text-white transition-colors">Permissoes do assinante</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Estas permissoes controlam o que o membro realmente consegue usar.</p>
                        </div>
                    </div>

                    <div class="space-y-8">
                        @foreach($planFeatureGroups as $groupName => $groupKeys)
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 mb-4 uppercase tracking-[0.2em]">{{ $groupName }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($groupKeys as $featureKey)
                                        @if(isset($planFeatures[$featureKey]))
                                            <label class="flex items-center gap-4 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 hover:bg-white dark:hover:bg-slate-900 hover:border-blue-200 dark:hover:border-blue-900 transition-all cursor-pointer">
                                                <input type="checkbox" name="permissions[]" value="{{ $featureKey }}" {{ in_array($featureKey, $selectedFeatures, true) ? 'checked' : '' }}
                                                    class="w-5 h-5 text-blue-600 border-slate-300 dark:border-slate-700 rounded-lg focus:ring-blue-500 dark:bg-slate-950">
                                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $planFeatures[$featureKey] }}</span>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 flex items-center justify-center text-xs">
                            <i class="fas fa-sliders-h"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-white transition-colors">Configuracoes</h3>
                    </div>

                    <div class="space-y-4">
                        <input type="hidden" name="is_active" value="0">
                        <label class="flex items-center justify-between cursor-pointer group p-3 rounded-2xl border border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/30">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Plano ativo</span>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 rounded-full peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </div>
                        </label>

                        <input type="hidden" name="highlight" value="0">
                        <label class="flex items-center justify-between cursor-pointer group p-3 rounded-2xl border border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/30">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Plano em destaque</span>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="highlight" value="1" class="sr-only peer" {{ old('highlight', $plan->highlight) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 rounded-full peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                            </div>
                        </label>

                        <input type="hidden" name="is_free" value="0">
                        <label class="flex items-center justify-between cursor-pointer group p-3 rounded-2xl border border-green-100 dark:border-green-900/30 bg-green-50/30 dark:bg-green-950/20">
                            <div>
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Plano gratuito padrao</span>
                                <p class="text-xs text-slate-500 dark:text-slate-500 mt-0.5">Atribuido automaticamente a novos cadastros</p>
                            </div>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_free" value="1" class="sr-only peer" {{ old('is_free', $plan->is_free ?? false) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 rounded-full peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </div>
                        </label>

                        <input type="hidden" name="coupons_enabled" value="0">
                        <label class="flex items-center justify-between cursor-pointer group p-3 rounded-2xl border border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/30">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Permitir cupons</span>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="coupons_enabled" value="1" class="sr-only peer" {{ old('coupons_enabled', $plan->coupons_enabled) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 rounded-full peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-fuchsia-600"></div>
                            </div>
                        </label>

                        <input type="hidden" name="is_recurring" value="0">
                        <label class="flex items-center justify-between cursor-pointer group p-3 rounded-2xl border border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/30">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Assinatura recorrente</span>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_recurring" value="1" class="sr-only peer" {{ old('is_recurring', $plan->is_recurring) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 rounded-full peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 transition-all">
                    <h3 class="font-bold text-slate-800 dark:text-white transition-colors mb-4 flex items-center gap-2">
                        <i class="fas fa-image text-blue-500 text-sm"></i> Capa do plano
                    </h3>

                    <div class="relative w-full aspect-video bg-slate-50 dark:bg-slate-950 rounded-2xl overflow-hidden border-2 border-dashed border-slate-200 dark:border-slate-800 group transition-all">
                        @if($plan->image)
                            <img id="plan_preview" src="{{ asset('storage/' . $plan->image) }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center backdrop-blur-sm">
                                <label class="cursor-pointer text-white flex flex-col items-center gap-2">
                                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wider">Trocar capa</span>
                                    <input type="file" name="image" class="hidden" accept="image/*" onchange="previewPlanImage(this)">
                                </label>
                            </div>
                        @else
                            <label class="w-full h-full flex flex-col items-center justify-center cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition text-slate-400 dark:text-slate-600">
                                <i class="fas fa-cloud-upload-alt text-3xl mb-2"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest">Enviar imagem</span>
                                <input type="file" name="image" class="hidden" accept="image/*" onchange="previewPlanImage(this)">
                            </label>
                            <img id="plan_preview" class="hidden w-full h-full object-cover">
                        @endif
                    </div>

                    <input type="hidden" name="remove_image" value="0" id="remove_image_input">
                    @if($plan->image)
                        <button type="button" onclick="markPlanImageForRemoval()" class="mt-4 w-full py-2 bg-red-50 dark:bg-red-900/10 text-red-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-100 transition">
                            <i class="fas fa-trash-alt mr-2"></i> Remover imagem
                        </button>
                    @endif
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs">
                            <i class="fas fa-list-ul"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-white transition-colors">Lista de beneficios</h3>
                    </div>
                    <textarea name="benefits" rows="8"
                        class="w-full px-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-semibold text-slate-700 dark:text-slate-300 placeholder:text-slate-400 leading-relaxed"
                        placeholder="Acesso a comunidade&#10;Clube de beneficios&#10;Pitch diferenciado">{{ $benefits }}</textarea>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-50 dark:border-slate-800 pb-4">
                        <div class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-500 flex items-center justify-center text-xs">
                            <i class="fas fa-table"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-white transition-colors">Campos comparativos opcionais</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Conexoes / mes</label>
                            <input type="text" name="comparison[connections_per_month]" value="{{ old('comparison.connections_per_month', data_get($plan->comparison, 'connections_per_month')) }}"
                                class="w-full px-5 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-sm font-bold dark:text-white transition-all focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Mentoria em grupo</label>
                            <input type="text" name="comparison[group_mentorship]" value="{{ old('comparison.group_mentorship', data_get($plan->comparison, 'group_mentorship')) }}"
                                class="w-full px-5 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-sm font-bold dark:text-white transition-all focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Mentoria individual</label>
                            <input type="text" name="comparison[individual_mentorship]" value="{{ old('comparison.individual_mentorship', data_get($plan->comparison, 'individual_mentorship')) }}"
                                class="w-full px-5 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-sm font-bold dark:text-white transition-all focus:border-blue-500 outline-none">
                        </div>
                        <input type="hidden" name="comparison[priority_support]" value="0">
                        <label class="flex items-center justify-between cursor-pointer group p-3 rounded-2xl border border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/30">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Suporte prioritario</span>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="comparison[priority_support]" value="1" class="sr-only peer" {{ old('comparison.priority_support', (bool) data_get($plan->comparison, 'priority_support')) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 rounded-full peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function previewPlanImage(input) {
    var preview = document.getElementById('plan_preview');
    if (!preview || !input.files || !input.files[0]) {
        return;
    }

    var reader = new FileReader();
    reader.onload = function (event) {
        preview.src = event.target.result;
        preview.classList.remove('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}

function markPlanImageForRemoval() {
    var removeInput = document.getElementById('remove_image_input');
    var preview = document.getElementById('plan_preview');

    if (removeInput) {
        removeInput.value = '1';
    }

    if (preview) {
        preview.classList.add('opacity-25', 'grayscale');
    }

    if (typeof toastr !== 'undefined') {
        toastr.info('Imagem marcada para remocao ao salvar.');
    }
}
</script>
@endpush
