@extends('panel.layouts.app')

@section('title', ($plan->id ? 'Editar' : 'Novo') . ' Plano')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.plans.index') }}" class="hover:underline">Planos</a>
    <span class="mx-2">/</span>
    <span class="text-slate-500">{{ $plan->id ? 'Editar' : 'Novo' }}</span>
@endsection

@section('panel_content')
    <form action="{{ $plan->id ? route('panel.admin.plans.update', $plan) : route('panel.admin.plans.store') }}" 
          method="POST" enctype="multipart/form-data">
        @csrf
        @if($plan->id)
            @method('PUT')
        @endif

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">
                        {{ $plan->id ? 'Editar' : 'Novo' }} Plano
                    </h2>
                    <p class="text-slate-500 text-sm">Configure os detalhes, preço e benefícios.</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('panel.admin.plans.index') }}" 
                       class="bg-white border border-slate-200 text-slate-600 font-bold py-2 px-4 rounded-xl hover:bg-slate-50 transition">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02]">
                        <i class="fas fa-save mr-2"></i> Salvar
                    </button>
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-start gap-3">
                    <i class="fas fa-exclamation-circle mt-0.5"></i>
                    <div>
                        <p class="font-bold">Atenção!</p>
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- General Info -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-5">
                        <h3 class="font-bold text-lg text-slate-800 border-b border-slate-100 pb-2 mb-4">Informações Gerais</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-1">Nome do Plano</label>
                                <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                                       class="w-full rounded-xl border-slate-400 bg-slate-50 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all font-medium text-slate-800">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Preço (R$)</label>
                                <input type="text" name="price" value="{{ old('price', number_format($plan->price ?? 0, 2, ',', '.')) }}" required
                                       class="w-full rounded-xl border-slate-400 bg-slate-50 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all font-medium text-slate-800 mask-money">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Ciclo de Cobrança</label>
                                <select name="period" class="w-full rounded-xl border-slate-400 bg-slate-50 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all font-medium text-slate-800">
                                    @foreach(['mensal','trimestral','semestral','anual','vitalício'] as $p)
                                        <option value="{{ $p }}" {{ old('period', $plan->period) == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-1">Descrição Curta</label>
                                <textarea name="description" rows="2" 
                                          class="w-full rounded-xl border-slate-400 bg-slate-50 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all font-medium text-slate-800">{{ old('description', $plan->description) }}</textarea>
                                <p class="text-xs text-slate-500 mt-1">Breve resumo exibido no card do plano.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                        <h3 class="font-bold text-lg text-slate-800 border-b border-slate-100 pb-2 mb-4">
                            Recursos Liberados (Permissões)
                            <span class="block text-xs font-normal text-slate-500 mt-1">Marque o que o usuário terá acesso ao assinar este plano.</span>
                        </h3>
                        
                        @php
                            $featureGroups = [
                                'Acesso Básico' => ['community', 'chat', 'connections', 'connections.unlimited'],
                                'Cursos' => ['courses', 'courses.certificates', 'courses.downloads'],
                                'Eventos' => ['events', 'events.recordings', 'events.vip'],
                                'Mentorias' => ['mentorships', 'mentorships.group', 'mentorships.individual'],
                                'Extras' => ['rankings', 'support.priority', 'early.access'],
                            ];
                            $selectedFeatures = old('permissions', $plan->permissions ?? []);
                        @endphp

                        <div class="space-y-6">
                            @foreach($featureGroups as $groupName => $keys)
                                <div>
                                    <h4 class="font-bold text-slate-700 mb-3 text-sm uppercase tracking-wider">{{ $groupName }}</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($keys as $key)
                                            @if(isset($planFeatures[$key]))
                                                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 transition cursor-pointer group">
                                                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                                        {{ in_array($key, $selectedFeatures) ? 'checked' : '' }}
                                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                    <span class="text-sm font-medium text-slate-700 group-hover:text-blue-700 transition">{{ $planFeatures[$key] }}</span>
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
                <div class="space-y-6">
                    <!-- Configs -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-4">
                        <h3 class="font-bold text-slate-800 mb-2">Configurações</h3>
                        
                        <label class="flex items-center justify-between cursor-pointer group">
                            <span class="text-sm font-bold text-slate-700">Plano Ativo</span>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </div>
                        </label>

                        <label class="flex items-center justify-between cursor-pointer group">
                            <span class="text-sm font-bold text-slate-700">Destaque (Popular)</span>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="highlight" value="1" class="sr-only peer" {{ old('highlight', $plan->highlight) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-400"></div>
                            </div>
                        </label>

                        <div class="pt-4 border-t border-slate-100">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Slug (URL)</label>
                            <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}" placeholder="Ex: pro, vip"
                                   class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Image -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-4">
                        <h3 class="font-bold text-slate-800 mb-2">Imagem de Capa</h3>
                        
                        <div class="relative w-full h-40 bg-slate-100 rounded-xl overflow-hidden border border-slate-200 group">
                            @if($plan->image)
                                <img src="{{ asset('storage/'.$plan->image) }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                    <label class="cursor-pointer text-white flex flex-col items-center gap-1">
                                        <i class="fas fa-camera text-2xl"></i>
                                        <span class="text-xs font-bold">Trocar Imagem</span>
                                        <input type="file" name="image" class="hidden" accept="image/*" onchange="previewImage(this)">
                                    </label>
                                </div>
                            @else
                                <label class="w-full h-full flex flex-col items-center justify-center cursor-pointer hover:bg-slate-200 transition text-slate-400">
                                    <i class="fas fa-image text-3xl mb-2"></i>
                                    <span class="text-xs font-bold">Upload Imagem</span>
                                    <input type="file" name="image" class="hidden" accept="image/*" onchange="previewImage(this)">
                                </label>
                            @endif
                        </div>
                        <input type="checkbox" name="remove_image" value="1" class="hidden" id="remove_image_input">
                        @if($plan->image)
                            <button type="button" onclick="removeImage()" class="text-xs text-red-600 hover:underline font-bold">
                                <i class="fas fa-trash mr-1"></i> Remover imagem atual
                            </button>
                        @endif
                    </div>

                    <!-- Benefits (List) -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-2">
                        <h3 class="font-bold text-slate-800 mb-2">Lista de Benefícios</h3>
                        <p class="text-xs text-slate-500">Exibido na lista de recursos do card (um por linha).</p>
                        <textarea name="benefits" rows="6" 
                                  class="w-full rounded-xl border-slate-400 bg-slate-50 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition-all font-medium text-slate-800"
                                  placeholder="Mentorias semanais&#10;Grupo VIP&#10;Acesso vitalício">{{ is_array($plan->benefits) ? implode("\n", $plan->benefits) : $plan->benefits }}</textarea>
                    </div>

                    <!-- Comparison Data -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-4">
                        <h3 class="font-bold text-slate-800 mb-2">Dados Comparativos</h3>
                        <p class="text-xs text-slate-500 mb-3">Usados na tabela de comparação completa (/premium).</p>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Conexões / Mês</label>
                            <input type="text" name="comparison[connections_per_month]" 
                                   value="{{ old('comparison.connections_per_month', data_get($plan->comparison, 'connections_per_month')) }}"
                                   placeholder="Ex: Ilimitadas"
                                   class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mentoria Individual</label>
                            <input type="text" name="comparison[individual_mentorship]" 
                                   value="{{ old('comparison.individual_mentorship', data_get($plan->comparison, 'individual_mentorship')) }}"
                                   placeholder="Ex: 1/mês"
                                   class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            // Check if preview img exists, if not replace the empty state
            // Simplified for now: just reload page or use complex JS? 
            // Let's keep it simple: We just change text to "Selecionado"
            const label = input.parentElement.querySelector('span');
            if(label) label.innerText = 'Imagem Selecionada';
        }
    }

    function removeImage() {
        if(confirm('A imagem será removida ao salvar. Continuar?')) {
            document.getElementById('remove_image_input').checked = true;
            // Visual feedback could be added here
        }
    }
</script>
@endpush
