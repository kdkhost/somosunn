@extends('panel.layouts.app')

@section('title', $vacancy->exists ? 'Editar Minha Vaga' : 'Publicar Nova Vaga')

@section('panel_content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">
                    {{ $vacancy->exists ? 'Editar Vaga: ' . $vacancy->title : 'Publicar Oportunidade' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">
                    @if(!$vacancy->exists)
                        Ao publicar, todos os membros da comunidade serão notificados imediatamente.
                    @else
                        Atualize os detalhes da sua vaga publicada.
                    @endif
                </p>
            </div>
            <a href="{{ route('panel.my-jobs.index') }}"
                class="text-sm font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>

        <form action="{{ $vacancy->exists ? route('panel.my-jobs.update', $vacancy) : route('panel.my-jobs.store') }}"
            method="POST" class="space-y-6" id="jobForm">
            @csrf
            @if($vacancy->exists) @method('PUT') @endif

            <div
                class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 space-y-8 transition-colors">
                {{-- Basic Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Título
                            da Vaga</label>
                        <input type="text" name="title" value="{{ old('title', $vacancy->title) }}" required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                            placeholder="Ex: Desenvolvedor Full Stack Sênior">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Nome
                            da Empresa</label>
                        <input type="text" name="company_name"
                            value="{{ old('company_name', $vacancy->company_name ?? auth()->user()->company) }}"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                            placeholder="Ex: Minha Empresa Ltda">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Localização
                            / Remoto</label>
                        <input type="text" name="location" value="{{ old('location', $vacancy->location) }}"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                            placeholder="Ex: São Paulo, SP / Remoto">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Tipo
                            de Contrato</label>
                        <select name="type" required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                            <option value="CLT" @selected(old('type', $vacancy->type) == 'CLT')>CLT</option>
                            <option value="PJ" @selected(old('type', $vacancy->type) == 'PJ')>PJ</option>
                            <option value="Freelance" @selected(old('type', $vacancy->type) == 'Freelance')>Freelance</option>
                            <option value="Estágio" @selected(old('type', $vacancy->type) == 'Estágio')>Estágio</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Faixa
                            Salarial (Opcional)</label>
                        <input type="text" name="salary_range" value="{{ old('salary_range', $vacancy->salary_range) }}"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                            placeholder="Ex: R$ 5.000 - R$ 8.000">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Data
                            de Expiração (Opcional)</label>
                        <input type="date" name="expires_at"
                            value="{{ old('expires_at', $vacancy->expires_at ? $vacancy->expires_at->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Visibilidade</label>
                        <select name="visibility" required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                            <option value="internal" @selected(old('visibility', $vacancy->visibility) == 'internal')>Somente
                                Comunidade (Interno)</option>
                            <option value="external" @selected(old('visibility', $vacancy->visibility) == 'external')>Somente
                                Público (Externo)</option>
                            <option value="both" @selected(old('visibility', $vacancy->visibility) == 'both' || !$vacancy->exists)>Ambos (Interno e Público)</option>
                        </select>
                    </div>
                </div>

                {{-- Descriptions --}}
                <div class="space-y-6">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Resumo
                            da Vaga (Aparece na listagem)</label>
                        <textarea name="short_description" rows="3" maxlength="500"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                            placeholder="Breve resumo da oportunidade...">{{ old('short_description', $vacancy->short_description) }}</textarea>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Descrição
                            detalhada</label>
                        <textarea name="description" id="jobDescription" rows="10"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">{{ old('description', $vacancy->description) }}</textarea>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Requisitos</label>
                        <textarea name="requirements" id="jobRequirements" rows="10"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">{{ old('requirements', $vacancy->requirements) }}</textarea>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Benefícios</label>
                        <textarea name="benefits" id="jobBenefits" rows="10"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">{{ old('benefits', $vacancy->benefits) }}</textarea>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div
                    class="flex items-center justify-end pt-6 border-t border-slate-100 dark:border-slate-800 transition-colors">
                    <button type="submit"
                        class="px-10 py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-xl shadow-blue-500/30 transition-all flex items-center gap-2">
                        <i class="fas fa-rocket"></i>
                        <span>{{ $vacancy->exists ? 'Salvar Alterações' : 'Publicar Vaga Agora' }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                const summernoteConfig = {
                    height: 300,
                    lang: 'pt-BR',
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                };

                $('#jobDescription').summernote({
                    ...summernoteConfig,
                    placeholder: 'Descreva detalhadamente as responsabilidades e o perfil desejado...'
                });

                $('#jobRequirements').summernote({
                    ...summernoteConfig,
                    placeholder: 'Liste o que é essencial para o candidato...'
                });

                $('#jobBenefits').summernote({
                    ...summernoteConfig,
                    placeholder: 'Quais as vantagens de trabalhar com você?'
                });
            });
        </script>
    @endpush
@endsection