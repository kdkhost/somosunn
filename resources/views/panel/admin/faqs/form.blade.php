@extends('panel.layouts.app')

@section('title', $faq->exists ? 'Editar Pergunta' : 'Nova Pergunta')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ $faq->exists ? 'Editar FAQ' : 'Nova FAQ' }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">Defina a pergunta e a resposta para o seu público.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('panel.admin.faqs.index') }}"
                    class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                    Cancelar
                </a>
                <button type="submit" form="faqForm"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center gap-2">
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

            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Contexto / Categoria</label>
                        <select name="context" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 font-medium">
                            @foreach($contexts as $key => $label)
                                <option value="{{ $key }}" @selected(old('context', $faq->context) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Ordem de Exibição</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $faq->sort_order ?: 0) }}"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 font-bold">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">A Pergunta</label>
                    <input type="text" name="question" value="{{ old('question', $faq->question) }}" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 font-medium"
                        placeholder="Ex: Como acesso meu certificado?">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">A Resposta</label>
                    <textarea name="answer" id="answerEditor" rows="10" required
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 font-medium">{{ old('answer', $faq->answer) }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="isActive" value="1" @checked(old('is_active', $faq->exists ? $faq->is_active : true)) class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                    <label for="isActive" class="text-sm font-semibold text-slate-700">Esta pergunta está ativa e
                        visível</label>
                </div>
            </div>
        </form>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
        <style>
            .note-editor.note-frame {
                border: 1px solid #e2e8f0;
                border-radius: 1rem;
                overflow: hidden;
                background: #f8fafc;
            }

            .note-toolbar {
                background: #f1f5f9;
                border-bottom: 1px solid #e2e8f0;
                padding: 0.5rem;
            }
        </style>
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