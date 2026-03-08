@extends('admin.layouts.app')

@section('page_title', $item->exists ? 'Editar Item' : 'Novo Item')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.redemptions.index') }}">Resgates</a></li>
    <li class="breadcrumb-item active">{{ $item->exists ? 'Editar' : 'Novo' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i
                            class="fas fa-gift mr-2"></i>{{ $item->exists ? 'Editar' : 'Cadastrar' }} Item para Resgate</h3>
                </div>

                <form
                    action="{{ $item->exists ? route('admin.redemptions.update', $item) : route('admin.redemptions.store') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    @if($item->exists) @method('PUT') @endif

                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nome do Item <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control"
                                value="{{ old('name', $item->name) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="points_cost">Custo em Pontos <span class="text-danger">*</span></label>
                                    <input type="number" name="points_cost" id="points_cost" class="form-control"
                                        value="{{ old('points_cost', $item->points_cost) }}" required min="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stock">Estoque Disponível <span class="text-danger">*</span></label>
                                    <input type="number" name="stock" id="stock" class="form-control"
                                        value="{{ old('stock', $item->stock) }}" required min="0">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Descrição</label>
                            <textarea id="description" name="description" class="form-control summernote"
                                rows="5">{{ old('description', $item->description) }}</textarea>
                        </div>

                        <div class="form-group mb-2">
                            <label for="image">Imagem do Item</label>
                            <input type="hidden" name="remove_image" value="0">
                            <div class="upload-box" data-max-size="5242880"
                                data-existing-url="{{ $item->image ? asset('storage/' . $item->image) : '' }}"
                                data-remove-input="[name='remove_image']">
                                <input type="file" name="image" id="image" accept="image/*" class="d-none">
                                <div class="upload-preview mb-2"></div>
                                <div class="upload-meta text-muted"></div>
                                <small class="text-muted upload-help"></small>
                                <div class="progress upload-progress progress-sm d-none mt-2">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                    value="1" @checked(old('is_active', $item->is_active ?? true))>
                                <label class="custom-control-label" for="is_active">Item Disponível para Resgate</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <a href="{{ route('admin.redemptions.index') }}" class="btn btn-secondary mr-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i> Salvar Item
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
                height: 200,
                lang: 'pt-BR',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            });
        });
    </script>
@endpush