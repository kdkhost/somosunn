@extends('admin.layouts.app')

@section('page_title', $vacancy->exists ? 'Editar Vaga' : 'Nova Vaga')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.jobs.index') }}">Vagas</a></li>
    <li class="breadcrumb-item active">{{ $vacancy->exists ? 'Editar' : 'Novo' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-pencil-alt mr-2"></i>{{ $vacancy->exists ? 'Editar' : 'Nova' }}
                        Vaga</h3>
                </div>

                <form action="{{ $vacancy->exists ? route('admin.jobs.update', $vacancy) : route('admin.jobs.store') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    @if($vacancy->exists) @method('PUT') @endif

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Título da Vaga <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" class="form-control"
                                        value="{{ old('title', $vacancy->title) }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="type">Tipo de Contrato <span class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-control custom-select">
                                        <option value="CLT" @selected(old('type', $vacancy->type) == 'CLT')>CLT</option>
                                        <option value="PJ" @selected(old('type', $vacancy->type) == 'PJ')>PJ</option>
                                        <option value="Freelance" @selected(old('type', $vacancy->type) == 'Freelance')>
                                            Freelance</option>
                                        <option value="Estágio" @selected(old('type', $vacancy->type) == 'Estágio')>Estágio
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="level">Nível <span class="text-danger">*</span></label>
                                    <select name="level" id="level" class="form-control custom-select">
                                        <option value="Junior" @selected(old('level', $vacancy->level) == 'Junior')>Junior
                                        </option>
                                        <option value="Pleno" @selected(old('level', $vacancy->level) == 'Pleno')>Pleno
                                        </option>
                                        <option value="Sênior" @selected(old('level', $vacancy->level) == 'Sênior')>Sênior
                                        </option>
                                        <option value="Especialista" @selected(old('level', $vacancy->level) == 'Especialista')>Especialista</option>
                                        <option value="Estágio" @selected(old('level', $vacancy->level) == 'Estágio')>Estágio
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_name">Nome da Empresa</label>
                                    <input type="text" name="company_name" id="company_name" class="form-control"
                                        value="{{ old('company_name', $vacancy->company_name) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location">Localização / Remoto</label>
                                    <input type="text" name="location" id="location" class="form-control"
                                        value="{{ old('location', $vacancy->location) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="salary_range">Faixa Salarial (Opcional)</label>
                                    <input type="text" name="salary_range" id="salary_range" class="form-control"
                                        value="{{ old('salary_range', $vacancy->salary_range) }}"
                                        placeholder="Ex: R$ 5.000 - R$ 7.000">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="expires_at">Expira em (Opcional)</label>
                                    <input type="date" name="expires_at" id="expires_at" class="form-control"
                                        value="{{ old('expires_at', $vacancy->expires_at ? $vacancy->expires_at->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="visibility">Visibilidade <span class="text-danger">*</span></label>
                                    <select name="visibility" id="visibility" class="form-control custom-select">
                                        <option value="internal" @selected(old('visibility', $vacancy->visibility) == 'internal')>Comunidade</option>
                                        <option value="external" @selected(old('visibility', $vacancy->visibility) == 'external')>Público</option>
                                        <option value="both" @selected(old('visibility', $vacancy->visibility) == 'both' || !$vacancy->exists)>Ambos</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="image">Imagem da Vaga / Logo da Empresa</label>
                                    @if($vacancy->image)
                                        <div class="mb-2">
                                            <img src="{{ asset($vacancy->image) }}" class="img-thumbnail"
                                                style="max-height: 100px;">
                                        </div>
                                    @endif
                                    <div class="custom-file">
                                        <input type="file" name="image" class="custom-file-input" id="image"
                                            accept="image/*">
                                        <label class="custom-file-label" for="image" data-browse="Procurar">Escolher
                                            arquivo...</label>
                                    </div>
                                    <small class="text-muted">Recomendado: 800x600px</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Opções Adicionais</label>
                                    <div class="custom-control custom-switch pt-2">
                                        <input type="checkbox" class="custom-control-input" id="is_demo" name="is_demo"
                                            value="1" @checked(old('is_demo', $vacancy->is_demo))>
                                        <label class="custom-control-label" for="is_demo">Esta é uma vaga fictícia
                                            (Demonstração)?</label>
                                    </div>
                                    <small class="text-muted">Se ativado, aparecerá uma etiqueta de "Demonstração" na
                                        listagem pública.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="short_description">Resumo (Aparece na listagem)</label>
                            <textarea id="short_description" name="short_description" class="form-control"
                                rows="3">{{ old('short_description', $vacancy->short_description) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="description">Descrição Detalhada <span class="text-danger">*</span></label>
                            <textarea id="description" name="description" class="form-control summernote"
                                rows="10">{{ old('description', $vacancy->description) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="requirements">Requisitos</label>
                            <textarea id="requirements" name="requirements" class="form-control summernote"
                                rows="5">{{ old('requirements', $vacancy->requirements) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="benefits">Benefícios</label>
                            <textarea id="benefits" name="benefits" class="form-control summernote"
                                rows="5">{{ old('benefits', $vacancy->benefits) }}</textarea>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                    value="1" @checked(old('is_active', $vacancy->is_active ?? true))>
                                <label class="custom-control-label" for="is_active">Vaga Ativa</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <a href="{{ route('admin.jobs.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success ml-2">
                            <i class="fas fa-save mr-1"></i> {{ $vacancy->exists ? 'Atualizar' : 'Salvar' }} Vaga
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- Summernote -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.css">
@endpush

@push('scripts')
    <!-- Summernote -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-pt-BR.min.js"></script>
    <script>
        $(function () {
            $('.summernote').summernote({
                height: 300,
                lang: 'pt-BR',
                placeholder: 'Escreva detalhes aqui...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
@endpush