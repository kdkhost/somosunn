@extends('panel.layouts.app')

@php
    $isEdit = $magazine->exists;
    $action = $isEdit
        ? route('panel.admin.magazines.update', $magazine)
        : route('panel.admin.magazines.store');
@endphp

@section('title', $isEdit ? 'Editar Revista' : 'Nova Revista')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-book-open text-purple-500"></i>
                {{ $isEdit ? 'Editar Revista' : 'Nova Revista' }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Publique edições em PDF com visualização interativa</p>
        </div>
        <a href="{{ route('panel.admin.magazines.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-sm">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-2xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 text-sm mb-4">
            <div class="font-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Corrija os erros abaixo:</div>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" data-no-ajax="true">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Coluna principal --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Informacoes basicas --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
                    <h3 class="text-xs uppercase tracking-widest font-black text-slate-500 dark:text-slate-400 mb-4 flex items-center gap-2">
                        <span class="w-1 h-4 bg-purple-500 rounded-full"></span>
                        Informacoes basicas
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Título *</label>
                            <input type="text" name="title" required value="{{ old('title', $magazine->title) }}"
                                placeholder="Ex.: Revista Manchete - Edição 7"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                            <div class="md:col-span-3">
                                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Categoria</label>
                                <input type="text" name="category" value="{{ old('category', $magazine->category) }}"
                                    placeholder="Ex: Moda, Manchetes, Tecnologia"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Edição / Número</label>
                                <input type="text" name="edition" value="{{ old('edition', $magazine->edition) }}"
                                    placeholder="Ex.: nº 01 - jan./2026"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            </div>
                            <div class="md:col-span-1">
                                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Data de publicação</label>
                                <input type="date" name="published_at" value="{{ old('published_at', optional($magazine->published_at)->format('Y-m-d')) }}"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Resumo curto</label>
                            <textarea name="short_description" rows="2" maxlength="500"
                                placeholder="Parágrafo exibido na vitrine"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">{{ old('short_description', $magazine->short_description) }}</textarea>
                        </div>

                        <div>
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Descrição completa</label>
                            <textarea name="full_description" rows="5"
                                placeholder="Descrição detalhada exibida na página da revista"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition">{{ old('full_description', $magazine->full_description) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Arquivos --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
                    <h3 class="text-xs uppercase tracking-widest font-black text-slate-500 dark:text-slate-400 mb-4 flex items-center gap-2">
                        <span class="w-1 h-4 bg-purple-500 rounded-full"></span>
                        Arquivos
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">
                                Capa da revista
                                <span class="text-xs font-medium text-slate-400 ml-1">(proporção 3:4)</span>
                            </label>
                            <x-unn-dropzone
                                name="thumbnail"
                                accept="image/jpeg,image/png,image/webp"
                                label="Arraste a capa aqui"
                                hint="ou clique para selecionar uma imagem"
                                icon="far fa-image"
                                :is-image="true"
                                :max-size-mb="10"
                                :current-url="$magazine->thumbnail_url"
                                current-label="Capa atual"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">
                                Arquivo PDF {{ $isEdit ? '' : '*' }}
                                <span class="text-xs font-medium text-slate-400 ml-1">(até 100 MB)</span>
                            </label>
                            <x-unn-dropzone
                                name="pdf_file"
                                accept="application/pdf"
                                label="Arraste o PDF aqui"
                                hint="ou clique para selecionar um arquivo PDF"
                                icon="fas fa-file-pdf"
                                :required="!$isEdit"
                                :max-size-mb="100"
                                :current-url="$magazine->pdf_url"
                                :current-label="$magazine->pdf_url ? 'Abrir PDF atual (' . number_format(($magazine->file_size_kb ?? 0) / 1024, 1) . ' MB)' : null"
                            />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Coluna lateral --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Publicação --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
                    <h3 class="text-xs uppercase tracking-widest font-black text-slate-500 dark:text-slate-400 mb-4 flex items-center gap-2">
                        <span class="w-1 h-4 bg-green-500 rounded-full"></span>
                        Publicação
                    </h3>

                    <div class="space-y-4">
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
                                <option value="interest" @selected(old('visibility', $magazine->visibility ?: 'interest') === 'interest')>Somente interessados em "Notícias"</option>
                                <option value="members" @selected(old('visibility', $magazine->visibility) === 'members')>Todos os membros</option>
                                <option value="public" @selected(old('visibility', $magazine->visibility) === 'public')>Público (visitantes)</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2">Número de páginas</label>
                            <input type="number" name="pages_count" min="1" max="2000" value="{{ old('pages_count', $magazine->pages_count) }}"
                                placeholder="Opcional"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20">
                        </div>
                    </div>
                </div>

                {{-- Opções --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
                    <h3 class="text-xs uppercase tracking-widest font-black text-slate-500 dark:text-slate-400 mb-4 flex items-center gap-2">
                        <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
                        Opções
                    </h3>

                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $magazine->is_featured)) class="rounded text-purple-600 focus:ring-purple-500">
                            <span class="flex-1 text-sm font-bold text-slate-700 dark:text-slate-300"><i class="fas fa-star text-amber-500 mr-1"></i>Destaque na vitrine</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            <input type="hidden" name="allow_download" value="0">
                            <input type="checkbox" name="allow_download" value="1" @checked(old('allow_download', $magazine->exists ? $magazine->allow_download : true)) class="rounded text-purple-600 focus:ring-purple-500">
                            <span class="flex-1 text-sm font-bold text-slate-700 dark:text-slate-300"><i class="fas fa-download text-blue-500 mr-1"></i>Permitir baixar o PDF</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            <input type="hidden" name="enable_sound" value="0">
                            <input type="checkbox" name="enable_sound" value="1" @checked(old('enable_sound', $magazine->exists ? $magazine->enable_sound : true)) class="rounded text-purple-600 focus:ring-purple-500">
                            <span class="flex-1 text-sm font-bold text-slate-700 dark:text-slate-300"><i class="fas fa-volume-high text-green-500 mr-1"></i>Som de virar página</span>
                        </label>
                    </div>
                </div>

                {{-- Ações --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
                    <button type="submit" class="w-full px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-black shadow-lg shadow-purple-500/30 transition">
                        <i class="fas fa-save mr-1"></i> Salvar Revista
                    </button>
                    <a href="{{ route('panel.admin.magazines.index') }}" class="mt-2 block text-center px-6 py-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-sm transition">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
@endsection
