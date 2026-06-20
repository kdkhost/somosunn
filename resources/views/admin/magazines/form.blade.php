@extends('admin.layouts.app')

@php
    $isEdit = $magazine->exists;
    $action = $isEdit
        ? route('admin.magazines.update', $magazine)
        : route('admin.magazines.store');
@endphp

@section('page_title', $isEdit ? 'Editar Revista' : 'Nova Revista')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.magazines.index') }}">Revistas</a></li>
    <li class="breadcrumb-item active">{{ $isEdit ? 'Editar' : 'Nova' }}</li>
@endsection

@section('content')
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" data-no-ajax="true">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <div class="col-lg-8">
            {{-- Dados básicos --}}
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-book-open mr-2"></i>{{ $isEdit ? 'Editar Revista' : 'Nova Revista' }}</h3>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>Corrija os erros abaixo:</strong>
                            <ul class="mb-0 mt-2">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Título <span class="text-danger">*</span></label>
                        <input type="text" name="title" required class="form-control"
                            value="{{ old('title', $magazine->title) }}"
                            placeholder="Ex.: Revista Manchete - Edição 7">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Categoria</label>
                            <input type="text" name="category" class="form-control"
                                value="{{ old('category', $magazine->category) }}"
                                placeholder="Ex: Moda, Manchetes, Tecnologia">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Edição / Número</label>
                            <input type="text" name="edition" class="form-control"
                                value="{{ old('edition', $magazine->edition) }}"
                                placeholder="Ex.: nº 01 - jan./2026">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Data de publicação</label>
                            <input type="date" name="published_at" class="form-control"
                                value="{{ old('published_at', optional($magazine->published_at)->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Resumo curto</label>
                        <textarea name="short_description" rows="2" maxlength="500" class="form-control"
                            placeholder="Parágrafo exibido na vitrine">{{ old('short_description', $magazine->short_description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Descrição completa</label>
                        <textarea name="full_description" rows="5" class="form-control"
                            placeholder="Descrição detalhada exibida na página da revista">{{ old('full_description', $magazine->full_description) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Arquivos --}}
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-cloud-upload-alt mr-2"></i>Arquivos</h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Capa da revista <small class="text-muted">(proporção 3:4 recomendada)</small></label>
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
                                theme="admin-lte"
                            />
                        </div>
                        <div class="form-group col-md-6">
                            <label>Arquivo PDF {{ $isEdit ? '' : '*' }} <small class="text-muted">(até 100 MB)</small></label>
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
                                theme="admin-lte"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Publicação --}}
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-rocket mr-2"></i>Publicação</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="draft" @selected(old('status', $magazine->status) === 'draft')>Rascunho</option>
                            <option value="published" @selected(old('status', $magazine->status ?: 'draft') === 'published')>Publicada</option>
                            <option value="archived" @selected(old('status', $magazine->status) === 'archived')>Arquivada</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Visibilidade <span class="text-danger">*</span></label>
                        <select name="visibility" class="form-control" required>
                            <option value="interest" @selected(old('visibility', $magazine->visibility ?: 'interest') === 'interest')>Somente interessados em "Notícias"</option>
                            <option value="members" @selected(old('visibility', $magazine->visibility) === 'members')>Todos os membros autenticados</option>
                            <option value="public" @selected(old('visibility', $magazine->visibility) === 'public')>Público (visitantes)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Número de páginas</label>
                        <input type="number" name="pages_count" min="1" max="2000" class="form-control"
                            value="{{ old('pages_count', $magazine->pages_count) }}"
                            placeholder="Opcional">
                    </div>
                </div>
            </div>

            {{-- Opções --}}
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-cog mr-2"></i>Opções</h3>
                </div>
                <div class="card-body">
                    <div class="custom-control custom-switch mb-3">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" class="custom-control-input" id="sw-featured" name="is_featured" value="1" @checked(old('is_featured', $magazine->is_featured))>
                        <label class="custom-control-label" for="sw-featured"><i class="fas fa-star text-warning mr-1"></i>Destaque na vitrine</label>
                    </div>
                    <div class="custom-control custom-switch mb-3">
                        <input type="hidden" name="allow_download" value="0">
                        <input type="checkbox" class="custom-control-input" id="sw-download" name="allow_download" value="1" @checked(old('allow_download', $magazine->exists ? $magazine->allow_download : true))>
                        <label class="custom-control-label" for="sw-download"><i class="fas fa-download text-info mr-1"></i>Permitir baixar o PDF</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="hidden" name="enable_sound" value="0">
                        <input type="checkbox" class="custom-control-input" id="sw-sound" name="enable_sound" value="1" @checked(old('enable_sound', $magazine->exists ? $magazine->enable_sound : true))>
                        <label class="custom-control-label" for="sw-sound"><i class="fas fa-volume-high text-success mr-1"></i>Som de virar página</label>
                    </div>
                </div>
            </div>

            {{-- Ações --}}
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-save mr-1"></i> Salvar Revista
                    </button>
                    <a href="{{ route('admin.magazines.index') }}" class="btn btn-default btn-block mt-2">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
