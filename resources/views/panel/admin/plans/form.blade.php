@extends('panel.layouts.app')

@section('title', ($plan->id ? 'Editar' : 'Novo') . ' Plano')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.plans.index') }}" class="hover:underline transition-all">Planos</a>
    <span class="mx-2 text-slate-300 dark:text-slate-700 transition-colors">/</span>
    <span class="text-slate-500 dark:text-slate-400 transition-colors">{{ $plan->id ? 'Editar' : 'Novo' }}</span>
@endsection

@section('panel_content')
    <form action="{{ $plan->id ? route('panel.admin.plans.update', $plan) : route('panel.admin.plans.store') }}" 
          method="POST" enctype="multipart/form-data">
        @csrf
        @if($plan->id)
            @method('PUT')
        @endif

        <div class="space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight transition-colors">
                        {{ $plan->id ? 'Editar' : 'Novo' }} Plano
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm transition-colors mt-1 font-medium">Configure os detalhes, preço e benefícios do plano.</p>
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
                        <p class="font-bold text-lg mb-1 tracking-tight">Ops! Encontramos alguns problemas:</p>
                        <ul class="list-disc list-inside text-sm font-medium opacity-90">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- General Info -->
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-8 transition-all hover:shadow-md">
                        <div class="flex items-center gap-3 border-b border-slate-50 dark:border-slate-800 pb-5">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h3 class="font-bold text-xl text-slate-800 dark:text-white transition-colors">Informações Gerais</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors">Nome do Plano</label>
                                <input type="text" name="name" value="{{ old('name', $plan->name) }}" required placeholder="Ex: Black Friday, VIP Mensal"
                                       class="w-full px-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-semibold text-slate-800 dark:text-white placeholder:text-slate-400">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors">Preço (R$)</label>
                                <input type="text" name="price" value="{{ old('price', number_format($plan->price ?? 0, 2, ',', '.')) }}" required
                                       class="w-full px-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-slate-800 dark:text-white mask-money">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors">Ciclo de Cobrança</label>
                                <select name="period" class="w-full px-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-semibold text-slate-800 dark:text-white">
                                    @foreach(['mensal' => 'Mensal', 'trimestral' => 'Trimestral', 'semestral' => 'Semestral', 'anual' => 'Anual', 'vitalício' => 'Vitalício'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('period', $plan->period) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2 transition-colors">Descrição Curta</label>
                                <textarea name="description" rows="3" placeholder="Resumo exibido no checkout"
                                          class="w-full px-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">{{ old('description', $plan->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-8 transition-all hover:shadow-md">
                        <div class="flex items-center gap-3 border-b border-slate-50 dark:border-slate-800 pb-5">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-xl text-slate-800 dark:text-white transition-colors">Recursos Liberados</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Defina o que este plano oferece ao assinante.</p>
                            </div>
                        </div>
                        
                        @php
                            $featureGroups = [
                                'Acesso Básico' => ['community', 'chat', 'connections', 'connections.unlimited'],
                                'Conteúdo' => ['courses', 'courses.certificates', 'courses.downloads'],
                                'Eventos & Mentorias' => ['events', 'events.recordings', 'events.vip', 'mentorships', 'mentorships.group', 'mentorships.individual'],
                                'Extras' => ['rankings', 'support.priority', 'early.access'],
                            ];
                            $selectedFeatures = old('permissions', $plan->permissions ?? []);
                        @endphp

                        <div class="space-y-8">
                            @foreach($featureGroups as $groupName => $keys)
                                <div>
                                    <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 mb-4 uppercase tracking-[0.2em] transition-colors">{{ $groupName }}</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($keys as $key)
                                            @if(isset($planFeatures[$key]))
                                                <label class="flex items-center gap-4 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 hover:bg-white dark:hover:bg-slate-900 hover:border-blue-200 dark:hover:border-blue-900 transition-all cursor-pointer group hover:shadow-sm">
                                                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                                        {{ in_array($key, $selectedFeatures) ? 'checked' : '' }}
                                                        class="w-5 h-5 text-blue-600 border-slate-300 dark:border-slate-700 rounded-lg focus:ring-blue-500 dark:bg-slate-950 transition-all">
                                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">{{ $planFeatures[$key] }}</span>
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-8">
                    <!-- Configs -->
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6 transition-all hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 flex items-center justify-center text-xs">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 dark:text-white transition-colors">Configurações</h3>
                        </div>
                        
                        <div class="space-y-5">
                            <label class="flex items-center justify-between cursor-pointer group p-3 rounded-2xl border border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/30">
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Plano Ativo</span>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                </div>
                            </label>

                            <label class="flex items-center justify-between cursor-pointer group p-3 rounded-2xl border border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/30">
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Destaque (Popular)</span>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="highlight" value="1" class="sr-only peer" {{ old('highlight', $plan->highlight) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                                </div>
                            </label>

                            <label class="flex items-center justify-between cursor-pointer group p-3 rounded-2xl border border-green-100 dark:border-green-900/30 bg-green-50/30 dark:bg-green-950/20">
                                <div>
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Plano Gratuito Padrão</span>
                                    <p class="text-xs text-slate-500 dark:text-slate-500 mt-0.5">Atribuído automaticamente a todo novo cadastro</p>
                                </div>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_free" value="1" class="sr-only peer" {{ old('is_free', $plan->is_free ?? false) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                </div>
                            </label>

                            <div class="pt-4 space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 transition-colors">Slug (URL)</label>
                                <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}" placeholder="Ex: pro, vip"
                                       class="w-full px-4 py-2 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-xs font-mono focus:border-blue-500 outline-none dark:text-white transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Image -->
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 transition-all hover:shadow-md">
                        <h3 class="font-bold text-slate-800 dark:text-white transition-colors mb-4 flex items-center gap-2">
                            <i class="fas fa-image text-blue-500 text-sm"></i> Capa do Plano
                        </h3>
                        
                        <div class="relative w-full aspect-video bg-slate-50 dark:bg-slate-950 rounded-2xl overflow-hidden border-2 border-dashed border-slate-200 dark:border-slate-800 group transition-all">
                            @if($plan->image)
                                <img id="plan_preview" src="{{ asset('storage/'.$plan->image) }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center backdrop-blur-sm">
                                    <label class="cursor-pointer text-white flex flex-col items-center gap-2">
                                        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                            <i class="fas fa-camera"></i>
                                        </div>
                                        <span class="text-xs font-bold uppercase tracking-wider">Trocar Capa</span>
                                        <input type="file" name="image" class="hidden" accept="image/*" onchange="previewImage(this)">
                                    </label>
                                </div>
                            @else
                                <label class="w-full h-full flex flex-col items-center justify-center cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition text-slate-400 dark:text-slate-600">
                                    <i class="fas fa-cloud-upload-alt text-3xl mb-2"></i>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Enviar Imagem</span>
                                    <input type="file" name="image" class="hidden" accept="image/*" onchange="previewImage(this, 'plan_preview')">
                                </label>
                                <img id="plan_preview" class="hidden w-full h-full object-cover">
                            @endif
                        </div>
                        
                        <input type="checkbox" name="remove_image" value="1" class="hidden" id="remove_image_input">
                        @if($plan->image)
                            <button type="button" onclick="removeImage()" class="mt-4 w-full py-2 bg-red-50 dark:bg-red-900/10 text-red-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-100 transition">
                                <i class="fas fa-trash-alt mr-2"></i> Remover Imagem
                            </button>
                        @endif
                    </div>

                    <!-- Benefits (List) -->
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-4 transition-all hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs">
                                <i class="fas fa-list-ul"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 dark:text-white transition-colors">Lista de Benefícios</h3>
                        </div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 italic">Descreva as vantagens exclusivas (uma por linha).</p>
                        <textarea name="benefits" rows="6" 
                                  class="w-full px-5 py-3.5 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-semibold text-slate-700 dark:text-slate-300 placeholder:text-slate-400 leading-relaxed"
                                  placeholder="Suporte prioritário&#10;Grupo exclusivo no Telegram&#10;Mentoria mensal">{{ is_array($plan->benefits) ? implode("\n", $plan->benefits) : $plan->benefits }}</textarea>
                    </div>

                    <!-- Comparison Data -->
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6 transition-all hover:shadow-md">
                        <div class="flex items-center gap-3 border-b border-slate-50 dark:border-slate-800 pb-4">
                            <div class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-500 flex items-center justify-center text-xs">
                                <i class="fas fa-table"></i>
                            </div>
                            <h3 class="font-bold text-slate-800 dark:text-white transition-colors">Comparativo</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 transition-colors">Conexões / Mês</label>
                                <input type="text" name="comparison[connections_per_month]" 
                                       value="{{ old('comparison.connections_per_month', data_get($plan->comparison, 'connections_per_month')) }}"
                                       placeholder="Ex: Ilimitadas"
                                       class="w-full px-5 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-sm font-bold dark:text-white transition-all focus:border-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 transition-colors">Mentoria Individual</label>
                                <input type="text" name="comparison[individual_mentorship]" 
                                       value="{{ old('comparison.individual_mentorship', data_get($plan->comparison, 'individual_mentorship')) }}"
                                       placeholder="Ex: 1/mês"
                                       class="w-full px-5 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-sm font-bold dark:text-white transition-all focus:border-blue-500 outline-none">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function previewImage(input, previewId = 'plan_preview') {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                // If it was a label, hide it (logic depends on initial state)
                const label = input.closest('label');
                if(label && label.querySelector('i')) {
                    // This is handled by CSS or dynamic classes if needed
                }
            }
            reader.readAsDataURL(input.files[0]);
            toastr.success('Imagem selecionada!');
        }
    }

    function removeImage() {
        Swal.fire({
            title: 'Remover Capa?',
            text: "A imagem será excluída permanentemente ao salvar o formulário.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, marcar para remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('remove_image_input').checked = true;
                const preview = document.getElementById('plan_preview');
                if(preview) preview.classList.add('opacity-25', 'grayscale');
                toastr.info('Capa marcada para remoção.');
            }
        });
    }
</script>
@endpush
