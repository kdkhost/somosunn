@extends('admin.layouts.app')

@section('page_title', $course->exists ? 'Editar Curso' : 'Novo Curso')
@section('breadcrumb_items')
    <li class="breadcrumb-item"><a href="{{ route('admin.courses.index') }}">Cursos</a></li>
    <li class="breadcrumb-item active">{{ $course->exists ? 'Editar' : 'Novo' }}</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <style>
        .nav-tabs .nav-link {
            font-weight: 600;
            color: #495057;
        }

        .nav-tabs .nav-link.active {
            color: #007bff;
        }

        .lesson-item {
            border: 1px solid #dee2e6;
            padding: 12px 15px;
            background: #fff;
            margin-bottom: 8px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s;
        }

        .lesson-item:hover {
            background: #f8f9fa;
            transform: translateX(2px);
            border-color: #adb5bd;
        }

        .dropzone {
            border: 2px dashed #007bff;
            border-radius: 6px;
            background: #f8f9fa;
            min-height: 150px;
            padding: 20px;
        }

        .attachment-list {
            list-style: none;
            padding: 0;
            margin-top: 15px;
        }

        .attachment-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #eee;
            background: #fff;
            border-radius: 4px;
            margin-bottom: 5px;
            border: 1px solid #eee;
        }

        .attachment-icon {
            margin-right: 12px;
            font-size: 1.4em;
            color: #6c757d;
        }

        .swal2-container {
            z-index: 2000 !important;
        }

        /* Better Form Groups */
        .form-group label {
            font-weight: 600;
            color: #343a40;
        }

        .custom-file-label::after {
            content: "Procurar";
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="courseTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab"
                                aria-controls="info" aria-selected="true">
                                <i class="fas fa-info-circle mr-1"></i> Informações Básicas
                            </a>
                        </li>
                        <li class="nav-item">
                            @if($course->exists)
                                <a class="nav-link" id="lessons-tab" data-toggle="tab" href="#lessons" role="tab"
                                    aria-controls="lessons" aria-selected="false">
                                    <i class="fas fa-layer-group mr-1"></i> Conteúdo / Aulas
                                </a>
                            @else
                                <a class="nav-link disabled" href="#" title="Salve o curso primeiro">
                                    <i class="fas fa-layer-group mr-1"></i> Conteúdo / Aulas <i
                                        class="fas fa-lock ml-1 text-muted"></i>
                                </a>
                            @endif
                        </li>
                        <li class="nav-item">
                            @if($course->exists)
                                <a class="nav-link" id="cert-tab" data-toggle="tab" href="#certificate" role="tab"
                                    aria-controls="certificate" aria-selected="false">
                                    <i class="fas fa-certificate mr-1"></i> Certificado
                                </a>
                            @else
                                <a class="nav-link disabled" href="#" title="Salve o curso primeiro">
                                    <i class="fas fa-certificate mr-1"></i> Certificado <i
                                        class="fas fa-lock ml-1 text-muted"></i>
                                </a>
                            @endif
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="courseTabsContent">

                        <!-- TAB 1: INFORMAÇÕES -->
                        <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                            <form method="POST"
                                action="{{ $course->exists ? route('admin.courses.update', $course) : route('admin.courses.store') }}"
                                enctype="multipart/form-data">
                                @csrf
                                @if($course->exists) @method('PUT') @endif

                                <div class="row">
                                    <!-- Coluna Principal -->
                                    <div class="col-lg-8">
                                        <div class="form-group mb-4">
                                            <label>Título do Curso</label>
                                            <input name="title" class="form-control form-control-lg"
                                                value="{{ old('title', $course->title) }}" required
                                                placeholder="Ex: Curso Completo de Laravel"
                                                style="font-weight: 600; font-size: 1.25rem;">
                                        </div>

                                        <div class="form-group mb-4">
                                            <label>Descrição Curta (Resumo)</label>
                                            <textarea name="short_description" class="form-control" rows="3"
                                                placeholder="Breve resumo para exibição nos cards..."
                                                maxlength="500">{{ old('short_description', $course->short_description) }}</textarea>
                                            <small class="text-muted">Máximo 500 caracteres.</small>
                                        </div>

                                        <div class="form-group mb-4">
                                            <label>Descrição Completa</label>
                                            <textarea name="full_description" id="fullDescription"
                                                class="form-control summernote">{{ old('full_description', $course->full_description) }}</textarea>
                                        </div>

                                        <div class="card bg-light mt-4 border-0">
                                            <div class="card-body">
                                                <label class="mb-2"><i class="fas fa-image mr-1"></i> Imagem de Capa</label>
                                                <div class="row align-items-center">
                                                    <div class="col-md-4">
                                                        @if($course->thumbnail)
                                                            <img src="{{ asset($course->thumbnail) }}"
                                                                class="img-fluid rounded shadow-sm">
                                                        @else
                                                            <div class="bg-white rounded mb-2 d-flex align-items-center justify-content-center border"
                                                                style="height: 120px;">
                                                                <i class="fas fa-image fa-3x text-muted"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="custom-file mb-2">
                                                            <input type="file" name="thumbnail" class="custom-file-input"
                                                                id="courseThumbnail"
                                                                accept="image/png, image/jpeg, image/jpg">
                                                            <label class="custom-file-label" for="courseThumbnail"
                                                                data-browse="Buscar">Escolher nova imagem</label>
                                                        </div>
                                                        <small class="text-muted d-block">Recomendado: 1280x720px
                                                            (JPG/PNG)</small>
                                                        <div class="progress mt-2" id="thumbnailProgressWrapper"
                                                            style="display:none; height: 6px;">
                                                            <div class="progress-bar bg-success" role="progressbar"
                                                                id="thumbnailProgressBar" style="width: 0%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Coluna Lateral -->
                                    <div class="col-lg-4">
                                        <div class="card shadow-sm border-0 bg-light">
                                            <div class="card-body">
                                                <h5 class="text-muted mb-3"><i class="fas fa-cog mr-2"></i>Publicação</h5>

                                                <div class="form-group mb-3">
                                                    <label>Status</label>
                                                    <select name="status" class="form-control custom-select">
                                                        <option value="draft" {{ $course->status == 'draft' ? 'selected' : '' }}>Rascunho</option>
                                                        <option value="published" {{ $course->status == 'published' ? 'selected' : '' }}>Publicado</option>
                                                        <option value="archived" {{ $course->status == 'archived' ? 'selected' : '' }}>Arquivado</option>
                                                        <option value="paused" {{ $course->status == 'paused' ? 'selected' : '' }}>Vendas Pausadas</option>
                                                    </select>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label>Preço (R$)</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">R$</span>
                                                        </div>
                                                        <input name="price"
                                                            class="form-control mask-money form-control-lg font-weight-bold"
                                                            value="{{ old('price', $course->price) }}" placeholder="0,00">
                                                    </div>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label>Autor / Instrutor</label>
                                                    <input name="author_name" class="form-control"
                                                        value="{{ old('author_name', $course->author_name ?? Auth::user()->name) }}">
                                                </div>

                                                <div class="form-group mb-4">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input"
                                                            name="is_featured" id="is_featured" value="1" {{ $course->is_featured ? 'checked' : '' }}>
                                                        <label class="custom-control-label font-weight-bold"
                                                            for="is_featured">Destaque na Home</label>
                                                    </div>
                                                </div>

                                                <hr>

                                                <button class="btn btn-primary btn-block btn-lg shadow-sm">
                                                    <i class="fas fa-save mr-2"></i> Salvar Informações
                                                </button>
                                            </div>
                                        </div>

                                        @if($course->exists && $course->slug)
                                            <div class="card shadow-sm border-0 mt-3">
                                                <div class="card-body">
                                                    <label class="mb-2">Link do Curso</label>
                                                    <div class="input-group mb-2">
                                                        <input type="text" class="form-control form-control-sm" id="courseLink"
                                                            value="{{ route('courses.show', $course->slug) }}" readonly>
                                                        <div class="input-group-append">
                                                            <button class="btn btn-outline-secondary btn-sm" type="button"
                                                                onclick="copyLink()">
                                                                <i class="fas fa-copy"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <a href="{{ route('courses.show', $course->slug) }}" target="_blank"
                                                        class="btn btn-sm btn-outline-info btn-block">
                                                        Visualizar Página <i class="fas fa-external-link-alt ml-1"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endif

                                        @if(!$course->exists)
                                            <div class="alert alert-info mt-3 text-center">
                                                <i class="fas fa-info-circle"></i> Salve para liberar a aba de Aulas.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- TAB 2: AULAS -->
                        @if($course->exists)
                            <div class="tab-pane fade" id="lessons" role="tabpanel" aria-labelledby="lessons-tab">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4 class="mb-0 text-dark">Grade Curricular</h4>
                                    <button class="btn btn-success shadow-sm" onclick="openLessonModal()">
                                        <i class="fas fa-plus mr-1"></i> Nova Aula
                                    </button>
                                </div>

                                <div id="lessons-list">
                                    @forelse($course->lessons as $lesson)
                                        <div class="lesson-item shadow-sm" data-id="{{ $lesson->id }}">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-grip-vertical text-muted mr-3"
                                                    style="cursor:move; opacity: 0.5;"></i>
                                                <div>
                                                    <div class="font-weight-bold" style="font-size: 1.05rem;">
                                                        <span class="text-muted mr-1">#{{ $lesson->order }}</span>
                                                        {{ $lesson->title }}
                                                    </div>
                                                    <div class="mt-1">
                                                        <span
                                                            class="badge badge-light border">{{ gmdate("H:i:s", $lesson->duration) }}</span>
                                                        @if($lesson->is_free_preview) <span class="badge badge-info">Preview
                                                        Grátis</span> @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-outline-primary btn-edit-lesson mr-1"
                                                    data-id="{{ $lesson->id }}" title="Editar"><i class="fas fa-edit"></i></button>
                                                <form action="{{ route('courses.lessons.destroy', [$course, $lesson]) }}"
                                                    method="POST" class="d-inline ajax-delete">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" title="Excluir"><i
                                                            class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-5 border rounded bg-light">
                                            <i class="fas fa-film fa-3x mb-3 text-secondary"></i>
                                            <h5>Nenhuma aula cadastrada ainda.</h5>
                                            <p class="mb-0">Clique no botão "Nova Aula" para começar.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endif

                        <!-- TAB 3: CERTIFICADO -->
                        @if($course->exists)
                            <div class="tab-pane fade" id="certificate" role="tabpanel" aria-labelledby="cert-tab">
                                <form id="certForm" method="POST" action="{{ route('admin.courses.update', $course) }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="col-xl-9 col-lg-8">
                                            <!-- CANVAS AREA -->
                                            <div class="card shadow-sm border-0">
                                                <div class="card-header bg-secondary text-white small py-1">
                                                    Editor Visual (A4 Paisagem)
                                                </div>
                                                <div class="card-body bg-dark d-flex justify-content-center align-items-center p-4"
                                                    style="min-height: 600px; overflow: auto;">

                                                    <div id="cert-canvas"
                                                        style="position: relative; width: 842px; height: 595px; background-color: white; box-shadow: 0 0 30px rgba(0,0,0,0.5); flex-shrink: 0; overflow: hidden;">
                                                        @if($course->certificate_bg)
                                                            <img src="{{ asset($course->certificate_bg) }}" id="cert-bg-img"
                                                                style="width: 100%; height: 100%; object-fit: cover; position: absolute; z-index: 1;">
                                                        @else
                                                            <div id="cert-bg-placeholder"
                                                                style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #ccc; z-index: 1; position: absolute; background: #eee;">
                                                                <div class="text-center">
                                                                    <i class="fas fa-image fa-3x mb-2"></i>
                                                                    <h5>Sem imagem de fundo</h5>
                                                                    <p class="small">Faça upload no painel lateral</p>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <!-- Draggable Container z-index 10 -->
                                                        <div id="cert-elements-layer"
                                                            style="position: absolute; top:0; left:0; width: 100%; height: 100%; z-index: 10;">
                                                            <!-- Elements will be injected here via JS -->
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-lg-4">
                                            <div class="card shadow-sm border-0">
                                                <div class="card-header bg-dark text-white font-weight-bold">Configurações</div>
                                                <div class="card-body">

                                                    <div class="custom-control custom-switch mb-4">
                                                        <input type="checkbox" name="is_certificate_enabled" value="1"
                                                            class="custom-control-input" id="is_certificate_enabled_tab" {{ $course->is_certificate_enabled ? 'checked' : '' }}>
                                                        <label class="custom-control-label font-weight-bold"
                                                            for="is_certificate_enabled_tab">Habilitar Certificado</label>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="small text-muted text-uppercase font-weight-bold">Fundo do
                                                            Certificado</label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input" name="certificate_bg"
                                                                accept="image/*" onchange="previewCertBg(this)">
                                                            <label class="custom-file-label">Escolher arquivo</label>
                                                        </div>
                                                        <small class="text-muted">Recomendado: 1920x1080px (PNG/JPG)</small>
                                                    </div>

                                                    <hr>

                                                    <h6 class="small text-muted text-uppercase font-weight-bold mb-3">Elementos
                                                        Visíveis</h6>
                                                    <div class="list-group mb-3" id="cert-available-tags">
                                                        <!-- Checkboxes to toggle visibility of elements -->
                                                        <div class="list-group-item p-2 border-0">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input cert-toggle"
                                                                    id="toggle-student" data-tag="student_name" checked>
                                                                <label class="custom-control-label" for="toggle-student">Nome do
                                                                    Aluno</label>
                                                            </div>
                                                        </div>
                                                        <div class="list-group-item p-2 border-0">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input cert-toggle"
                                                                    id="toggle-course" data-tag="course_name" checked>
                                                                <label class="custom-control-label" for="toggle-course">Nome do
                                                                    Curso</label>
                                                            </div>
                                                        </div>
                                                        <div class="list-group-item p-2 border-0">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input cert-toggle"
                                                                    id="toggle-date" data-tag="completion_date" checked>
                                                                <label class="custom-control-label" for="toggle-date">Data de
                                                                    Conclusão</label>
                                                            </div>
                                                        </div>
                                                        <div class="list-group-item p-2 border-0">
                                                            <div class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input cert-toggle"
                                                                    id="toggle-code" data-tag="certificate_code" checked>
                                                                <label class="custom-control-label" for="toggle-code">Código de
                                                                    Validação</label>
                                                            </div>
                                                        </div>
                                                        <div class="list-group-item p-2 border-0 bg-warning rounded mt-2">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div>
                                                                    <i class="fas fa-lock mr-2 text-dark"></i>
                                                                    <span class="font-weight-bold text-dark">Logo da
                                                                        Plataforma</span>
                                                                </div>
                                                                <small class="badge badge-dark">OBRIGATÓRIO</small>
                                                            </div>
                                                            <small class="text-dark d-block mt-1"><i
                                                                    class="fas fa-info-circle"></i> A logo não pode ser removida
                                                                do certificado</small>

                                                            <div class="mt-3">
                                                                <label class="small text-dark mb-1 font-weight-bold">Tamanho da
                                                                    Logo</label>
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <label class="small text-muted mb-0">Largura
                                                                            (px)</label>
                                                                        <input type="number" id="logo-width"
                                                                            class="form-control form-control-sm" min="50"
                                                                            max="400" value="120" placeholder="120">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="small text-muted mb-0">Altura (px)</label>
                                                                        <input type="number" id="logo-height"
                                                                            class="form-control form-control-sm" min="30"
                                                                            max="200" value="60" placeholder="60">
                                                                    </div>
                                                                </div>
                                                                <small class="text-muted">Min: 50x30px | Máx: 400x200px</small>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div id="cert-style-controls" style="display:none;"
                                                        class="bg-light p-3 rounded border">
                                                        <h6 class="font-weight-bold mb-3" style="font-size: 0.9rem;">Editar:
                                                            <span id="selected-elem-name" class="text-primary"></span>
                                                        </h6>

                                                        <div class="form-group mb-2">
                                                            <label class="small text-muted mb-0">Fonte</label>
                                                            <select id="style-font-family" class="form-control form-control-sm">
                                                                <option value="Arial, sans-serif">Arial</option>
                                                                <option value="'Times New Roman', serif">Times New Roman
                                                                </option>
                                                                <option value="'Courier New', monospace">Courier New</option>
                                                                <option value="Georgia, serif">Georgia</option>
                                                            </select>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-6">
                                                                <div class="form-group mb-2">
                                                                    <label class="small text-muted mb-0">Tamanho</label>
                                                                    <input type="number" id="style-font-size"
                                                                        class="form-control form-control-sm" value="20">
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="form-group mb-2">
                                                                    <label class="small text-muted mb-0">Cor</label>
                                                                    <input type="color" id="style-color"
                                                                        class="form-control form-control-sm h-auto"
                                                                        value="#000000" style="padding: 2px;">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group mb-0">
                                                            <label class="small text-muted mb-0">Peso</label>
                                                            <select id="style-font-weight" class="form-control form-control-sm">
                                                                <option value="normal">Normal</option>
                                                                <option value="bold">Negrito</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" name="certificate_settings"
                                                        id="certificate_settings_input">
                                                    <button type="submit" class="btn btn-primary btn-block mt-4"
                                                        id="btn-save-cert">
                                                        <i class="fas fa-save mr-1"></i> Salvar Certificado
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Aula (KEEP EXISTING MODAL EXACTLY AS IS TO AVOID JS ISSUES) -->
    <div class="modal fade" id="lessonModal" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lessonModalTitle">Gerenciar Aula</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="lessonForm">
                        @csrf
                        <input type="hidden" name="_method" id="lessonMethod" value="POST">
                        <input type="hidden" id="lessonId" value="">

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Título da Aula</label>
                                    <input name="title" id="lessonTitle" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Ordem</label>
                                    <input type="number" name="order" id="lessonOrder" class="form-control" value="1">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Duração (seg)</label>
                                    <input type="number" name="duration" id="lessonDuration" class="form-control"
                                        placeholder="0">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Fonte do Vídeo</label>
                            <ul class="nav nav-pills mb-2" id="video-source-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="pills-url-tab" data-toggle="pill" href="#pills-url"
                                        role="tab">Link Externo (YouTube/Vimeo)</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="pills-file-tab" data-toggle="pill" href="#pills-file"
                                        role="tab">Upload de Arquivo (MP4)</a>
                                </li>
                            </ul>
                            <div class="tab-content" id="video-source-content">
                                <div class="tab-pane fade show active" id="pills-url" role="tabpanel">
                                    <input name="video_url" id="lessonVideo" class="form-control" placeholder="https://...">
                                </div>
                                <div class="tab-pane fade" id="pills-file" role="tabpanel">
                                    <div class="custom-file">
                                        <input type="file" name="video_file" class="custom-file-input" id="lessonVideoFile"
                                            accept="video/mp4,video/x-m4v,video/*">
                                        <label class="custom-file-label" for="lessonVideoFile"
                                            id="lessonVideoFileLabel">Escolher vídeo...</label>
                                    </div>
                                    <small class="text-muted">A duração será detectada automaticamente ao selecionar o
                                        arquivo.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Conteúdo (Texto/HTML)</label>
                            <textarea name="content" id="lessonContent" class="form-control summernote" rows="3"></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_free_preview" id="lessonPreview" class="form-check-input"
                                value="1">
                            <label class="form-check-label" for="lessonPreview">Aula Gratuita (Preview)</label>
                        </div>

                        <div class="progress mt-3" id="uploadProgressWrapper" style="display:none;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                role="progressbar" id="uploadProgressBar" style="width: 0%">0%</div>
                        </div>

                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-primary" id="btnSaveLesson" data-toggle="tooltip"
                                title="Salvar e fechar modal">Salvar Dados da Aula</button>
                        </div>
                    </form>

                    <hr>

                    <!-- Attachment Section (Only for existing lessons) -->
                    <div id="attachmentsSection" style="display:none;">
                        <h5><i class="fas fa-paperclip"></i> Materiais de Apoio</h5>
                        <p class="text-muted small">Arquivos para download (PDF, Doc, Zip, etc)</p>

                        <div class="dropzone" id="filesDropzone">
                            <div class="dz-message" data-dz-message><span>Arraste arquivos aqui ou clique para enviar</span>
                            </div>
                        </div>

                        <ul class="attachment-list" id="attachmentList">
                            <!-- Loaded via JS -->
                        </ul>
                    </div>
                    <div id="attachmentsWarning" class="alert alert-info mt-3">
                        Salve a aula primeiro para poder anexar materiais.
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script>
        Dropzone.autoDiscover = false;

        // Helper functions
        function openLessonModal(lesson = null) {
            $('#lessonForm')[0].reset();
            $('#lessonId').val('');
            $('#lessonMethod').val('POST');
            $('#lessonModalTitle').text('Nova Aula');
            $('#lessonVideoFileLabel').text('Escolher vídeo...');
            $('#attachmentsSection').hide();
            $('#attachmentsWarning').show();
            $('#attachmentList').empty();

            // Reset Video Inputs
            $('#lessonVideo').prop('disabled', false);
            $('#lessonVideoFile').prop('disabled', false);
            $('#video-source-tab a').removeClass('disabled');

            // Reset Summernote
            $('#lessonContent').summernote('reset');

            if (lesson) {
                // Edit Mode
                $('#lessonModalTitle').text('Editar Aula');
                $('#lessonId').val(lesson.id);
                $('#lessonMethod').val('PUT');
                $('#lessonTitle').val(lesson.title);
                $('#lessonOrder').val(lesson.order);
                // Video Logic
                const videoUrl = lesson.video_url || '';
                if (videoUrl.includes('storage/') || videoUrl.includes('course-videos/')) {
                    // Local File
                    $('#lessonVideo').val(''); // Clear URL field
                    const fileName = videoUrl.split('/').pop();
                    $('#lessonVideoFileLabel').text('Arquivo atual: ' + fileName);

                    // Switch tab to file
                    $('#pills-file-tab').tab('show');
                } else {
                    // External URL
                    $('#lessonVideo').val(videoUrl);
                    $('#lessonVideoFileLabel').text('Escolher vídeo...');
                    $('#pills-url-tab').tab('show');
                }

                // Lock Video Editing
                $('#lessonVideo').prop('disabled', true);
                $('#lessonVideoFile').prop('disabled', true);
                $('#video-source-tab a').addClass('disabled'); // Disable tab switching

                // Set Content
                $('#lessonContent').summernote('code', lesson.content || '');

                $('#lessonDuration').val(lesson.duration);
                if (lesson.is_free_preview) $('#lessonPreview').prop('checked', true);

                // Fetch details (attachments)
                fetchLessonDetails(lesson.id);

                $('#attachmentsSection').show();
                $('#attachmentsWarning').hide();

                // Re-init dropzone url
                updateDropzoneUrl(lesson.id);
            }

            $('#lessonModal').modal('show');

            // Ensure Summernote is visible/init
            setTimeout(function () {
                $('#lessonContent').summernote({
                    height: 150,
                    toolbar: [
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        ['font', ['strikethrough', 'superscript', 'subscript']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['fullscreen', 'codeview']]
                    ]
                });
            }, 200);
        }

        function updateDropzoneUrl(lessonId) {
            if (myDropzone) {
                myDropzone.options.url = '/courses/{{ $course->id }}/lessons/' + lessonId + '/attachments';
            }
        }

        function fetchLessonDetails(lessonId) {
            $.get('/courses/{{ $course->id }}/lessons/' + lessonId + '/details', function (data) {
                renderAttachments(data.attachments, lessonId);
            });
        }

        function renderAttachments(attachments, lessonId) {
            const list = $('#attachmentList');
            list.empty();
            attachments.forEach(att => {
                const size = (att.file_size / 1024 / 1024).toFixed(2) + ' MB';
                const item = `
                            <li class="attachment-item" id="att-${att.id}">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file attachment-icon"></i>
                                    <div>
                                        <div class="font-weight-bold" id="att-name-${att.id}">${att.file_name}</div>
                                        <small class="text-muted">${size}</small>
                                    </div>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="renameAttachment(${lessonId}, ${att.id}, '${att.file_name}')" data-toggle="tooltip" title="Renomear"><i class="fas fa-pen"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteAttachment(${lessonId}, ${att.id})" data-toggle="tooltip" title="Excluir"><i class="fas fa-trash"></i></button>
                                </div>
                            </li>
                        `;
                list.append(item);
            });
        }

        // Attachment Actions
        window.renameAttachment = function (lessonId, attId, currentName) {
            Swal.fire({
                title: 'Renomear',
                input: 'text',
                inputValue: currentName,
                showCancelButton: true,
                confirmButtonText: 'Salvar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/courses/{{ $course->id }}/lessons/' + lessonId + '/attachments/' + attId,
                        type: 'PUT',
                        data: { name: result.value, _token: '{{ csrf_token() }}' },
                        success: function () { fetchLessonDetails(lessonId); toastr.success('Renomeado'); }
                    });
                }
            });
        };

        window.deleteAttachment = function (lessonId, attId) {
            Swal.fire({
                title: 'Excluir arquivo?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/courses/{{ $course->id }}/lessons/' + lessonId + '/attachments/' + attId,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function () { fetchLessonDetails(lessonId); toastr.success('Removido'); }
                    });
                }
            });
        };

        // Initialize Dropzone
        let myDropzone;
        $(document).ready(function () {
            if ($('#filesDropzone').length) {
                myDropzone = new Dropzone("#filesDropzone", {
                    url: "/dummy", // Set dynamically
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    maxFilesize: 500, // 500 MB
                    timeout: 0, // No timeout
                    acceptedFiles: null,
                    autoProcessQueue: false, // Wait for name
                    addRemoveLinks: true,
                    dictRemoveFile: "Cancelar",
                    dictDefaultMessage: "Arraste arquivos aqui ou clique para enviar",
                    dictFallbackMessage: "Seu navegador não suporta upload arrastar e soltar.",
                    dictFileTooBig: "Arquivo muito grande (@{{filesize}}MiB). Máximo: @{{maxFilesize}}MiB.",
                    dictInvalidFileType: "Você não pode enviar arquivos deste tipo.",
                    dictResponseError: "Servidor respondeu com código @{{statusCode}}.",
                    dictCancelUpload: "Cancelar upload",
                    dictUploadCanceled: "Upload cancelado.",

                    init: function () {
                        // Fix SweetAlert2 focus inside Bootstrap Modal
                        // This kills the 'enforceFocus' feature of Bootstrap which fights with SweetAlert2
                        $.fn.modal.Constructor.prototype._enforceFocus = function () { };

                        this.on("addedfile", function (file) {
                            // Prompt for name immediately
                            Swal.fire({
                                title: 'Nome do arquivo',
                                input: 'text',
                                inputValue: file.name,
                                showCancelButton: true,
                                confirmButtonText: 'Enviar',
                                cancelButtonText: 'Cancelar Upload',
                                allowOutsideClick: false,
                                returnFocus: false, // Prevent Swal from trying to return focus to dropzone immediately
                                didOpen: () => {
                                    const input = Swal.getInput();
                                    input.focus();
                                    // Double check focus after a minimal delay
                                    setTimeout(() => input.focus(), 50);
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    file.customName = result.value;
                                    myDropzone.processQueue();
                                } else {
                                    myDropzone.removeFile(file);
                                }
                            });
                        });

                        this.on("sending", function (file, xhr, formData) {
                            if (file.customName) {
                                formData.append("name", file.customName);
                            }
                        });

                        this.on("success", function (file, response) {
                            if (response.success) {
                                toastr.success('Arquivo enviado!');
                                const lessonId = $('#lessonId').val();
                                fetchLessonDetails(lessonId);
                                this.removeFile(file);
                            }
                        });

                        this.on("error", function (file, message) {
                            // If manually removed (canceled), don't show error
                            if (file.accepted === false) return;

                            // Handle Dropzone error message objects or strings
                            let msg = message;
                            if (typeof message === 'object' && message.error) msg = message.error;

                            toastr.error(msg || 'Erro no upload');
                            this.removeFile(file);
                        });
                    }
                });
            }

            // Edit Lesson Click
            $('.btn-edit-lesson').on('click', function () {
                const id = $(this).data('id');
                // Fetch basic data from data attributes or DOM? Better fetch clean JSON.
                // Simple approach: get row data. But for edit stability, fetching details is better.
                $.get('/courses/{{ $course->id }}/lessons/' + id + '/details', function (data) {
                    openLessonModal(data);
                });
            });

            // Detect Duration for Local File
            $('#lessonVideoFile').change(function (e) {
                var file = e.target.files[0];
                if (file) {
                    $('#lessonVideoFileLabel').text(file.name);
                    var video = document.createElement('video');
                    video.preload = 'metadata';
                    video.onloadedmetadata = function () {
                        window.URL.revokeObjectURL(video.src);
                        var duration = Math.floor(video.duration);
                        $('#lessonDuration').val(duration);
                        toastr.info('Duração detectada: ' + duration + 's');
                    }
                    video.src = URL.createObjectURL(file);
                }
            });

            // Save Lesson Button Click (Force Submit)
            $('#btnSaveLesson').on('click', function (e) {
                e.preventDefault();

                // Visual Feedback
                var $btn = $(this);
                var originalText = $btn.text();
                $btn.prop('disabled', true).text('Salvando...');

                const id = $('#lessonId').val();
                let url = '/courses/{{ $course->id }}/lessons';
                if (id) url += '/' + id;

                // Sync Summernote content manually to ensure it's captured
                var content = $('#lessonContent').summernote('code');

                var formData = new FormData($('#lessonForm')[0]);
                formData.set('content', content); // Force override with summernote data

                // Reset and Show Progress
                $('#uploadProgressWrapper').show();
                var $progressBar = $('#uploadProgressBar');
                $progressBar.css('width', '0%').text('0%');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json', // Expect JSON
                    xhr: function () {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function (evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                                $progressBar.css('width', percentComplete + '%');
                                $progressBar.text(percentComplete + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function (response) {
                        $('#uploadProgressWrapper').hide();
                        $btn.prop('disabled', false).text(originalText);
                        toastr.success('Aula salva com sucesso!');

                        // Close modal and reload page immediately usually creates a jarring effect
                        // Better interaction: Close modal, then reload.
                        $('#lessonModal').modal('hide');
                        setTimeout(function () {
                            window.location.reload();
                        }, 500); // 0.5s delay to see success
                    },
                    error: function (xhr) {
                        $('#uploadProgressWrapper').hide();
                        $btn.prop('disabled', false).text(originalText);

                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let msg = '';
                            $.each(xhr.responseJSON.errors, function (k, v) { msg += v[0] + '<br>'; });
                            toastr.error(msg);
                        } else if (xhr.status === 413) {
                            toastr.error('O arquivo é muito grande para o servidor. Limite: 500MB.');
                        } else if (xhr.status === 403) {
                            toastr.error('Não autorizado. Verifique suas permissões.');
                        } else {
                            toastr.error('Erro ao salvar.');
                            console.error(xhr);
                        }
                    }
                });
            });

            // Ajax Delete Lesson
            $('.ajax-delete').on('submit', function (e) {
                e.preventDefault();
                if (!confirm('Excluir aula?')) return;
                $.post($(this).attr('action'), $(this).serialize(), function () {
                    location.reload();
                });
            });

            // Initialize Tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Image Preview Logic
            $('#courseThumbnail').on('change', function (event) {
                var file = event.target.files[0];
                if (file) {
                    // Update Label
                    $(this).next('.custom-file-label').html(file.name);

                    // Show Preview
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        // Find or create img element
                        // The refactored DOM is different, we need to target correctly
                        // New structure: .card-body > .row > .col-md-4 (img wrapper)

                        var container = $(event.target).closest('.row').find('.col-md-4');
                        // Wait, $(event.target) is in .col-md-8. Closest .row contains both.

                        var img = container.find('img');

                        if (img.length) {
                            img.attr('src', e.target.result);
                        } else {
                            // Create img if replacing icon
                            var wrapper = container.find('.bg-white'); // It has bg-white now
                            if (wrapper.length) wrapper.replaceWith('<img src="' + e.target.result + '" class="img-fluid rounded shadow-sm">');
                            else container.prepend('<img src="' + e.target.result + '" class="img-fluid rounded shadow-sm">');
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Main Course Form AJAX Submit (to show progress)
            $('form[enctype="multipart/form-data"]').on('submit', function (e) {
                // Check if it's the certificate form (it has id certForm)
                if ($(this).attr('id') === 'certForm') return; // Let the other listener handle it or this one?
                // Wait, the cert form ALSO has enctype multipart. We need to distinguish.
                // The Main form doesn't have an ID in my code above, but it's in #info.
                // Let's filter by action?

                // Actually, the Cert form listener has `e.preventDefault()` too.
                // If this runs first, it might conflict.
                // Let's ensure this listener only applies to the main course form.
                // The key is: $(this).closest('#info').length

                if ($(this).closest('#info').length === 0) return; // Skip if not in info tab

                e.preventDefault();

                var $form = $(this);
                var $btn = $form.find('button[type="submit"], button.btn-primary');
                var originalText = $btn.html(); // Use html to keep icon

                // Check if file is selected for progress bar
                var hasFile = $('#courseThumbnail')[0].files.length > 0;
                if (hasFile) {
                    $('#thumbnailProgressWrapper').show();
                }

                // FORCE SYNC SUMMERNOTE
                if ($('#fullDescription').length) {
                    $('#fullDescription').val($('#fullDescription').summernote('code'));
                }

                $btn.prop('disabled', true).text('Salvando...');

                var formData = new FormData(this);

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function () {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function (evt) {
                            if (evt.lengthComputable && hasFile) {
                                var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                                $('#thumbnailProgressBar').css('width', percentComplete + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function (response) {
                        toastr.success('Curso salvo com sucesso!');
                        setTimeout(function () {
                            window.location.href = "{{ route('admin.courses.index') }}";
                        }, 500);
                    },
                    error: function (xhr) {
                        $('#thumbnailProgressWrapper').hide();
                        $btn.prop('disabled', false).html(originalText);

                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            // Validation errors
                            let msg = '';
                            $.each(xhr.responseJSON.errors, function (k, v) { msg += v[0] + '<br>'; });
                            toastr.error(msg);
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            // Server Exception (e.g. DB error)
                            // Cut long messages if needed, but usually helpful to see
                            toastr.error('Erro: ' + xhr.responseJSON.message);
                        } else {
                            toastr.error('Erro ao salvar curso. Código: ' + xhr.status);
                            console.error(xhr);
                        }
                    }
                });
            });
        });
        function copyLink() {
            var copyText = document.getElementById("courseLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            toastr.success('Link copiado!');
        }
    </script>

    <!-- jQuery UI for Draggable -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <script>
        // Certificate Editor Logic
        $(document).ready(function () {
            if ('{{ $course->exists }}' == '') return;

            // Initial Settings from DB (or defaults)
            let certSettings = {!! $course->certificate_settings ? json_encode($course->certificate_settings) : '{}' !!};

            // Default Tags (with mandatory logo)
            const defaultTags = {
                'student_name': { x: 50, y: 40, text: '[Nome do Aluno]', fontSize: 30, color: '#000000', fontWeight: 'bold', fontFamily: 'Arial, sans-serif' },
                'course_name': { x: 50, y: 55, text: '[Nome do Curso]', fontSize: 24, color: '#333333', fontWeight: 'bold', fontFamily: 'Arial, sans-serif' },
                'completion_date': { x: 50, y: 65, text: 'Concluído em: 01/01/2024', fontSize: 16, color: '#555555', fontWeight: 'normal', fontFamily: 'Arial, sans-serif' },
                'certificate_code': { x: 50, y: 85, text: 'Validação: ABC-123', fontSize: 12, color: '#999999', fontWeight: 'normal', fontFamily: 'Arial, sans-serif' },
                'platform_logo': { x: 50, y: 10, text: 'LOGO UNN', fontSize: 36, color: '#0066cc', fontWeight: 'bold', fontFamily: 'Georgia, serif', width: 120, height: 60, mandatory: true }
            };

            // Merge defaults
            $.each(defaultTags, function (key, val) {
                if (!certSettings[key]) {
                    certSettings[key] = val;
                }
            });

            const $canvas = $('#cert-elements-layer');
            let activeElementId = null;
            let customFonts = [];

            // Load Custom Fonts
            $.ajax({
                url: '{{ route("admin.fonts.api.active") }}',
                type: 'GET',
                success: function (fonts) {
                    customFonts = fonts;
                    // Add to selector
                    fonts.forEach(font => {
                        $('#style-font-family').append(`<option value="${font.font_family}">${font.name}</option>`);

                        // If it's a Google Font link, inject it
                        if (font.type === 'google_link' && font.google_font_url) {
                            $('head').append(`<link href="${font.google_font_url}" rel="stylesheet">`);
                        }
                        // If it's a file upload, inject @font-face
                        else if (font.type === 'file' && font.file_path) {
                            const fontUrl = '{{ asset('')}}' + font.file_path;
                            $('head').append(`<style>@font-face { font-family: '${font.font_family}'; src: url('${fontUrl}'); }</style>`);
                        }
                    });
                }
            });


            // Render Elements
            function renderElements() {
                $canvas.empty();
                $.each(certSettings, function (key, data) {
                    // Check visibility based on toggle switch
                    // Only render if we assume it's visible or handle visibility via CSS

                    // Create Element
                    let $el = $('<div>')
                        .addClass('cert-element')
                        .attr('id', 'el-' + key)
                        .attr('data-tag', key)
                        .css({
                            position: 'absolute',
                            left: data.x + '%',
                            top: data.y + '%',
                            transform: 'translate(-50%, -50%)', // Center based on coords
                            fontSize: data.fontSize + 'px',
                            color: data.color,
                            fontWeight: data.fontWeight,
                            fontFamily: data.fontFamily || 'Arial, sans-serif',
                            cursor: 'move',
                            whiteSpace: 'nowrap',
                            border: '1px dashed transparent',
                            padding: '5px'
                        })
                        .text(data.text);

                    // Click to Select
                    $el.on('mousedown', function (e) {
                        $('.cert-element').css('border-color', 'transparent');
                        $(this).css('border-color', '#007bff');
                        activeElementId = key;

                        // Populate Tools
                        $('#selected-elem-name').text(data.text);
                        $('#style-font-size').val(data.fontSize);
                        $('#style-color').val(data.color);
                        $('#style-font-weight').val(data.fontWeight);
                        $('#style-font-family').val(data.fontFamily || 'Arial, sans-serif');
                        $('#cert-style-controls').show();

                        e.stopPropagation();
                    });

                    $canvas.append($el);
                });

                // Init Draggable
                $('.cert-element').draggable({
                    containment: "#cert-canvas",
                    scroll: false,
                    stop: function (event, ui) {
                        let key = $(this).data('tag');
                        let parentW = $('#cert-canvas').width();
                        let parentH = $('#cert-canvas').height();

                        // Get current position (Top-Left corner of element)
                        let leftPx = ui.position.left;
                        let topPx = ui.position.top;

                        // Convert to %
                        // Note: We use top-left anchoring here for simplicity in storage, 
                        // but visual rendering uses translate(-50%,-50%) which might shift visual center.
                        // A robust system would calculate text width. For now, simple % is enough.
                        certSettings[key].x = (leftPx / parentW) * 100;
                        certSettings[key].y = (topPx / parentH) * 100;
                    }
                });

                // Remove transform for easier drag math in jQuery UI
                $('.cert-element').css('transform', 'none');

                // Apply visibility (except mandatory logo)
                $('.cert-toggle').each(function () {
                    let key = $(this).data('tag');
                    // Platform logo is mandatory and cannot be hidden
                    if (key !== 'platform_logo' && !$(this).is(':checked')) {
                        $('#el-' + key).hide();
                    }
                });

                // Ensure logo is ALWAYS visible
                $('#el-platform_logo').show();

                // Load logo size from settings
                if (certSettings['platform_logo']) {
                    $('#logo-width').val(certSettings['platform_logo'].width || 120);
                    $('#logo-height').val(certSettings['platform_logo'].height || 60);
                }
            }

            renderElements();

            // Style Change Listeners
            $('#style-font-size').on('input', function () {
                if (activeElementId) {
                    let val = $(this).val();
                    certSettings[activeElementId].fontSize = val;
                    $('#el-' + activeElementId).css('font-size', val + 'px');
                }
            });
            $('#style-color').on('input', function () {
                if (activeElementId) {
                    let val = $(this).val();
                    certSettings[activeElementId].color = val;
                    $('#el-' + activeElementId).css('color', val);
                }
            });
            $('#style-font-weight').on('change', function () {
                if (activeElementId) {
                    let val = $(this).val();
                    certSettings[activeElementId].fontWeight = val;
                    $('#el-' + activeElementId).css('font-weight', val);
                }
            });

            $('#style-font-family').on('change', function () {
                if (activeElementId) {
                    let val = $(this).val();
                    certSettings[activeElementId].fontFamily = val;
                    $('#el-' + activeElementId).css('font-family', val);
                }
            });
            
            // Logo Size Controls
            $('#logo-width').on('input', function() {
                let val = parseInt($(this).val()) || 120;
                // Clamp between min and max
                val = Math.max(50, Math.min(400, val));
                certSettings['platform_logo'].width = val;
                // Visual feedback - update element size
                toastr.info('Largura da logo atualizada: ' + val + 'px');
            });
            
            $('#logo-height').on('input', function() {
                let val = parseInt($(this).val()) || 60;
                // Clamp between min and max
                val = Math.max(30, Math.min(200, val));
                certSettings['platform_logo'].height = val;
                toastr.info('Altura da logo atualizada: ' + val + 'px');
            });

            // Toggle Visibility (logo is MANDATORY and cannot be hidden)
            $('.cert-toggle').on('change', function () {
                let key = $(this).data('tag');
                
                // Prevent logo from being hidden
                if(key === 'platform_logo') {
                    $(this).prop('checked', true); // Force checked
                    toastr.warning('A logo da plataforma é obrigatória e não pode ser removida.');
                    return;
                }
                
                if ($(this).is(':checked')) {
                    $('#el-' + key).show();
                } else {
                    $('#el-' + key).hide();
                }
            });

            // Sync Settings on Submit (AJAX)
            $('#certForm').on('submit', function (e) {
                e.preventDefault();
                $('#certificate_settings_input').val(JSON.stringify(certSettings));

                var formData = new FormData(this);
                var $btn = $('#btn-save-cert');
                var originalText = $btn.html();

                $btn.prop('disabled', true).text('Salvando...');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        toastr.success('Configurações do Certificado salvas!');
                        $btn.prop('disabled', false).html(originalText);
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false).html(originalText);
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let msg = '';
                            $.each(xhr.responseJSON.errors, function (k, v) { msg += v[0] + '<br>'; });
                            toastr.error(msg);
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('Erro ao salvar. Código: ' + xhr.status);
                        }
                    }
                });
            });
        });

        // Preview Background
        window.previewCertBg = function (input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    if ($('#cert-bg-img').length) {
                        $('#cert-bg-img').attr('src', e.target.result);
                    } else {
                        $('#cert-bg-placeholder').replaceWith('<img src="' + e.target.result + '" id="cert-bg-img" style="width: 100%; height: 100%; object-fit: cover; position: absolute; z-index: 1;">');
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush