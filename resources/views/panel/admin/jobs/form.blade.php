@extends('panel.layouts.app')

@section('title', $vacancy->exists ? 'Editar Vaga' : 'Nova Vaga')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">
                    {{ $vacancy->exists ? 'Editar Vaga: ' . $vacancy->title : 'Criar Nova Vaga' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Configure os detalhes da
                    oportunidade de emprego.</p>
            </div>
            <a href="{{ route('admin.jobs.index') }}"
                class="text-sm font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>

        <form action="{{ $vacancy->exists ? route('admin.jobs.update', $vacancy) : route('admin.jobs.store') }}"
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
                        <input type="text" name="company_name" value="{{ old('company_name', $vacancy->company_name) }}"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                            placeholder="Ex: Kdkhost Soluções">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Localização
                            / Remoto</label>
                        <input type="text" name="location" value="{{ old('location', $vacancy->location) }}"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                            placeholder="Ex: Curitiba, PR / Remoto">
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
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Data
                            de Expiração (Opcional)</label>
                        <input type="date" name="expires_at"
                            value="{{ old('expires_at', $vacancy->expires_at ? $vacancy->expires_at->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                    </div>
                </div>

                {{-- Descriptions --}}
                <div class="space-y-6">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Resumo
                            da Vaga (Aparece na listagem)</label>
                        <textarea name="short_description" rows="3"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">{{ old('short_description', $vacancy->short_description) }}</textarea>
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
                    class="flex items-center justify-between pt-6 border-t border-slate-100 dark:border-slate-800 transition-colors">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $vacancy->is_active ?? true))
                                class="w-5 h-5 text-blue-600 border-slate-300 rounded-lg focus:ring-blue-500 bg-white transition-all">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Vaga Ativa</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-xl shadow-blue-500/30 transition-all flex items-center gap-2">
                        <i class="fas fa-check"></i>
                        <span>{{ $vacancy->exists ? 'Atualizar Vaga' : 'Publicar Vaga' }}</span>
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
                    placeholder: 'Descreva detalhadamente a vaga...'
                });

                $('#jobRequirements').summernote({
                    ...summernoteConfig,
                    placeholder: 'Liste os requisitos técnicos e comportamentais...'
                });

                $('#jobBenefits').summernote({
                    ...summernoteConfig,
                    placeholder: 'Liste os benefícios oferecidos (VA, VR, Plano de Saúde, etc)...'
                });
            });
        </script>
    @endpush
@endsection