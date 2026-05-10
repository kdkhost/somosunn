@php
    $isPanel = request()->routeIs('panel.*');
    $layout = $isPanel ? 'panel.layouts.app' : 'admin.layouts.app';
    $indexRoute = $isPanel ? 'panel.admin.magazines.index' : 'admin.magazines.index';
    $storeRoute = $isPanel ? 'panel.admin.magazines.store' : 'admin.magazines.store';
    $updateRoute = $isPanel ? 'panel.admin.magazines.update' : 'admin.magazines.update';
    $isEdit = $magazine->exists;
    $action = $isEdit ? route($updateRoute, $magazine) : route($storeRoute);
@endphp
@extends($layout)

@section('title', $isEdit ? 'Editar Revista' : 'Nova Revista')
@section('page_title', $isEdit ? 'Editar Revista' : 'Nova Revista')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($indexRoute) }}">Revistas</a></li>
    <li class="breadcrumb-item active">{{ $isEdit ? 'Editar' : 'Nova' }}</li>
@endsection

@section('content')
<div class="{{ $isPanel ? 'max-w-5xl mx-auto p-4 sm:p-6 lg:p-8' : '' }}">
<div class="{{ $isPanel ? 'bg-white dark:bg-slate-900 rounded-2xl shadow border border-slate-100 dark:border-slate-800 p-6' : 'card' }}">
    <div class="{{ $isPanel ? '' : 'card-header' }}">
        <h5 class="{{ $isPanel ? 'text-2xl font-black text-slate-900 dark:text-white mb-4 flex items-center gap-3' : 'mb-0' }}">
            <i class="fas fa-book-open text-purple-500"></i>
            {{ $isEdit ? 'Editar Revista' : 'Nova Revista' }}
        </h5>
    </div>
    <div class="{{ $isPanel ? '' : 'card-body' }}">
        @if($errors->any())
            <div class="{{ $isPanel ? 'rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm mb-4' : 'alert alert-danger' }}">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" data-no-ajax="true">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="{{ $isPanel ? 'grid grid-cols-1 md:grid-cols-2 gap-4' : 'row' }}">
                {{-- Titulo --}}
                <div class="{{ $isPanel ? 'md:col-span-2' : 'col-md-12 form-group' }}">
                    <label class="{{ $isPanel ? 'text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2' : '' }}">Titulo *</label>
                    <input type="text" name="title" value="{{ old('title', $magazine->title) }}" required
                        class="{{ $isPanel ? 'w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500' : 'form-control' }}">
                </div>

                {{-- Categoria --}}
                <div class="{{ $isPanel ? '' : 'col-md-6 form-group' }}">
                    <label class="{{ $isPanel ? 'text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2' : '' }}">Categoria</label>
                    <input type="text" name="category" value="{{ old('category', $magazine->category) }}" placeholder="Ex: Moda, Manchetes, Tecnologia"
                        class="{{ $isPanel ? 'w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500' : 'form-control' }}">
                </div>

                {{-- Edicao --}}
                <div class="{{ $isPanel ? '' : 'col-md-3 form-group' }}">
                    <label class="{{ $isPanel ? 'text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2' : '' }}">Edicao / Numero</label>
                    <input type="text" name="edition" value="{{ old('edition', $magazine->edition) }}" placeholder="Ex: #01 - Jan/2026"
                        class="{{ $isPanel ? 'w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500' : 'form-control' }}">
                </div>

                {{-- Data de publicacao --}}
                <div class="{{ $isPanel ? '' : 'col-md-3 form-group' }}">
                    <label class="{{ $isPanel ? 'text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2' : '' }}">Data publicacao</label>
                    <input type="date" name="published_at" value="{{ old('published_at', optional($magazine->published_at)->format('Y-m-d')) }}"
                        class="{{ $isPanel ? 'w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500' : 'form-control' }}">
                </div>

                {{-- Resumo curto --}}
                <div class="{{ $isPanel ? 'md:col-span-2' : 'col-md-12 form-group' }}">
                    <label class="{{ $isPanel ? 'text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2' : '' }}">Resumo curto</label>
                    <textarea name="short_description" rows="2" maxlength="500"
                        class="{{ $isPanel ? 'w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500' : 'form-control' }}">{{ old('short_description', $magazine->short_description) }}</textarea>
                </div>

                {{-- Descricao completa --}}
                <div class="{{ $isPanel ? 'md:col-span-2' : 'col-md-12 form-group' }}">
                    <label class="{{ $isPanel ? 'text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2' : '' }}">Descricao completa</label>
                    <textarea name="full_description" rows="5"
                        class="{{ $isPanel ? 'w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white focus:border-purple-500' : 'form-control' }}">{{ old('full_description', $magazine->full_description) }}</textarea>
                </div>

                {{-- Thumbnail --}}
                <div class="{{ $isPanel ? '' : 'col-md-6 form-group' }}">
                    <label class="{{ $isPanel ? 'text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2' : '' }}">Capa (imagem)</label>
                    <input type="file" name="thumbnail" accept="image/*" class="{{ $isPanel ? 'w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white' : 'form-control' }}">
                    @if($magazine->thumbnail_url)
                        <div class="mt-2"><img src="{{ $magazine->thumbnail_url }}" class="w-24 h-32 object-cover rounded shadow-sm"></div>
                    @endif
                </div>

                {{-- PDF --}}
                <div class="{{ $isPanel ? '' : 'col-md-6 form-group' }}">
                    <label class="{{ $isPanel ? 'text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2' : '' }}">Arquivo PDF {{ !$isEdit ? '*' : '(deixe vazio para manter)' }}</label>
                    <input type="file" name="pdf_file" accept="application/pdf" {{ !$isEdit ? 'required' : '' }} class="{{ $isPanel ? 'w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white' : 'form-control' }}">
                    @if($magazine->pdf_url)
                        <div class="mt-2 text-xs text-slate-500"><i class="fas fa-file-pdf text-red-500 mr-1"></i> Arquivo atual: <a href="{{ $magazine->pdf_url }}" target="_blank" class="text-blue-500 underline">abrir PDF</a> ({{ number_format(($magazine->file_size_kb ?? 0) / 1024, 1) }} MB)</div>
                    @endif
                    <small class="text-slate-500 text-xs">Tamanho maximo: 100 MB</small>
                </div>

                {{-- Status --}}
                <div class="{{ $isPanel ? '' : 'col-md-4 form-group' }}">
                    <label class="{{ $isPanel ? 'text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2' : '' }}">Status *</label>
                    <select name="status" required class="{{ $isPanel ? 'w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white' : 'form-control' }}">
                        <option value="draft" @selected(old('status', $magazine->status) === 'draft')>Rascunho</option>
                        <option value="published" @selected(old('status', $magazine->status) === 'published')>Publicada</option>
                        <option value="archived" @selected(old('status', $magazine->status) === 'archived')>Arquivada</option>
                    </select>
                </div>

                {{-- Visibilidade --}}
                <div class="{{ $isPanel ? '' : 'col-md-4 form-group' }}">
                    <label class="{{ $isPanel ? 'text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2' : '' }}">Visibilidade *</label>
                    <select name="visibility" required class="{{ $isPanel ? 'w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white' : 'form-control' }}">
                        <option value="interest" @selected(old('visibility', $magazine->visibility ?: 'interest') === 'interest')>Somente interessados em "Noticias"</option>
                        <option value="members" @selected(old('visibility', $magazine->visibility) === 'members')>Todos os membros autenticados</option>
                        <option value="public" @selected(old('visibility', $magazine->visibility) === 'public')>Publico (visitantes)</option>
                    </select>
                </div>

                {{-- Paginas --}}
                <div class="{{ $isPanel ? '' : 'col-md-4 form-group' }}">
                    <label class="{{ $isPanel ? 'text-sm font-bold text-slate-700 dark:text-slate-300 block mb-2' : '' }}">Numero de paginas (opcional)</label>
                    <input type="number" name="pages_count" min="1" max="2000" value="{{ old('pages_count', $magazine->pages_count) }}"
                        class="{{ $isPanel ? 'w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm bg-white dark:bg-slate-950 dark:text-white' : 'form-control' }}">
                </div>

                {{-- Flags --}}
                <div class="{{ $isPanel ? 'md:col-span-2' : 'col-md-12 form-group' }}">
                    <div class="flex flex-wrap gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $magazine->is_featured)) class="rounded text-purple-600">
                            Destaque na vitrine
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="hidden" name="allow_download" value="0">
                            <input type="checkbox" name="allow_download" value="1" @checked(old('allow_download', $magazine->exists ? $magazine->allow_download : true)) class="rounded text-purple-600">
                            Permitir download do PDF
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="hidden" name="enable_sound" value="0">
                            <input type="checkbox" name="enable_sound" value="1" @checked(old('enable_sound', $magazine->exists ? $magazine->enable_sound : true)) class="rounded text-purple-600">
                            Som de virar pagina
                        </label>
                    </div>
                </div>
            </div>

            <div class="{{ $isPanel ? 'mt-6 flex justify-end gap-3' : 'mt-3' }}">
                <a href="{{ route($indexRoute) }}" class="{{ $isPanel ? 'px-4 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold' : 'btn btn-default' }}">Cancelar</a>
                <button type="submit" class="{{ $isPanel ? 'px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold shadow' : 'btn btn-primary' }}">
                    <i class="fas fa-save mr-1"></i> Salvar
                </button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
