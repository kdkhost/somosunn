@extends('panel.layouts.app')

@section('title', $faq->exists ? 'Editar Pergunta' : 'Nova Pergunta')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">
                    {{ $faq->exists ? 'Editar FAQ' : 'Nova FAQ' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Defina a pergunta e a resposta
                    para o seu público.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('panel.admin.faqs.index') }}"
                    class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                    Cancelar
                </a>
                <button type="submit" form="faqForm"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <span>Salvar Pergunta</span>
                </button>
            </div>
        </div>

        <form id="faqForm"
            action="{{ $faq->exists ? route('panel.admin.faqs.update', $faq) : route('panel.admin.faqs.store') }}"
            method="POST" class="space-y-6">
            @csrf
            @if($faq->exists) @method('PUT') @endif

            <div
                class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 space-y-6 transition-colors duration-300">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Contexto
                            / Categoria</label>
                        <select name="context" required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">
                            @foreach($contexts as $key => $label)
                                <option value="{{ $key }}" @selected(old('context', $faq->context) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">Ordem
                            de Exibição</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $faq->sort_order ?: 0) }}"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-bold">
                    </div>
                </div>

                <div>
                    <label
                        class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">A
                        Pergunta</label>
                    <input type="text" name="question" value="{{ old('question', $faq->question) }}" required
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                        placeholder="Ex: Como acesso meu certificado?">
                </div>

                <div>
                    <label
                        class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 transition-colors">A
                        Resposta</label>
                    <textarea name="answer" id="answerEditor" rows="10" required
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">{{ old('answer', $faq->answer) }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="isActive" value="1" @checked(old('is_active', $faq->exists ? $faq->is_active : true))
                        class="w-4 h-4 text-blue-600 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 rounded focus:ring-blue-500 transition-colors">
                    <label for="isActive"
                        class="text-sm font-semibold text-slate-700 dark:text-slate-300 transition-colors">Esta pergunta
                        está ativa e
                        visível</label>
                </div>
            </div>
        </form>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    @endpush
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#answerEditor').summernote({
                    placeholder: 'Escreva a resposta aqui...',
                    tabsize: 2,
                    height: 300,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                });
            });
        </script>
    @endpush
@endsection