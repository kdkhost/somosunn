@extends('panel.layouts.app')

@php
    $isEdit = $magazine->exists;
    $action = $isEdit
        ? route('panel.admin.magazines.update', $magazine)
        : route('panel.admin.magazines.store');
@endphp

@section('title', $isEdit ? 'Editar Revista' : 'Nova Revista')

@push('styles')
<style>
    /* Ajuste FilePond para PDFs — visual mais alto e centrado */
    .mag-pdf-pond .filepond--root {
        min-height: 180px;
    }
    .mag-pdf-pond .filepond--drop-label {
        min-height: 180px;
        font-weight: 600;
    }
    .mag-pdf-pond .filepond--panel-root {
        background-color: rgb(248 250 252 / 1);
        border: 2px dashed rgb(203 213 225 / 1);
        border-radius: 1rem;
    }
    .dark .mag-pdf-pond .filepond--panel-root {
        background-color: rgb(15 23 42 / 0.5);
        border-color: rgb(51 65 85 / 1);
    }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-100 dark:border-slate-800 overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white text-2xl">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-white">{{ $isEdit ? 'Editar Revista' : 'Nova Revista' }}</h1>
                        <p class="text-purple-100 text-sm">Publique edicoes em PDF com visualizacao tipo flipbook</p>
                    </div>
                </div>
                <a href="{{ route('panel.admin.magazines.index') }}" class="text-white/80 hover:text-white text-sm font-bold flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <div class="p-6 sm:p-8">
            @if($errors->any())
                <div class="rounded-2xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 text-sm mb-6">
                    <div class="font-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Corrija os erros abaixo:</div>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $action }}" enctype="multipart/form-data" data-no-ajax="true">
                @csrf
                @if($isEdit) @method('PUT') @endif

                {{-- Informacoes basicas --}}
                <div class="mb-8">
                    <h3 class="text-xs uppercase tracking-widest font-black text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 bg-purple-500 rounded-full"></span>
                        Informacoes basicas
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div class="md:col-span-6">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Titulo *</label>
                            <input type="text" name="title" required value="{{ old('title', $magazine->title) }}"
                                placeholder="Ex: Revista Manchete - Edicao 7"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        </div>

                        <div class="md:col-span-3">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Categoria</label>
                            <input type="text" name="category" value="{{ old('category', $magazine->category) }}"
                                placeholder="Ex: Moda, Manchetes, Tecnologia"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Edicao / Numero</label>
                            <input type="text" name="edition" value="{{ old('edition', $magazine->edition) }}"
                                placeholder="Ex: #01 - Jan/2026"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        </div>

                        <div class="md:col-span-1">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Data publicacao</label>
                            <input type="date" name="published_at" value="{{ old('published_at', optional($magazine->published_at)->format('Y-m-d')) }}"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        </div>

                        <div class="md:col-span-6">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Resumo curto</label>
                            <textarea name="short_description" rows="2" maxlength="500" placeholder="Um paragrafo que aparece na vitrine"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">{{ old('short_description', $magazine->short_description) }}</textarea>
                        </div>

                        <div class="md:col-span-6">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Descricao completa</label>
                            <textarea name="full_description" rows="5" placeholder="Descricao aparece na pagina da revista"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">{{ old('full_description', $magazine->full_description) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Arquivos (drag-and-drop) --}}
                <div class="mb-8">
                    <h3 class="text-xs uppercase tracking-widest font-black text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 bg-purple-500 rounded-full"></span>
                        Arquivos
                    </h3>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Capa --}}
                        <div>
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">
                                Capa da revista
                                <span class="text-xs font-medium text-slate-400 ml-1">(imagem, proporcao 3:4 recomendada)</span>
                            </label>
                            <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp"
                                class="filepond" data-max-files="1">
                            @if($magazine->thumbnail_url)
                                <div class="mt-2 flex items-center gap-3 text-xs text-slate-500">
                                    <img src="{{ $magazine->thumbnail_url }}" class="w-16 h-20 object-cover rounded-lg shadow">
                                    <span>Capa atual (sera substituida se voce enviar uma nova)</span>
                                </div>
                            @endif
                        </div>

                        {{-- PDF --}}
                        <div>
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">
                                Arquivo PDF {{ $isEdit ? '' : '*' }}
                                <span class="text-xs font-medium text-slate-400 ml-1">(ate 100 MB)</span>
                            </label>
                            <div class="mag-pdf-pond">
                                <input type="file" name="pdf_file" accept="application/pdf"
                                    class="filepond" data-max-files="1" {{ !$isEdit ? 'required' : '' }}>
                            </div>
                            @if($magazine->pdf_url)
                                <div class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                                    <i class="fas fa-file-pdf text-red-500"></i>
                                    <a href="{{ $magazine->pdf_url }}" target="_blank" class="text-purple-600 hover:underline font-bold">Abrir PDF atual</a>
                                    <span>({{ number_format(($magazine->file_size_kb ?? 0) / 1024, 1) }} MB &middot; {{ $magazine->pages_count ?? '?' }} paginas)</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Publicacao --}}
                <div class="mb-8">
                    <h3 class="text-xs uppercase tracking-widest font-black text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 bg-purple-500 rounded-full"></span>
                        Publicacao e visibilidade
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Status *</label>
                            <select name="status" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                                <option value="draft" @selected(old('status', $magazine->status) === 'draft')>Rascunho</option>
                                <option value="published" @selected(old('status', $magazine->status ?: 'draft') === 'published')>Publicada</option>
                                <option value="archived" @selected(old('status', $magazine->status) === 'archived')>Arquivada</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Visibilidade *</label>
                            <select name="visibility" required class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                                <option value="interest" @selected(old('visibility', $magazine->visibility ?: 'interest') === 'interest')>So interessados em "Noticias"</option>
                                <option value="members" @selected(old('visibility', $magazine->visibility) === 'members')>Todos os membros</option>
                                <option value="public" @selected(old('visibility', $magazine->visibility) === 'public')>Publico (visitantes)</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Numero de paginas (opcional)</label>
                            <input type="number" name="pages_count" min="1" max="2000" value="{{ old('pages_count', $magazine->pages_count) }}"
                                placeholder="Detectado automaticamente"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-5 px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $magazine->is_featured)) class="rounded text-purple-600 focus:ring-purple-500">
                            <i class="fas fa-star text-amber-500"></i> Destaque na vitrine
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="hidden" name="allow_download" value="0">
                            <input type="checkbox" name="allow_download" value="1" @checked(old('allow_download', $magazine->exists ? $magazine->allow_download : true)) class="rounded text-purple-600 focus:ring-purple-500">
                            <i class="fas fa-download text-blue-500"></i> Permitir download do PDF
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="hidden" name="enable_sound" value="0">
                            <input type="checkbox" name="enable_sound" value="1" @checked(old('enable_sound', $magazine->exists ? $magazine->enable_sound : true)) class="rounded text-purple-600 focus:ring-purple-500">
                            <i class="fas fa-volume-high text-green-500"></i> Som de virar pagina
                        </label>
                    </div>
                </div>

                {{-- Acoes --}}
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('panel.admin.magazines.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-sm transition">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-sm shadow-lg shadow-purple-500/30 transition">
                        <i class="fas fa-save mr-1"></i> Salvar Revista
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
