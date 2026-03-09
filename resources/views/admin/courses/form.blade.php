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
            margin-bottom: 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
            /* Keep above potential artifacts */
        }

        .lesson-item:hover {
            border-color: #007bff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
            transform: translateY(-2px);
            background: #fff !important;
        }

        .lesson-item .btn {
            border-radius: 6px;
            padding: 5px 10px;
            border-width: 1.5px;
        }

        .lesson-item .btn:focus {
            box-shadow: none !important;
            outline: none !important;
        }

        #lessons-list {
            position: relative;
        }

        /* Suppress potential timeline artifacts from theme */
        #lessons-list::before,
        #lessons-list::after,
        .lesson-item::before,
        .lesson-item::after {
            display: none !important;
            content: none !important;
        }

        .lesson-item .d-flex:first-child {
            flex: 1;
            min-width: 0;
            /* Allow title to truncate if needed */
        }

        .lesson-item .d-flex:last-child {
            flex-shrink: 0;
            margin-left: auto;
        }

        .lesson-item .fa-grip-vertical {
            flex-shrink: 0;
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

        .course-thumb-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            background: #f8fafc;
            min-height: 92px;
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            cursor: pointer;
            transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
        }

        .course-thumb-dropzone:hover,
        .course-thumb-dropzone:focus {
            border-color: #3b82f6;
            background: #eff6ff;
            box-shadow: 0 0 0 0.18rem rgba(59, 130, 246, .18);
            outline: none;
        }

        .course-thumb-dropzone.is-dragover {
            border-color: #2563eb;
            background: #dbeafe;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, .2);
        }

        .course-thumb-dropzone .dropzone-main {
            color: #334155;
            font-weight: 600;
            line-height: 1.4;
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
                        @if($course->exists)
                            <li class="nav-item">
                                <a class="nav-link" id="lessons-tab" data-toggle="tab" href="#lessons" role="tab"
                                    aria-controls="lessons" aria-selected="false">
                                    <i class="fas fa-layer-group mr-1"></i> Conteúdo / Aulas
                                </a>
                            </li>
                            <li class="nav-item" id="nav-item-cert"
                                style="{{ $course->is_certificate_enabled ? '' : 'display:none;' }}">
                                <a class="nav-link" id="cert-tab" data-toggle="tab" href="#certificate" role="tab"
                                    aria-controls="certificate" aria-selected="false">
                                    <i class="fas fa-certificate mr-1"></i> Certificado
                                </a>
                            </li>
                        @endif
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
                                                        <input type="file" name="thumbnail" class="d-none"
                                                            id="courseThumbnail"
                                                            accept="image/png, image/jpeg, image/jpg, image/webp">
                                                        <div id="courseThumbnailDropzone" class="course-thumb-dropzone mb-2"
                                                            role="button" tabindex="0"
                                                            aria-label="Selecionar imagem de capa">
                                                            <div class="dropzone-main">
                                                                <i class="fas fa-cloud-upload-alt mr-1 text-primary"></i>
                                                                Arraste e solte a capa aqui ou clique para selecionar
                                                            </div>
                                                            <small class="text-muted mt-1 d-block"
                                                                id="courseThumbnailFilename">Nenhum arquivo
                                                                selecionado.</small>
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

                                                <div class="card border-0 shadow-sm mb-3">
                                                    <div class="card-body p-3">
                                                        <h6 class="text-muted mb-3">
                                                            <i class="fas fa-bolt mr-2"></i> Promoção relâmpago
                                                        </h6>

                                                        <div class="form-group mb-2">
                                                            <label class="small text-muted">Preço promocional (R$)</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">R$</span>
                                                                </div>
                                                                <input name="flash_sale_price"
                                                                    class="form-control mask-money"
                                                                    value="{{ old('flash_sale_price', $course->flash_sale_price) }}"
                                                                    placeholder="0,00">
                                                            </div>
                                                        </div>

                                                        <div class="form-group mb-0">
                                                            <label class="small text-muted">Termina em</label>
                                                            <input type="datetime-local" name="flash_sale_ends_at"
                                                                class="form-control"
                                                                value="{{ old('flash_sale_ends_at', $course->flash_sale_ends_at ? $course->flash_sale_ends_at->format('Y-m-d\\TH:i') : '') }}">
                                                            <small class="text-muted d-block mt-1">
                                                                Quando expirar, o preço volta ao valor normal
                                                                automaticamente.
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label>Autor / Instrutor</label>
                                                    <input name="author_name" class="form-control"
                                                        value="{{ old('author_name', $course->author_name ?? Auth::user()->name) }}">
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group mb-3">
                                                            <div
                                                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-primary">
                                                                <input type="hidden" name="is_recurring" value="0">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    name="is_recurring" id="is_recurring" value="1" {{ $course->is_recurring ? 'checked' : '' }}
                                                                    onchange="toggleRecurringOptions(this)">
                                                                <label class="custom-control-label font-weight-bold"
                                                                    for="is_recurring">Venda como Assinatura
                                                                    (Recorrente)</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div id="recurring_options"
                                                    style="{{ $course->is_recurring ? '' : 'display:none;' }}">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group mb-3">
                                                                <label class="small text-muted">Período</label>
                                                                <select name="period" class="form-control form-control-sm">
                                                                    <option value="months" {{ $course->period == 'months' ? 'selected' : '' }}>Mensal</option>
                                                                    <option value="days" {{ $course->period == 'days' ? 'selected' : '' }}>Diário</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-group mb-3">
                                                                <label class="small text-muted">A cada (ciclo)</label>
                                                                <input type="number" name="billing_cycle"
                                                                    class="form-control form-control-sm"
                                                                    value="{{ $course->billing_cycle ?? 1 }}" min="1">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group mb-4">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input"
                                                            name="is_featured" id="is_featured" value="1" {{ $course->is_featured ? 'checked' : '' }}>
                                                        <label class="custom-control-label font-weight-bold"
                                                            for="is_featured">Destaque na Home</label>
                                                    </div>
                                                </div>

                                                <div class="form-group mb-4">
                                                    <label class="font-weight-bold"><i class="fas fa-eye mr-1"></i> Onde Exibir?</label>
                                                    <select name="visibility" class="form-control" style="border-radius: 8px;">
                                                        <option value="ambos" {{ old('visibility', $course->visibility ?? 'ambos') == 'ambos' ? 'selected' : '' }}>Ambos os locais</option>
                                                        <option value="somos_unn" {{ old('visibility', $course->visibility ?? 'ambos') == 'somos_unn' ? 'selected' : '' }}>Somente Somos UNN</option>
                                                        <option value="somos_unicas" {{ old('visibility', $course->visibility ?? 'ambos') == 'somos_unicas' ? 'selected' : '' }} style="color: #ec4899; font-weight: bold;">Somente Somos Únicas</option>
                                                    </select>
                                                </div>

                                                <script>
                                                    function toggleRecurringOptions(el) {
                                                        document.getElementById('recurring_options').style.display = el.checked ? 'block' : 'none';
                                                    }
                                                </script>

                                                <div class="form-group mb-4">
                                                    <h6 class="text-muted mb-2"><i
                                                            class="fas fa-play-circle mr-2"></i>Player de Vídeo</h6>

                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input"
                                                            name="video_block_download" id="video_block_download" value="1"
                                                            {{ $course->video_block_download ? 'checked' : '' }}>
                                                        <label class="custom-control-label font-weight-bold"
                                                            for="video_block_download">Bloquear download do vídeo</label>
                                                    </div>
                                                    <small class="text-muted d-block mt-1">
                                                        Remove botões/menus de download (não impede download via ferramentas
                                                        do navegador).
                                                    </small>

                                                    <div class="custom-control custom-switch mt-3">
                                                        <input type="checkbox" class="custom-control-input"
                                                            name="video_floating_enabled" id="video_floating_enabled"
                                                            value="1" {{ $course->video_floating_enabled ? 'checked' : '' }}>
                                                        <label class="custom-control-label font-weight-bold"
                                                            for="video_floating_enabled">Mini player flutuante</label>
                                                    </div>

                                                    <div class="custom-control custom-switch mt-3">
                                                        <input type="checkbox" class="custom-control-input"
                                                            name="is_certificate_enabled" id="is_certificate_enabled"
                                                            value="1" {{ $course->is_certificate_enabled ? 'checked' : '' }}>
                                                        <label class="custom-control-label font-weight-bold"
                                                            for="is_certificate_enabled">Habilitar Certificado</label>
                                                    </div>

                                                    <div class="row mt-3" id="video_floating_size_group"
                                                        style="{{ $course->video_floating_enabled ? '' : 'display:none;' }}">
                                                        <div class="col-6">
                                                            <label class="small text-muted">Largura (px)</label>
                                                            <input type="number" min="260" max="960"
                                                                name="video_floating_width" class="form-control"
                                                                value="{{ old('video_floating_width', $course->video_floating_width ?? 420) }}">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="small text-muted">Altura (px)</label>
                                                            <input type="number" min="160" max="720"
                                                                name="video_floating_height" class="form-control"
                                                                value="{{ old('video_floating_height', $course->video_floating_height ?? 236) }}">
                                                        </div>
                                                        <div class="col-12">
                                                            <small class="text-muted d-block mt-2">
                                                                Ao rolar a página, o vídeo fica fixo no canto da tela.
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr>

                                                <button class="btn btn-primary btn-block btn-lg shadow-sm">
                                                    <i class="fas fa-save mr-2"></i> Salvar Informações
                                                </button>
                                            </div>
                                        </div>

                                        @if($course->exists)
                                            <div class="card shadow-sm border-0 mt-3">
                                                <div class="card-body">
                                                    <label class="mb-2">Link do Curso</label>
                                                    <div class="input-group mb-2">
                                                        <input type="text" class="form-control form-control-sm" id="courseLink"
                                                            value="{{ route('courses.show', $course->slug ?: $course->id) }}"
                                                            readonly>
                                                        <div class="input-group-append">
                                                            <button class="btn btn-outline-secondary btn-sm" type="button"
                                                                onclick="copyLink()">
                                                                <i class="fas fa-copy"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <a href="{{ route('courses.show', $course->slug ?: $course->id) }}"
                                                        target="_blank" class="btn btn-sm btn-outline-info btn-block">
                                                        Visualizar Página <i class="fas fa-external-link-alt ml-1"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endif

                                        @if(!$course->exists && !(Auth::user() && (Auth::user()->isAdmin() || Auth::user()->role === 'superadmin')))
                                            <div class="alert alert-info mt-3 text-center">
                                                <i class="fas fa-info-circle"></i> Salve para liberar a aba de Aulas.
                                            </div>
                                        @elseif(!$course->exists && Auth::user() && (Auth::user()->isAdmin() || Auth::user()->role === 'superadmin'))
                                            <div class="alert alert-success mt-3 text-center">
                                                <i class="fas fa-unlock"></i> Como admin, você pode cadastrar aulas e
                                                certificados a qualquer momento. O curso será salvo automaticamente ao adicionar
                                                conteúdo.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- TAB 2: AULAS -->
                        @if($course->exists)
                            <div class="tab-pane fade" id="lessons" role="tabpanel" aria-labelledby="lessons-tab">
                                <div class="d-flex align-items-center mb-4">
                                    <h4 class="mb-0 text-dark">Grade Curricular</h4>
                                    <button class="btn btn-success shadow-sm ml-auto" id="btnNovaAula" type="button">
                                        <i class="fas fa-plus mr-1"></i> Nova Aula
                                    </button>
                                </div>

                                @push('scripts')
                                    <script>
                                        document.addEventListener('DOMContentLoaded', function () {
                                            const btnNovaAula = document.getElementById('btnNovaAula');
                                            if (!btnNovaAula) return;
                                            btnNovaAula.addEventListener('click', function (e) {
                                                e.preventDefault();
                                                // Se já existe course_id, apenas abre o modal normalmente
                                                const courseId = {{ $course->exists ? $course->id : 'null' }};
                                                if (courseId) {
                                                    if (typeof openLessonModal === 'function') openLessonModal();
                                                    return;
                                                }
                                                // Auto-save do formulário de curso via AJAX
                                                const form = btnNovaAula.closest('form');
                                                if (!form) return;
                                                const formData = new FormData(form);
                                                formData.append('status', 'draft');
                                                btnNovaAula.disabled = true;
                                                btnNovaAula.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Salvando...';
                                                fetch(form.action, {
                                                    method: 'POST',
                                                    headers: {
                                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                                    },
                                                    body: formData
                                                })
                                                    .then(response => response.json())
                                                    .then(data => {
                                                        if (data && data.id) {
                                                            // Redireciona para a edição do curso já salvo
                                                            window.location.href = `/admin/courses/${data.id}/edit?openLesson=1`;
                                                        } else {
                                                            Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro ao salvar curso. Tente novamente.' });
                                                            btnNovaAula.disabled = false;
                                                            btnNovaAula.innerHTML = '<i class="fas fa-plus mr-1"></i> Nova Aula';
                                                        }
                                                    })
                                                    .catch(() => {
                                                        Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro ao salvar curso. Tente novamente.' });
                                                        btnNovaAula.disabled = false;
                                                        btnNovaAula.innerHTML = '<i class="fas fa-plus mr-1"></i> Nova Aula';
                                                    });
                                            });
                                        });
                                    </script>
                                @endpush

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
                                                        @if($lesson->is_free_preview)
                                                            <span class="badge badge-info">
                                                                @if(($lesson->free_preview_mode ?? 'full') === 'time' && (int) ($lesson->free_preview_seconds ?? 0) > 0)
                                                                    Preview {{ gmdate('i:s', (int) $lesson->free_preview_seconds) }}
                                                                @else
                                                                    Preview Grátis
                                                                @endif
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center flex-nowrap" style="gap: 5px;">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-lesson"
                                                    data-id="{{ $lesson->id }}" title="Editar"><i class="fas fa-edit"></i></button>
                                                <form action="{{ route('admin.courses.lessons.destroy', [$course, $lesson]) }}"
                                                    method="POST" class="d-inline ajax-delete">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir"><i
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
                                        <div class="col-12">
                                            <!-- CANVAS AREA -->
                                            <div class="card shadow-sm border-0">
                                                <div class="card-header bg-secondary text-white small py-2">
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                        <span>Editor Visual (A4 Paisagem)</span>
                                                        <div class="d-flex align-items-center">
                                                            <div class="input-group input-group-sm" style="width: 220px;">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">Zoom</span>
                                                                </div>
                                                                <select id="cert-zoom" class="custom-select custom-select-sm">
                                                                    <option value="0.5">50%</option>
                                                                    <option value="0.75">75%</option>
                                                                    <option value="1" selected>100%</option>
                                                                    <option value="1.25">125%</option>
                                                                    <option value="1.5">150%</option>
                                                                    <option value="2">200%</option>
                                                                    <option value="2.5">250%</option>
                                                                    <option value="3">300%</option>
                                                                </select>
                                                                <div class="input-group-append">
                                                                    <button type="button" class="btn btn-outline-light"
                                                                        id="cert-fit">
                                                                        Fit
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
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
                                                                    <p class="small">Faça upload no painel abaixo</p>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <!-- Grid Overlay (optional) -->
                                                        <div id="cert-grid-overlay"
                                                            style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:5; pointer-events:none; display:none;">
                                                        </div>

                                                        <!-- Draggable Container z-index 10 -->
                                                        <div id="cert-elements-layer"
                                                            style="position: absolute; top:0; left:0; width: 100%; height: 100%; z-index: 10;">
                                                            <!-- Elements will be injected here via JS -->
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mt-3">
                                            <div class="card shadow-sm border-0">
                                                <div class="card-header bg-dark text-white font-weight-bold">Configurações</div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-12 col-xl-6">

                                                            {{-- O switch foi movido para a barra lateral "Publicação" --}}
                                                            <input type="hidden" name="is_certificate_enabled"
                                                                value="{{ $course->is_certificate_enabled ? '1' : '0' }}"
                                                                id="is_certificate_enabled_hidden">

                                                            <div class="form-group">
                                                                <label
                                                                    class="small text-muted text-uppercase font-weight-bold">Fundo
                                                                    do
                                                                    Certificado</label>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input"
                                                                        name="certificate_bg" accept="image/*"
                                                                        onchange="previewCertBg(this)">
                                                                    <label class="custom-file-label">Escolher arquivo</label>
                                                                </div>
                                                                <small class="text-muted">Recomendado: 1920x1080px
                                                                    (PNG/JPG)</small>
                                                            </div>


                                                            <div class="form-group">
                                                                <label
                                                                    class="small text-muted text-uppercase font-weight-bold">Ajuste
                                                                    do Fundo</label>
                                                                <select id="cert-bg-fit" class="form-control form-control-sm">
                                                                    <option value="cover">Cover (cortar)</option>
                                                                    <option value="stretch">Stretch (esticar)</option>
                                                                </select>
                                                                <small class="text-muted">Mantém consistência entre editor e
                                                                    PDF</small>
                                                            </div>

                                                            <div class="border rounded p-2 mb-3 bg-light">
                                                                <div
                                                                    class="small text-muted text-uppercase font-weight-bold mb-2">
                                                                    Ferramentas</div>

                                                                <div class="custom-control custom-switch mb-2">
                                                                    <input type="checkbox" class="custom-control-input"
                                                                        id="cert-grid-enabled">
                                                                    <label class="custom-control-label"
                                                                        for="cert-grid-enabled">Mostrar grade</label>
                                                                </div>

                                                                <div class="form-group mb-2">
                                                                    <label class="small text-muted mb-0">Passo da grade</label>
                                                                    <select id="cert-grid-step"
                                                                        class="form-control form-control-sm">
                                                                        <option value="1">1%</option>
                                                                        <option value="2">2%</option>
                                                                        <option value="5" selected>5%</option>
                                                                        <option value="10">10%</option>
                                                                    </select>
                                                                </div>

                                                                <div class="custom-control custom-switch mb-2">
                                                                    <input type="checkbox" class="custom-control-input"
                                                                        id="cert-snap-enabled" checked>
                                                                    <label class="custom-control-label"
                                                                        for="cert-snap-enabled">Snap na grade</label>
                                                                </div>

                                                                <div class="form-group mb-2">
                                                                    <label class="small text-muted mb-0">Passo do snap</label>
                                                                    <select id="cert-snap-step"
                                                                        class="form-control form-control-sm">
                                                                        <option value="0.25">0,25%</option>
                                                                        <option value="0.5">0,5%</option>
                                                                        <option value="1" selected>1%</option>
                                                                        <option value="2">2%</option>
                                                                        <option value="5">5%</option>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group mb-0">
                                                                    <label class="small text-muted mb-0">Nudge (setas)</label>
                                                                    <select id="cert-nudge-step"
                                                                        class="form-control form-control-sm">
                                                                        <option value="0.1">0,1%</option>
                                                                        <option value="0.25">0,25%</option>
                                                                        <option value="0.5" selected>0,5%</option>
                                                                        <option value="1">1%</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label
                                                                    class="small text-muted text-uppercase font-weight-bold">Carga
                                                                    Horária (automática)</label>
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control bg-light"
                                                                        value="{{ $course->total_hours > 0 ? $course->total_hours . 'h' : 'Adicione aulas para calcular' }}"
                                                                        readonly>
                                                                    <div class="input-group-append">
                                                                        <span class="input-group-text bg-info text-white">
                                                                            <i class="fas fa-calculator"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <small class="text-muted">Calculado automaticamente pela soma
                                                                    das
                                                                    durações das aulas</small>
                                                            </div>

                                                            <div class="form-group">
                                                                <label
                                                                    class="small text-muted text-uppercase font-weight-bold">Assinatura
                                                                    do Instrutor</label>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input"
                                                                        name="instructor_signature" id="instructor_signature"
                                                                        accept="image/png,image/jpeg,image/jpg"
                                                                        onchange="previewSignature(this)">
                                                                    <label class="custom-file-label">Escolher arquivo</label>
                                                                </div>
                                                                <small class="text-muted">Recomendado: 300x100px PNG
                                                                    transparente</small>
                                                                @if($course->instructor_signature)
                                                                    <div class="mt-2">
                                                                        <img src="{{ asset($course->instructor_signature) }}"
                                                                            id="signaturePreview" class="img-thumbnail"
                                                                            style="max-width: 150px; background: #f8f9fa;">
                                                                    </div>
                                                                @else
                                                                    <div class="mt-2" id="signaturePreviewWrapper"
                                                                        style="display:none;">
                                                                        <img id="signaturePreview" class="img-thumbnail"
                                                                            style="max-width: 150px; background: #f8f9fa;">
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="form-group">
                                                                <label
                                                                    class="small text-muted text-uppercase font-weight-bold">Título
                                                                    do Certificado</label>
                                                                <input type="text" class="form-control" name="certificate_title"
                                                                    id="certificate_title"
                                                                    value="{{ $course->certificate_settings['title'] ?? 'CERTIFICATE OF ACHIEVEMENT' }}"
                                                                    placeholder="CERTIFICATE OF ACHIEVEMENT">
                                                            </div>

                                                            <div class="form-group">
                                                                <label
                                                                    class="small text-muted text-uppercase font-weight-bold">Texto
                                                                    de
                                                                    Apresentação</label>
                                                                <textarea class="form-control" name="presentation_text"
                                                                    id="presentation_text" rows="2"
                                                                    placeholder="This certificate is proudly present to">{{ $course->certificate_settings['presentation_text'] ?? $course->default_presentation_text }}</textarea>
                                                                <small class="text-muted">Texto acima do nome do aluno (gerado
                                                                    automaticamente)</small>
                                                            </div>

                                                        </div>

                                                        <div class="col-12 col-xl-6 mt-3 mt-xl-0">
                                                            <hr class="d-xl-none">

                                                            <h6 class="small text-muted text-uppercase font-weight-bold mb-3">
                                                                Elementos
                                                                Visíveis</h6>
                                                            <div class="list-group mb-3" id="cert-available-tags">
                                                                <!-- Checkboxes to toggle visibility of elements -->
                                                                <div class="list-group-item p-2 border-0">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input cert-toggle"
                                                                            id="toggle-student" data-tag="student_name" checked>
                                                                        <label class="custom-control-label"
                                                                            for="toggle-student">Nome do
                                                                            Aluno</label>
                                                                    </div>
                                                                </div>
                                                                <div class="list-group-item p-2 border-0">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input cert-toggle"
                                                                            id="toggle-course" data-tag="course_name" checked>
                                                                        <label class="custom-control-label"
                                                                            for="toggle-course">Nome do
                                                                            Curso</label>
                                                                    </div>
                                                                </div>
                                                                <div class="list-group-item p-2 border-0">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input cert-toggle"
                                                                            id="toggle-date" data-tag="completion_date" checked>
                                                                        <label class="custom-control-label"
                                                                            for="toggle-date">Data de
                                                                            Conclusão</label>
                                                                    </div>
                                                                </div>
                                                                <div class="list-group-item p-2 border-0">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input cert-toggle"
                                                                            id="toggle-code" data-tag="certificate_code"
                                                                            checked>
                                                                        <label class="custom-control-label"
                                                                            for="toggle-code">Código de
                                                                            Validação</label>
                                                                    </div>
                                                                </div>
                                                                <div class="list-group-item p-2 border-0">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input cert-toggle"
                                                                            id="toggle-author" data-tag="author_name" checked>
                                                                        <label class="custom-control-label"
                                                                            for="toggle-author">Nome do
                                                                            Autor</label>
                                                                    </div>
                                                                </div>
                                                                <div class="list-group-item p-2 border-0">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input cert-toggle"
                                                                            id="toggle-workload" data-tag="workload_hours"
                                                                            checked>
                                                                        <label class="custom-control-label"
                                                                            for="toggle-workload">Carga
                                                                            Horária</label>
                                                                    </div>
                                                                </div>
                                                                <div class="list-group-item p-2 border-0">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input cert-toggle"
                                                                            id="toggle-title" data-tag="title">
                                                                        <label class="custom-control-label"
                                                                            for="toggle-title">Título do
                                                                            Certificado</label>
                                                                    </div>
                                                                </div>
                                                                <div class="list-group-item p-2 border-0">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input cert-toggle"
                                                                            id="toggle-presentation"
                                                                            data-tag="presentation_text">
                                                                        <label class="custom-control-label"
                                                                            for="toggle-presentation">Texto de
                                                                            Apresentação</label>
                                                                    </div>
                                                                </div>
                                                                <div class="list-group-item p-2 border-0">
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox"
                                                                            class="custom-control-input cert-toggle"
                                                                            id="toggle-signature"
                                                                            data-tag="instructor_signature">
                                                                        <label class="custom-control-label"
                                                                            for="toggle-signature">Assinatura do
                                                                            Instrutor</label>
                                                                    </div>
                                                                </div>
                                                                <div
                                                                    class="list-group-item p-2 border-0 bg-warning rounded mt-2">
                                                                    <div
                                                                        class="d-flex align-items-center justify-content-between">
                                                                        <div>
                                                                            <i class="fas fa-lock mr-2 text-dark"></i>
                                                                            <span class="font-weight-bold text-dark">Logo da
                                                                                Plataforma</span>
                                                                        </div>
                                                                        <small class="badge badge-dark">OBRIGATÓRIO</small>
                                                                    </div>
                                                                    <small class="text-dark d-block mt-1"><i
                                                                            class="fas fa-info-circle"></i> A logo não pode ser
                                                                        removida
                                                                        do certificado</small>

                                                                    <div class="mt-3">
                                                                        <label
                                                                            class="small text-dark mb-1 font-weight-bold">Tamanho
                                                                            da
                                                                            Logo</label>
                                                                        <div class="row">
                                                                            <div class="col-6">
                                                                                <label class="small text-muted mb-0">Largura
                                                                                    (px)</label>
                                                                                <input type="number" id="logo-width"
                                                                                    class="form-control form-control-sm"
                                                                                    min="50" max="400" value="120"
                                                                                    placeholder="120">
                                                                            </div>
                                                                            <div class="col-6">
                                                                                <label class="small text-muted mb-0">Altura
                                                                                    (px)</label>
                                                                                <input type="number" id="logo-height"
                                                                                    class="form-control form-control-sm"
                                                                                    min="30" max="200" value="60"
                                                                                    placeholder="60">
                                                                            </div>
                                                                        </div>
                                                                        <small class="text-muted">Min: 50x30px | Máx:
                                                                            400x200px</small>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <hr>

                                                            <h6 class="small text-muted text-uppercase font-weight-bold mb-3">
                                                                Camadas</h6>
                                                            <div class="list-group mb-3" id="cert-layers"></div>

                                                            <div id="cert-style-controls" style="display:none;"
                                                                class="bg-light p-3 rounded border">
                                                                <h6 class="font-weight-bold mb-3" style="font-size: 0.9rem;">
                                                                    Editar:
                                                                    <span id="selected-elem-name" class="text-primary"></span>
                                                                </h6>

                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <div class="form-group mb-2">
                                                                            <label class="small text-muted mb-0">X (%)</label>
                                                                            <input type="number" id="style-x"
                                                                                class="form-control form-control-sm" step="0.1">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <div class="form-group mb-2">
                                                                            <label class="small text-muted mb-0">Y (%)</label>
                                                                            <input type="number" id="style-y"
                                                                                class="form-control form-control-sm" step="0.1">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="custom-control custom-switch mb-2">
                                                                    <input type="checkbox" class="custom-control-input"
                                                                        id="style-locked">
                                                                    <label class="custom-control-label"
                                                                        for="style-locked">Bloquear
                                                                        elemento</label>
                                                                </div>

                                                                <div class="form-group mb-2">
                                                                    <label class="small text-muted mb-0">Fonte</label>
                                                                    <select id="style-font-family"
                                                                        class="form-control form-control-sm">
                                                                        <option value="Arial, sans-serif">Arial</option>
                                                                        <option value="'Times New Roman', serif">Times New Roman
                                                                        </option>
                                                                        <option value="'Courier New', monospace">Courier New
                                                                        </option>
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
                                                                            <label class="small text-muted mb-0">Camada
                                                                                (Z-Index)</label>
                                                                            <input type="number" id="style-z-index"
                                                                                class="form-control form-control-sm" value="10"
                                                                                step="1">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-12">
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
                                                                    <select id="style-font-weight"
                                                                        class="form-control form-control-sm">
                                                                        <option value="normal">Normal</option>
                                                                        <option value="bold">Negrito</option>
                                                                    </select>
                                                                </div>
                                                            </div>

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

                        <div class="row" id="lessonPreviewConfig" style="display:none;">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipo de Gratuidade</label>
                                    <select name="free_preview_mode" id="lessonPreviewMode" class="form-control">
                                        <option value="full">Aula inteira gratuita</option>
                                        <option value="time">Tempo limitado (prévia parcial)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" id="lessonPreviewSecondsGroup" style="display:none;">
                                <div class="form-group">
                                    <label>Tempo gratuito (segundos)</label>
                                    <input type="number" name="free_preview_seconds" id="lessonPreviewSeconds"
                                        class="form-control" min="1" step="1" placeholder="Ex: 180">
                                    <small class="text-muted">Defina quantos segundos ficarão liberados nesta aula.</small>
                                </div>
                            </div>
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
        const aulaStoreUrlAdmin = @json($course->exists ? route('courses.lessons.store', $course) : null);
        const aulaBaseUrlAdmin = aulaStoreUrlAdmin;
        const aulaImagemConteudoUrlAdmin = @json($course->exists ? route('admin.courses.lessons.content-image', $course) : null);

            function enviarImagemConteudoAula(file, $editor) {
                if (!aulaImagemConteudoUrlAdmin) {
                    toastr.warning('Salve o curso primeiro para enviar imagens no conteúdo.');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('image', file);

                $.ajax({
                    url: aulaImagemConteudoUrlAdmin,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).done(function (response) {
                    if (response && response.url) {
                        $editor.summernote('insertImage', response.url);
                        return;
                    }

                    toastr.error('Nao foi possivel inserir a imagem no conteúdo.');
                }).fail(function (xhr) {
                    const mensagem = xhr?.responseJSON?.message || 'Falha ao enviar imagem do conteúdo.';
                    toastr.error(mensagem);
                });
            }

            function initCourseSummernoteEditors(tryCount = 0) {
                if (!(window.jQuery && $.fn && $.fn.summernote)) {
                    if (tryCount < 25) {
                        setTimeout(function () {
                            initCourseSummernoteEditors(tryCount + 1);
                        }, 180);
                    }
                    return;
                }

                const fullDescription = $('#fullDescription');
                if (fullDescription.length && !fullDescription.next('.note-editor').length) {
                    fullDescription.summernote({
                        height: 260,
                        lang: 'pt-BR',
                        placeholder: 'Detalhe o conteudo do curso...',
                        callbacks: {
                            onImageUpload: function (files) {
                                if (!files || !files.length) return;
                                Array.from(files).forEach((file) => enviarImagemConteudoAula(file, fullDescription));
                            }
                        }
                    });
                }

                const lessonContent = $('#lessonContent');
                if (lessonContent.length && !lessonContent.next('.note-editor').length) {
                    lessonContent.summernote({
                        height: 150,
                        lang: 'pt-BR',
                        toolbar: [
                            ['style', ['bold', 'italic', 'underline', 'clear']],
                            ['font', ['strikethrough', 'superscript', 'subscript']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['insert', ['link', 'picture', 'video']],
                            ['view', ['fullscreen', 'codeview']]
                        ],
                        callbacks: {
                            onImageUpload: function (files) {
                                if (!files || !files.length) return;
                                Array.from(files).forEach((file) => enviarImagemConteudoAula(file, lessonContent));
                            }
                        }
                    });
                }
            }

            function toggleLessonPreviewConfig() {
                const isPreview = $('#lessonPreview').is(':checked');
                const mode = $('#lessonPreviewMode').val() || 'full';
                const isTimeMode = isPreview && mode === 'time';

                $('#lessonPreviewConfig').toggle(isPreview);
                $('#lessonPreviewSecondsGroup').toggle(isTimeMode);
                $('#lessonPreviewSeconds').prop('required', isTimeMode);
            }

            $(document).ready(function () {
                $('#video_floating_enabled').on('change', function () {
                    $('#video_floating_size_group').toggle(this.checked);
                });
                $('#lessonPreview, #lessonPreviewMode').on('change', toggleLessonPreviewConfig);
                toggleLessonPreviewConfig();
                initCourseSummernoteEditors();

                const $lessonList = $('#lessons-list');
                @if($course->exists)
                    if ($lessonList.length && $.fn.sortable) {
                        $lessonList.sortable({
                            items: '.lesson-item',
                            handle: '.fa-grip-vertical',
                            axis: 'y',
                            tolerance: 'pointer',
                            update: function () {
                                const lessons = [];
                                $lessonList.find('.lesson-item').each(function (index) {
                                    const id = Number($(this).data('id') || 0);
                                    if (!id) return;
                                    lessons.push({
                                        id: id,
                                        order: index + 1
                                    });
                                    $(this).find('.text-muted.mr-1').first().text('#' + (index + 1));
                                });

                                if (!lessons.length) return;

                                $.ajax({
                                    url: '{{ route('admin.courses.lessons.reorder', $course) }}',
                                    method: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        lessons: lessons
                                    }
                                }).done(function () {
                                    toastr.success('Ordem das aulas atualizada.');
                                }).fail(function (xhr) {
                                    toastr.error(xhr?.responseJSON?.message || 'Falha ao atualizar a ordem das aulas.');
                                });
                            }
                        });
                    }
                @endif
            });

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
                if (window.jQuery && $.fn && $.fn.summernote && $('#lessonContent').next('.note-editor').length) {
                    $('#lessonContent').summernote('reset');
                } else {
                    $('#lessonContent').val('');
                }

                $('#lessonPreview').prop('checked', false);
                $('#lessonPreviewMode').val('full');
                $('#lessonPreviewSeconds').val('');
                toggleLessonPreviewConfig();

                if (lesson) {
                    // Edit Mode
                    $('#lessonModalTitle').text('Editar Aula');
                    $('#lessonId').val(lesson.id);
                    $('#lessonMethod').val('PUT');
                    $('#lessonTitle').val(lesson.title);
                    $('#lessonOrder').val(lesson.order);
                    // Video Logic
                    const videoUrl = lesson.video_url || '';
                    const videoInternoProtegido = !!lesson.video_has_upload;
                    if (videoInternoProtegido || videoUrl.includes('storage/') || videoUrl.includes('course-videos/')) {
                        // Local File
                        $('#lessonVideo').val(''); // Clear URL field
                        const statusHls = (lesson.video_transcode_status || '') === 'ready'
                            ? 'Arquivo atual: video interno protegido (HLS ativo)'
                            : 'Arquivo atual: video interno protegido (processando)';
                        const fileName = videoUrl ? videoUrl.split('/').pop() : '';
                        $('#lessonVideoFileLabel').text(fileName ? ('Arquivo atual: ' + fileName) : statusHls);

                        // Switch tab to file
                        $('#pills-file-tab').tab('show');
                    } else {
                        // External URL
                        $('#lessonVideo').val(videoUrl);
                        $('#lessonVideoFileLabel').text('Escolher vídeo...');
                        $('#pills-url-tab').tab('show');
                    }

                    // Permite ajuste da fonte de video mesmo na edicao da aula
                    $('#lessonVideo').prop('disabled', false);
                    $('#lessonVideoFile').prop('disabled', false);
                    $('#video-source-tab a').removeClass('disabled');

                    // Set Content
                    if (window.jQuery && $.fn && $.fn.summernote && $('#lessonContent').next('.note-editor').length) {
                        $('#lessonContent').summernote('code', lesson.content || '');
                    } else {
                        $('#lessonContent').val(lesson.content || '');
                    }

                    $('#lessonDuration').val(lesson.duration);
                    $('#lessonPreview').prop('checked', !!lesson.is_free_preview);
                    $('#lessonPreviewMode').val(lesson.free_preview_mode || 'full');
                    $('#lessonPreviewSeconds').val(lesson.free_preview_seconds || '');
                    toggleLessonPreviewConfig();
                    if ((!lesson.duration || Number(lesson.duration) <= 0) && videoUrl) {
                        agendarDeteccaoDuracaoUrl(false);
                    }

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
                    initCourseSummernoteEditors();
                }, 200);
            }

            function updateDropzoneUrl(lessonId) {
                if (myDropzone && aulaBaseUrlAdmin) {
                    myDropzone.options.url = aulaBaseUrlAdmin + '/' + lessonId + '/attachments';
                }
            }

            function fetchLessonDetails(lessonId) {
                if (!aulaBaseUrlAdmin) {
                    return;
                }

                $.get(aulaBaseUrlAdmin + '/' + lessonId + '/details', function (data) {
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
                            url: aulaBaseUrlAdmin + '/' + lessonId + '/attachments/' + attId,
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
                            url: aulaBaseUrlAdmin + '/' + lessonId + '/attachments/' + attId,
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
                window.abrirEdicaoAula = function (id) {
                    const lessonId = Number(id || 0);
                    if (!lessonId || !aulaBaseUrlAdmin) return;

                    $.get(aulaBaseUrlAdmin + '/' + lessonId + '/details', function (data) {
                        openLessonModal(data);
                    }).fail(function (xhr) {
                        let mensagem = 'Não foi possível carregar os dados da aula para edição.';
                        if (xhr && xhr.status === 403) {
                            mensagem = 'Você não tem permissão para editar esta aula.';
                        } else if (xhr && xhr.status === 404) {
                            mensagem = 'A aula não foi encontrada.';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao editar aula',
                            text: mensagem
                        });
                    });
                };

                $(document).on('click', '.btn-edit-lesson', function (e) {
                    e.preventDefault();
                    abrirEdicaoAula($(this).data('id'));
                });

                let promessaApiYoutube = null;
                let temporizadorDeteccaoDuracaoUrl = null;

                function extrairIdYoutube(url) {
                    const valor = String(url || '').trim();
                    if (!valor) return null;

                    try {
                        const normalizado = /^(https?:)?\/\//i.test(valor) ? valor : ('https://' + valor.replace(/^\/+/, ''));
                        const parsed = new URL(normalizado);
                        const host = String(parsed.hostname || '').toLowerCase();
                        const pathParts = String(parsed.pathname || '').split('/').filter(Boolean);

                        if (host === 'youtu.be' && pathParts[0]) return pathParts[0];

                        if (host.endsWith('youtube.com') || host.endsWith('youtube-nocookie.com')) {
                            if (pathParts.length >= 2 && ['embed', 'shorts', 'live'].includes(pathParts[0])) {
                                return pathParts[1];
                            }
                            const v = parsed.searchParams.get('v');
                            if (v) return v;
                            const vi = parsed.searchParams.get('vi');
                            if (vi) return vi;
                        }
                    } catch (e) { }

                    const match = valor.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?.*v=|embed\/|shorts\/|live\/)|youtube-nocookie\.com\/embed\/)([^?&#/]+)/i);
                    return match && match[1] ? match[1] : null;
                }

                function carregarApiYoutube() {
                    if (window.YT && typeof window.YT.Player === 'function') {
                        return Promise.resolve(window.YT);
                    }

                    if (promessaApiYoutube) {
                        return promessaApiYoutube;
                    }

                    promessaApiYoutube = new Promise((resolve, reject) => {
                        const anterior = window.onYouTubeIframeAPIReady;
                        window.onYouTubeIframeAPIReady = function () {
                            if (typeof anterior === 'function') {
                                try { anterior(); } catch (e) { }
                            }
                            resolve(window.YT);
                        };

                        if (!document.querySelector('script[data-youtube-iframe-api="1"]')) {
                            const script = document.createElement('script');
                            script.src = 'https://www.youtube.com/iframe_api';
                            script.async = true;
                            script.setAttribute('data-youtube-iframe-api', '1');
                            script.onerror = () => reject(new Error('Falha ao carregar API do YouTube.'));
                            document.head.appendChild(script);
                        }

                        setTimeout(() => {
                            if (!(window.YT && typeof window.YT.Player === 'function')) {
                                reject(new Error('Tempo esgotado ao carregar API do YouTube.'));
                            }
                        }, 12000);
                    });

                    return promessaApiYoutube;
                }

                function detectarDuracaoYoutube(videoId) {
                    return carregarApiYoutube().then(() => new Promise((resolve, reject) => {
                        const probeId = 'yt-duration-probe-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
                        const probe = document.createElement('div');
                        probe.id = probeId;
                        probe.style.cssText = 'position:fixed;left:-99999px;top:-99999px;width:1px;height:1px;opacity:0;pointer-events:none;';
                        document.body.appendChild(probe);

                        let player = null;
                        let finalizado = false;

                        const finalizar = (duracao = 0, erro = null) => {
                            if (finalizado) return;
                            finalizado = true;

                            if (player && typeof player.destroy === 'function') {
                                try { player.destroy(); } catch (e) { }
                            }
                            if (probe && probe.parentNode) {
                                probe.parentNode.removeChild(probe);
                            }

                            if (erro) {
                                reject(erro);
                                return;
                            }

                            if (duracao > 0) {
                                resolve(duracao);
                            } else {
                                reject(new Error('Não foi possível detectar a duração do vídeo.'));
                            }
                        };

                        const timeout = setTimeout(() => finalizar(0, new Error('Tempo esgotado ao detectar duracao do YouTube.')), 18000);

                        player = new window.YT.Player(probeId, {
                            videoId: videoId,
                            width: '1',
                            height: '1',
                            playerVars: { autoplay: 0, controls: 0, rel: 0, modestbranding: 1, playsinline: 1 },
                            events: {
                                onReady: function (event) {
                                    const inicio = Date.now();
                                    const tentar = function () {
                                        const duracao = Math.floor(Number(event.target.getDuration()) || 0);
                                        if (duracao > 0) {
                                            clearTimeout(timeout);
                                            finalizar(duracao);
                                            return;
                                        }

                                        if ((Date.now() - inicio) >= 15000) {
                                            clearTimeout(timeout);
                                            finalizar(0, new Error('Nao foi possivel detectar a duracao do YouTube.'));
                                            return;
                                        }

                                        setTimeout(tentar, 400);
                                    };

                                    tentar();
                                },
                                onError: function () {
                                    clearTimeout(timeout);
                                    finalizar(0, new Error('Video do YouTube indisponivel para previa.'));
                                }
                            }
                        });
                    }));
                }

                function detectarDuracaoVideoRemoto(url) {
                    return new Promise((resolve, reject) => {
                        const probe = document.createElement('video');
                        probe.preload = 'metadata';
                        probe.muted = true;
                        probe.style.display = 'none';

                        const finalizar = (duracao = 0, erro = null) => {
                            try {
                                probe.pause();
                                probe.removeAttribute('src');
                                probe.load();
                            } catch (e) { }

                            if (probe.parentNode) {
                                probe.parentNode.removeChild(probe);
                            }

                            if (erro) {
                                reject(erro);
                                return;
                            }

                            if (duracao > 0) {
                                resolve(duracao);
                            } else {
                                reject(new Error('Não foi possível detectar a duração.'));
                            }
                        };

                        probe.onloadedmetadata = function () {
                            const duracao = Math.floor(Number(probe.duration) || 0);
                            finalizar(duracao);
                        };

                        probe.onerror = function () {
                            finalizar(0, new Error('Falha ao carregar metadados do vídeo.'));
                        };

                        document.body.appendChild(probe);
                        probe.src = url;
                    });
                }

                function detectarDuracaoPorUrlAula(mostrarErro = false) {
                    const rawUrl = ($('#lessonVideo').val() || '').trim();
                    if (!rawUrl) return;

                    const normalizada = /^(https?:)?\/\//i.test(rawUrl) ? rawUrl : ('https://' + rawUrl.replace(/^\/+/, ''));
                    const idYoutube = extrairIdYoutube(normalizada);

                    const promessa = idYoutube
                        ? detectarDuracaoYoutube(idYoutube)
                        : detectarDuracaoVideoRemoto(normalizada);

                    promessa.then((duracao) => {
                        if (!Number.isFinite(duracao) || duracao <= 0) return;
                        $('#lessonDuration').val(duracao);
                        toastr.info('Duração detectada automaticamente: ' + duracao + 's');
                    }).catch(() => {
                        if (mostrarErro) {
                            toastr.warning('Não foi possível detectar a duração automática para este link.');
                        }
                    });
                }

                function agendarDeteccaoDuracaoUrl(mostrarErro = false) {
                    if (temporizadorDeteccaoDuracaoUrl) {
                        clearTimeout(temporizadorDeteccaoDuracaoUrl);
                    }
                    temporizadorDeteccaoDuracaoUrl = setTimeout(() => {
                        detectarDuracaoPorUrlAula(mostrarErro);
                    }, 650);
                }

                $('#lessonVideo').on('change blur', function () {
                    agendarDeteccaoDuracaoUrl(false);
                });

                // Detect Duration for Local File
                $('#lessonVideoFile').change(function (e) {
                    var file = e.target.files[0];
                    if (file) {
                        const limiteMb = {{ (int) config('uploads.video_max_mb', 1024) }};
                        const limiteBytes = limiteMb * 1024 * 1024;
                        if (file.size > limiteBytes) {
                            toastr.error('O video excede o limite permitido de ' + limiteMb + 'MB.');
                            e.target.value = '';
                            $('#lessonVideoFileLabel').text('Escolher video...');
                            return;
                        }

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
                    let url = aulaStoreUrlAdmin;
                    if (!url) {
                        toastr.error('Salve o curso primeiro para cadastrar aulas.');
                        $btn.prop('disabled', false).text(originalText);
                        return;
                    }
                    if (id) url += '/' + id;

                    // Sync Summernote content manually to ensure it's captured
                    var content = '';
                    if (window.jQuery && $.fn && $.fn.summernote && $('#lessonContent').next('.note-editor').length) {
                        content = $('#lessonContent').summernote('code');
                    } else {
                        content = $('#lessonContent').val() || '';
                    }

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
                        timeout: 900000,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
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
                            if (response && response.success === false) {
                                toastr.error(response.message || 'Falha ao salvar a aula.');
                                return;
                            }

                            toastr.success((response && response.message) ? response.message : 'Aula salva com sucesso!');

                            // Close modal and reload page immediately usually creates a jarring effect
                            // Better interaction: Close modal, then reload.
                            $('#lessonModal').modal('hide');
                            setTimeout(function () {
                                window.location.reload();
                            }, 500); // 0.5s delay to see success
                        },
                        error: function (xhr) {
                            if (xhr && xhr.statusText === 'timeout') {
                                toastr.error('Tempo limite excedido no upload. Tente um arquivo menor.');
                                return;
                            }

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
                        },
                        complete: function () {
                            $('#uploadProgressWrapper').hide();
                            $btn.prop('disabled', false).text(originalText);
                        }
                    });
                });

                // Ajax Delete Lesson
                $('.ajax-delete').on('submit', function (e) {
                    e.preventDefault();
                    const form = $(this);

                    Swal.fire({
                        title: 'Excluir aula?',
                        text: "Esta ação não poderá ser desfeita!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sim, excluir!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.post(form.attr('action'), form.serialize(), function () {
                                Swal.fire({
                                    title: 'Excluído!',
                                    text: 'A aula foi removida com sucesso.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            }).fail(function () {
                                Swal.fire('Erro!', 'Não foi possível excluir a aula.', 'error');
                            });
                        }
                    });
                });

                // Initialize Tooltips
                $('[data-toggle="tooltip"]').tooltip();

                // Image Preview Logic
                $('#courseThumbnail').on('change', function (event) {
                    var file = event.target.files[0];
                    if (file) {
                        // Update Label
                        $('#courseThumbnailFilename').text(file.name);

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

                // Thumbnail drag-and-drop
                const thumbInput = document.getElementById('courseThumbnail');
                const thumbDropzone = document.getElementById('courseThumbnailDropzone');
                const setThumbFile = function (file) {
                    if (!thumbInput || !file) return;
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    thumbInput.files = dt.files;
                    $(thumbInput).trigger('change');
                };

                if (thumbDropzone && thumbInput) {
                    const preventDefaults = function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    };

                    ['dragenter', 'dragover', 'dragleave', 'dragend', 'drop'].forEach(function (name) {
                        thumbDropzone.addEventListener(name, preventDefaults);
                    });

                    ['dragenter', 'dragover'].forEach(function (name) {
                        thumbDropzone.addEventListener(name, function () {
                            thumbDropzone.classList.add('is-dragover');
                        });
                    });

                    ['dragleave', 'dragend', 'drop'].forEach(function (name) {
                        thumbDropzone.addEventListener(name, function () {
                            thumbDropzone.classList.remove('is-dragover');
                        });
                    });

                    thumbDropzone.addEventListener('click', function () {
                        thumbInput.click();
                    });

                    thumbDropzone.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            thumbInput.click();
                        }
                    });

                    thumbDropzone.addEventListener('drop', function (e) {
                        const files = e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files : null;
                        if (!files || !files.length) return;

                        const file = files[0];
                        const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                        if (allowed.indexOf((file.type || '').toLowerCase()) === -1) {
                            toastr.error('Use apenas imagens JPG, JPEG, PNG ou WEBP.');
                            return;
                        }

                        setThumbFile(file);
                        toastr.success('Capa selecionada com arrasta e solta.');
                    });
                }

                // Signature Preview Function
                function previewSignature(input) {
                    if (input.files && input.files[0]) {
                        // Update label
                        $(input).next('.custom-file-label').html(input.files[0].name);

                        // Show preview
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            $('#signaturePreview').attr('src', e.target.result);
                            $('#signaturePreviewWrapper').show();
                        };
                        reader.readAsDataURL(input.files[0]);
                    }
                }

                // Certificate Background Preview
                function previewCertBg(input) {
                    if (input.files && input.files[0]) {
                        $(input).next('.custom-file-label').html(input.files[0].name);
                        // Optionally, you could preview the background on the certificate canvas
                        // For now, just update the label
                    }
                }

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

                // Initial Settings from DB (legacy map or schema v2 document)
                let rawCertSettings = {!! $course->certificate_settings ? json_encode($course->certificate_settings) : '{}' !!};

                const isSchemaV2 = rawCertSettings && rawCertSettings.schemaVersion === 2 && rawCertSettings.elements;
                let certDoc = isSchemaV2 ? rawCertSettings : { schemaVersion: 2, meta: {}, elements: {} };
                certDoc.meta = certDoc.meta || {};
                certDoc.elements = certDoc.elements || {};
                certDoc.schemaVersion = 2;

                // Legacy meta extraction (v1)
                if (!isSchemaV2) {
                    if (rawCertSettings && typeof rawCertSettings.backgroundFit === 'string') {
                        certDoc.meta.backgroundFit = rawCertSettings.backgroundFit;
                    }
                    if (rawCertSettings && typeof rawCertSettings.custom_title === 'string') {
                        certDoc.meta.titleText = rawCertSettings.custom_title;
                    }
                    if (rawCertSettings && typeof rawCertSettings.title === 'string') {
                        certDoc.meta.titleText = rawCertSettings.title;
                    }
                    if (rawCertSettings && typeof rawCertSettings.custom_presentation_text === 'string') {
                        certDoc.meta.presentationText = rawCertSettings.custom_presentation_text;
                    }
                    if (rawCertSettings && typeof rawCertSettings.presentation_text === 'string') {
                        certDoc.meta.presentationText = rawCertSettings.presentation_text;
                    }

                    // Copy legacy elements (arrays with x/y)
                    if (rawCertSettings && typeof rawCertSettings === 'object') {
                        Object.keys(rawCertSettings).forEach((k) => {
                            const v = rawCertSettings[k];
                            if (v && typeof v === 'object' && v.x !== undefined && v.y !== undefined) {
                                certDoc.elements[k] = v;
                            }
                        });
                    }
                }

                certDoc.meta.backgroundFit = certDoc.meta.backgroundFit || 'cover';

                // Working map for elements (v1-compatible)
                let certSettings = certDoc.elements;

                // Get Logo URL from PHP
                @php
                    $logoAuth = \App\Models\Setting::get('logo_auth') ?: \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
                    $logoAuthSrc = $logoAuth ? asset(ltrim($logoAuth, '/')) : asset('img/logo.svg');
                @endphp
                const platformLogoUrl = "{{ $logoAuthSrc }}";
                const instructorSignatureUrl = "{{ $course->instructor_signature ? asset($course->instructor_signature) : '' }}";
                let instructorSignaturePreviewUrl = instructorSignatureUrl;

                const initialTitleText = ($('#certificate_title').length ? ($('#certificate_title').val() || certDoc.meta.titleText) : certDoc.meta.titleText) || 'CERTIFICADO DE CONCLUSÃO';
                const initialPresentationText = ($('#presentation_text').length ? ($('#presentation_text').val() || certDoc.meta.presentationText) : certDoc.meta.presentationText) || '';

                // Default Tags (with mandatory logo and auto-populated fields)
                const defaultTags = {
                    'student_name': { x: 50, y: 40, text: '[Nome do Aluno]', fontSize: 30, color: '#000000', fontWeight: 'bold', fontFamily: 'Arial, sans-serif' },
                    'course_name': { x: 50, y: 55, text: '[Nome do Curso]', fontSize: 24, color: '#333333', fontWeight: 'bold', fontFamily: 'Arial, sans-serif' },
                    'completion_date': { x: 50, y: 65, text: 'Concluído em: 01/01/2024', fontSize: 16, color: '#555555', fontWeight: 'normal', fontFamily: 'Arial, sans-serif' },
                    'certificate_code': { x: 50, y: 85, text: 'Validação: ABC-123', fontSize: 12, color: '#999999', fontWeight: 'normal', fontFamily: 'Arial, sans-serif' },
                    'author_name': { x: 30, y: 90, text: '{{ $course->author_name ?? "Instrutor" }}', fontSize: 18, color: '#333333', fontWeight: 'bold', fontFamily: 'Arial, sans-serif', zIndex: 10 },
                    'workload_hours': { x: 70, y: 90, text: 'Carga Horária: {{ $course->total_hours }}h', fontSize: 14, color: '#666666', fontWeight: 'normal', fontFamily: 'Arial, sans-serif', zIndex: 10 },
                    'title': { x: 10, y: 18, text: initialTitleText, fontSize: 34, color: '#000000', fontWeight: 'bold', fontFamily: 'Arial, sans-serif', zIndex: 15, visible: false, multiline: true, maxWidth: 700, textAlign: 'center' },
                    'presentation_text': { x: 10, y: 28, text: initialPresentationText, fontSize: 16, color: '#333333', fontWeight: 'normal', fontFamily: 'Arial, sans-serif', zIndex: 15, visible: false, multiline: true, maxWidth: 700, textAlign: 'center' },
                    'instructor_signature': { x: 70, y: 80, text: 'Assinatura do Instrutor', fontSize: 12, color: '#6c757d', fontWeight: 'normal', fontFamily: 'Arial, sans-serif', width: 200, height: 60, zIndex: 10, visible: !!instructorSignatureUrl },
                    'platform_logo': { x: 50, y: 10, text: 'LOGO UNN', fontSize: 36, color: '#0066cc', fontWeight: 'bold', fontFamily: 'Georgia, serif', width: 120, height: 60, mandatory: true, zIndex: 20 }
                };

                @php
                    $certificateTagLabels = [
                        'student_name' => 'Nome do Aluno',
                        'course_name' => 'Nome do Curso',
                        'completion_date' => 'Data de Conclusão',
                        'certificate_code' => 'Código de Validação',
                        'author_name' => 'Nome do Autor',
                        'workload_hours' => 'Carga Horária',
                        'title' => 'Título do Certificado',
                        'presentation_text' => 'Texto de Apresentação',
                        'instructor_signature' => 'Assinatura do Instrutor',
                        'platform_logo' => 'Logo da Plataforma',
                    ];
                @endphp
                const tagLabels = @json($certificateTagLabels);

                // Merge defaults
                $.each(defaultTags, function (key, val) {
                    if (!certSettings[key]) {
                        certSettings[key] = val;
                    }
                });

                // Ensure deterministic defaults for v2 fields (without changing x/y)
                $.each(certSettings, function (key, data) {
                    if (!data || typeof data !== 'object') return;
                    if (data.visible === undefined) data.visible = true;
                    if (data.locked === undefined) data.locked = false;
                    if (data.zIndex === undefined) data.zIndex = (key === 'platform_logo') ? 20 : 10;
                });
                if (certSettings['platform_logo']) {
                    certSettings['platform_logo'].mandatory = true;
                    certSettings['platform_logo'].visible = true;
                }
                if (certSettings['instructor_signature']) {
                    if (certSettings['instructor_signature'].width === undefined) certSettings['instructor_signature'].width = 200;
                    if (certSettings['instructor_signature'].height === undefined) certSettings['instructor_signature'].height = 60;
                }

                // Keep element text in sync with meta inputs (WYSIWYG in editor)
                if (certSettings['title'] && $('#certificate_title').length) {
                    certSettings['title'].text = $('#certificate_title').val() || certSettings['title'].text || '';
                }
                if (certSettings['presentation_text'] && $('#presentation_text').length) {
                    certSettings['presentation_text'].text = $('#presentation_text').val() || certSettings['presentation_text'].text || '';
                }

                const $canvas = $('#cert-elements-layer');
                let activeElementId = null;
                let customFonts = [];
                const BASE_W = 842;
                const BASE_H = 595;

                function applyZoom(zoom) {
                    const z = Math.max(0.25, Math.min(zoom || 1, 3));
                    $('#cert-canvas').css({
                        width: (BASE_W * z) + 'px',
                        height: (BASE_H * z) + 'px'
                    });
                }

                function fitCanvas() {
                    const $wrap = $('#cert-canvas').parent();
                    const availW = $wrap.width() - 20;
                    const availH = $wrap.height() - 20;
                    const target = Math.max(0.25, Math.min(availW / BASE_W, availH / BASE_H));

                    const opts = $('#cert-zoom option').map(function () { return parseFloat($(this).val()); }).get();
                    let nearest = opts[0] || 1;
                    opts.forEach(function (v) {
                        if (Math.abs(v - target) < Math.abs(nearest - target)) nearest = v;
                    });

                    $('#cert-zoom').val(nearest.toString()).trigger('change');
                }

                $('#cert-zoom').on('change', function () {
                    applyZoom(parseFloat($(this).val()));
                });
                $('#cert-fit').on('click', function () {
                    fitCanvas();
                });
                applyZoom(parseFloat($('#cert-zoom').val()) || 1);

                function scheduleFitCanvas() {
                    setTimeout(function () {
                        const $tab = $('#certificate');
                        if ($tab.length && ($tab.hasClass('active') || $tab.hasClass('show'))) {
                            fitCanvas();
                        }
                    }, 50);
                }

                $('a[data-toggle="tab"][href="#certificate"]').on('shown.bs.tab', function () {
                    scheduleFitCanvas();
                });
                scheduleFitCanvas();

                function applyBackgroundFit() {
                    const fit = ($('#cert-bg-fit').val() || 'cover') === 'stretch' ? 'fill' : 'cover';
                    $('#cert-bg-img').css('object-fit', fit);
                }

                $('#cert-bg-fit').val((certDoc.meta && certDoc.meta.backgroundFit) ? certDoc.meta.backgroundFit : 'cover');
                $('#cert-bg-fit').on('change', function () {
                    certDoc.meta.backgroundFit = $(this).val() || 'cover';
                    applyBackgroundFit();
                });
                applyBackgroundFit();

                function updateGridOverlay() {
                    const enabled = $('#cert-grid-enabled').is(':checked');
                    const step = parseFloat($('#cert-grid-step').val()) || 5;
                    const $grid = $('#cert-grid-overlay');

                    if (!enabled) {
                        $grid.hide();
                        return;
                    }

                    $grid.show().css({
                        backgroundImage:
                            'linear-gradient(to right, rgba(0, 123, 255, 0.25) 1px, transparent 1px), ' +
                            'linear-gradient(to bottom, rgba(0, 123, 255, 0.25) 1px, transparent 1px)',
                        backgroundSize: step + '% ' + step + '%'
                    });
                }

                $('#cert-grid-enabled').on('change', updateGridOverlay);
                $('#cert-grid-step').on('change', updateGridOverlay);
                updateGridOverlay();

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


                function updateLayersList() {
                    const $list = $('#cert-layers');
                    if (!$list.length) return;

                    $list.empty();

                    const items = Object.keys(certSettings)
                        .filter((k) => certSettings[k] && typeof certSettings[k] === 'object' && certSettings[k].x !== undefined && certSettings[k].y !== undefined)
                        .map((k) => {
                            const z = (certSettings[k].zIndex !== undefined) ? parseInt(certSettings[k].zIndex) : (k === 'platform_logo' ? 20 : 10);
                            const visible = (k === 'platform_logo') ? true : (certSettings[k].visible !== false);
                            const locked = !!certSettings[k].locked;
                            return { key: k, zIndex: isNaN(z) ? 10 : z, visible, locked };
                        })
                        .sort((a, b) => (b.zIndex - a.zIndex));

                    items.forEach((item) => {
                        const label = tagLabels[item.key] || item.key;
                        const $btn = $('<button type="button">')
                            .addClass('list-group-item list-group-item-action py-1 px-2 d-flex align-items-center justify-content-between')
                            .toggleClass('active', activeElementId === item.key);

                        const $left = $('<span>').addClass('text-truncate').text(label);
                        const $right = $('<span>').addClass('d-flex align-items-center');

                        if (item.key !== 'platform_logo' && !item.visible) {
                            $right.append($('<span>').addClass('badge badge-secondary mr-1').text('Oculto'));
                        }
                        if (item.locked) {
                            $right.append($('<span>').addClass('badge badge-warning mr-1').text('Lock'));
                        }

                        $right.append($('<span>').addClass('badge badge-light border').text('z:' + item.zIndex));

                        $btn.append($left).append($right);
                        $btn.on('click', function () {
                            $('#el-' + item.key).trigger('mousedown');
                        });

                        $list.append($btn);
                    });
                }

                // Render Elements
                function renderElements() {
                    $canvas.empty();
                    $.each(certSettings, function (key, data) {
                        // Check visibility based on toggle switch
                        // Only render if we assume it's visible or handle visibility via CSS
                        if (!data || typeof data !== 'object' || data.x === undefined || data.y === undefined) return;

                        // Create Element
                        let $el = $('<div>')
                            .addClass('cert-element')
                            .attr('id', 'el-' + key)
                            .attr('data-tag', key)
                            .css({
                                position: 'absolute',
                                left: data.x + '%',
                                top: data.y + '%',
                                fontSize: (data.fontSize || 16) + 'px',
                                color: data.color || '#000000',
                                fontWeight: data.fontWeight || 'normal',
                                fontFamily: data.fontFamily || 'Arial, sans-serif',
                                cursor: data.locked ? 'not-allowed' : 'move',
                                display: (key !== 'platform_logo' && data.visible === false) ? 'none' : 'block',
                                whiteSpace: data.multiline ? 'pre-line' : 'nowrap',
                                width: (data.multiline && data.maxWidth) ? (data.maxWidth + 'px') : 'auto',
                                textAlign: data.textAlign || 'left',
                                border: '1px dashed transparent',
                                padding: '5px',
                                zIndex: data.zIndex || 10
                            });

                        if (key === 'platform_logo') {
                            $el.css({
                                width: (data.width || 120) + 'px',
                                height: (data.height || 60) + 'px',
                                padding: '0px',
                                backgroundImage: 'url("' + platformLogoUrl + '")',
                                backgroundSize: '100% 100%', // Allow full stretch to match container
                                backgroundRepeat: 'no-repeat',
                                backgroundPosition: 'center'
                            });
                            $el.text(''); // Clear text
                        } else if (key === 'instructor_signature') {
                            const w = (data.width || 200);
                            const h = (data.height || 60);
                            const url = instructorSignaturePreviewUrl || '';
                            const isHidden = (data.visible === false);
                            const showAs = isHidden ? 'none' : (url ? 'block' : 'flex');

                            $el.css({
                                width: w + 'px',
                                height: h + 'px',
                                padding: '0px',
                                backgroundImage: url ? ('url("' + url + '")') : 'none',
                                backgroundSize: 'contain',
                                backgroundRepeat: 'no-repeat',
                                backgroundPosition: 'center',
                                backgroundColor: url ? 'transparent' : '#f8f9fa',
                                color: url ? 'transparent' : '#6c757d',
                                fontSize: '12px',
                                borderColor: url ? 'transparent' : '#adb5bd',
                                display: showAs,
                                alignItems: 'center',
                                justifyContent: 'center',
                            });

                            $el.text(url ? '' : 'Assinatura (envie no painel abaixo)');
                        } else {
                            $el.text(data.text);
                        }

                        // Click to Select
                        $el.on('mousedown', function (e) {
                            $('.cert-element').css('border-color', 'transparent');
                            $(this).css('border-color', '#007bff');
                            activeElementId = key;

                            // Populate Tools
                            // For logo, we might want to hide text input or font controls?
                            // For now keep it simple.
                            $('#selected-elem-name').text(tagLabels[key] || data.text || key);
                            // If logo, switch context of tools if needed, but for now just show standard

                            $('#style-x').val(parseFloat(data.x ?? 0).toFixed(2));
                            $('#style-y').val(parseFloat(data.y ?? 0).toFixed(2));
                            $('#style-locked').prop('checked', !!data.locked);
                            $('#style-font-size').val(data.fontSize);
                            $('#style-z-index').val(data.zIndex || 10);
                            $('#style-color').val(data.color);
                            $('#style-font-weight').val(data.fontWeight);
                            $('#style-font-family').val(data.fontFamily || 'Arial, sans-serif');
                            $('#cert-style-controls').show();
                            updateLayersList();

                            e.stopPropagation();
                        });

                        $canvas.append($el);

                        // Initialize Resizable for image elements
                        if (key === 'platform_logo' || key === 'instructor_signature') {
                            $el.resizable({
                                aspectRatio: false, // User requested free resize ("quadrado x retangulo")
                                disabled: !!data.locked,
                                handles: 'n, e, s, w, ne, se, sw, nw',
                                stop: function (event, ui) {
                                    let w = ui.size.width;
                                    let h = ui.size.height;

                                    // Update Settings
                                    certSettings[key].width = w;
                                    certSettings[key].height = h;

                                    if (key === 'platform_logo') {
                                        // Update Inputs
                                        $('#logo-width').val(Math.round(w));
                                        $('#logo-height').val(Math.round(h));
                                        toastr.info('Tamanho da logo atualizado: ' + Math.round(w) + 'x' + Math.round(h));
                                    } else {
                                        toastr.info('Tamanho da assinatura atualizado: ' + Math.round(w) + 'x' + Math.round(h));
                                    }
                                }
                            });
                        }
                    });

                    // Init Draggable
                    $('.cert-element').draggable({
                        containment: false, // Allow dragging outside
                        scroll: false,
                        start: function () {
                            let key = $(this).data('tag');
                            if (certSettings[key] && certSettings[key].locked) {
                                return false;
                            }
                        },
                        stop: function (event, ui) {
                            let key = $(this).data('tag');
                            let parentW = $('#cert-canvas').width();
                            let parentH = $('#cert-canvas').height();

                            // Get current position (Top-Left corner of element)
                            let leftPx = ui.position.left;
                            let topPx = ui.position.top;

                            // Convert to % (top-left anchored)
                            let x = (leftPx / parentW) * 100;
                            let y = (topPx / parentH) * 100;

                            // Optional snap-to-grid
                            if ($('#cert-snap-enabled').is(':checked')) {
                                let step = parseFloat($('#cert-snap-step').val()) || 1;
                                x = Math.round(x / step) * step;
                                y = Math.round(y / step) * step;
                                $(this).css({ left: x + '%', top: y + '%' });
                            }

                            certSettings[key].x = x;
                            certSettings[key].y = y;

                            if (activeElementId === key) {
                                $('#style-x').val(parseFloat(x).toFixed(2));
                                $('#style-y').val(parseFloat(y).toFixed(2));
                            }

                            updateLayersList();
                        }
                    });

                    // Sync visibility toggles from persisted state
                    $('.cert-toggle').each(function () {
                        let key = $(this).data('tag');
                        if (key === 'platform_logo') {
                            $(this).prop('checked', true);
                            $('#el-platform_logo').show();
                            return;
                        }

                        const visible = certSettings[key] ? (certSettings[key].visible !== false) : true;
                        $(this).prop('checked', visible);
                    });

                    // Load logo size from settings
                    if (certSettings['platform_logo']) {
                        $('#logo-width').val(certSettings['platform_logo'].width || 120);
                        $('#logo-height').val(certSettings['platform_logo'].height || 60);
                        $('#logo-width, #logo-height').prop('disabled', !!certSettings['platform_logo'].locked);
                    }

                    updateLayersList();
                }

                renderElements();

                // Live sync: title/presentation/signature previews in the canvas
                $('#certificate_title').on('input', function () {
                    const val = $(this).val() || '';
                    certDoc.meta = certDoc.meta || {};
                    certDoc.meta.titleText = val;
                    if (certSettings['title']) {
                        certSettings['title'].text = val;
                        $('#el-title').text(val);
                    }
                });

                $('#presentation_text').on('input', function () {
                    const val = $(this).val() || '';
                    certDoc.meta = certDoc.meta || {};
                    certDoc.meta.presentationText = val;
                    if (certSettings['presentation_text']) {
                        certSettings['presentation_text'].text = val;
                        $('#el-presentation_text').text(val);
                    }
                });

                $('#instructor_signature').on('change', function () {
                    if (!this.files || !this.files[0]) return;
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        instructorSignaturePreviewUrl = e.target.result;

                        if (certSettings['instructor_signature']) {
                            certSettings['instructor_signature'].visible = true;
                        }
                        $('.cert-toggle[data-tag="instructor_signature"]').prop('checked', true);

                        const $sig = $('#el-instructor_signature');
                        if ($sig.length) {
                            const isHidden = certSettings['instructor_signature'] && certSettings['instructor_signature'].visible === false;
                            $sig.css({
                                backgroundImage: 'url("' + instructorSignaturePreviewUrl + '")',
                                backgroundSize: 'contain',
                                backgroundRepeat: 'no-repeat',
                                backgroundPosition: 'center',
                                backgroundColor: 'transparent',
                                borderColor: 'transparent',
                                color: 'transparent',
                                display: isHidden ? 'none' : 'block',
                            }).text('');
                        }

                        updateLayersList();
                    };
                    reader.readAsDataURL(this.files[0]);
                });

                // Style Change Listeners
                $('#style-font-size').on('input', function () {
                    if (activeElementId) {
                        let val = $(this).val();
                        certSettings[activeElementId].fontSize = val;
                        $('#el-' + activeElementId).css('font-size', val + 'px');
                    }
                });
                $('#style-z-index').on('input', function () {
                    if (activeElementId) {
                        let val = $(this).val();
                        certSettings[activeElementId].zIndex = val;
                        $('#el-' + activeElementId).css('z-index', val);
                        updateLayersList();
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

                $('#style-x').on('input', function () {
                    if (!activeElementId) return;
                    let val = parseFloat($(this).val());
                    if (isNaN(val)) return;
                    certSettings[activeElementId].x = val;
                    $('#el-' + activeElementId).css('left', val + '%');
                });

                $('#style-y').on('input', function () {
                    if (!activeElementId) return;
                    let val = parseFloat($(this).val());
                    if (isNaN(val)) return;
                    certSettings[activeElementId].y = val;
                    $('#el-' + activeElementId).css('top', val + '%');
                });

                $('#style-locked').on('change', function () {
                    if (!activeElementId) return;
                    const locked = $(this).is(':checked');
                    certSettings[activeElementId].locked = locked;

                    const $el = $('#el-' + activeElementId);
                    $el.css('cursor', locked ? 'not-allowed' : 'move');

                    try { locked ? $el.draggable('disable') : $el.draggable('enable'); } catch (e) { }
                    try { locked ? $el.resizable('disable') : $el.resizable('enable'); } catch (e) { }

                    if (activeElementId === 'platform_logo') {
                        $('#logo-width, #logo-height').prop('disabled', locked);
                    }

                    updateLayersList();
                });

                // Keyboard nudging (arrow keys) for precise movement
                function clampPercent(val) {
                    return Math.max(0, Math.min(100, val));
                }

                function nudgeSelected(dx, dy) {
                    if (!activeElementId) return;
                    const data = certSettings[activeElementId];
                    if (!data || data.locked) return;

                    let x = parseFloat(data.x);
                    let y = parseFloat(data.y);
                    if (isNaN(x)) x = 0;
                    if (isNaN(y)) y = 0;

                    x = clampPercent(x + dx);
                    y = clampPercent(y + dy);

                    if ($('#cert-snap-enabled').is(':checked')) {
                        const snap = parseFloat($('#cert-snap-step').val()) || 1;
                        x = Math.round(x / snap) * snap;
                        y = Math.round(y / snap) * snap;
                    }

                    x = Math.round(x * 10000) / 10000;
                    y = Math.round(y * 10000) / 10000;

                    data.x = x;
                    data.y = y;

                    $('#el-' + activeElementId).css({ left: x + '%', top: y + '%' });
                    $('#style-x').val(parseFloat(x).toFixed(2));
                    $('#style-y').val(parseFloat(y).toFixed(2));
                }

                $(document).on('keydown.certNudge', function (e) {
                    if (!activeElementId) return;
                    if (!$('#certificate').hasClass('show')) return;

                    const $target = $(e.target);
                    if (
                        $target.is('input, textarea, select') ||
                        $target.closest('input, textarea, select').length ||
                        $target.is('[contenteditable=true]') ||
                        $target.closest('[contenteditable=true]').length
                    ) {
                        return;
                    }

                    if (e.ctrlKey || e.metaKey || e.altKey) return;

                    let step = parseFloat($('#cert-nudge-step').val());
                    if (isNaN(step) || step <= 0) step = 0.5;
                    if (e.shiftKey) step = step * 5;

                    const key = e.key || '';
                    const code = e.which || e.keyCode;

                    let dx = 0, dy = 0;
                    if (key === 'ArrowLeft' || code === 37) dx = -step;
                    else if (key === 'ArrowRight' || code === 39) dx = step;
                    else if (key === 'ArrowUp' || code === 38) dy = -step;
                    else if (key === 'ArrowDown' || code === 40) dy = step;
                    else return;

                    e.preventDefault();
                    nudgeSelected(dx, dy);
                });

                // Logo Size Controls
                $('#logo-width').on('input', function () {
                    let val = parseInt($(this).val()) || 120;
                    // Clamp between min and max
                    val = Math.max(50, Math.min(400, val));
                    if (certSettings['platform_logo'] && certSettings['platform_logo'].locked) {
                        $(this).val(certSettings['platform_logo'].width || 120);
                        return;
                    }
                    certSettings['platform_logo'].width = val;
                    $('#el-platform_logo').css('width', val + 'px');
                });

                $('#logo-height').on('input', function () {
                    let val = parseInt($(this).val()) || 60;
                    // Clamp between min and max
                    val = Math.max(30, Math.min(200, val));
                    if (certSettings['platform_logo'] && certSettings['platform_logo'].locked) {
                        $(this).val(certSettings['platform_logo'].height || 60);
                        return;
                    }
                    certSettings['platform_logo'].height = val;
                    $('#el-platform_logo').css('height', val + 'px');
                });

                // Toggle Visibility (logo is MANDATORY and cannot be hidden)
                $('.cert-toggle').on('change', function () {
                    let key = $(this).data('tag');

                    // Prevent logo from being hidden
                    if (key === 'platform_logo') {
                        $(this).prop('checked', true); // Force checked
                        toastr.warning('A logo da plataforma é obrigatória e não pode ser removida.');
                        return;
                    }

                    if ($(this).is(':checked')) {
                        certSettings[key].visible = true;

                        if (key === 'instructor_signature') {
                            const hasUrl = !!(instructorSignaturePreviewUrl || '');
                            $('#el-' + key).css('display', hasUrl ? 'block' : 'flex');
                        } else {
                            $('#el-' + key).show();
                        }
                    } else {
                        certSettings[key].visible = false;
                        $('#el-' + key).hide();
                    }

                    updateLayersList();
                });

                // Sync Settings on Submit (AJAX)
                $('#certForm').on('submit', function (e) {
                    e.preventDefault();

                    certDoc.meta = certDoc.meta || {};
                    certDoc.meta.backgroundFit = $('#cert-bg-fit').val() || 'cover';
                    certDoc.meta.titleText = $('#certificate_title').val() || '';
                    certDoc.meta.presentationText = $('#presentation_text').val() || '';
                    if (certSettings['platform_logo']) {
                        certSettings['platform_logo'].visible = true;
                        certSettings['platform_logo'].mandatory = true;
                    }

                    $('#certificate_settings_input').val(JSON.stringify(certDoc));

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
                    try {
                        $(input).next('.custom-file-label').html(input.files[0].name);
                    } catch (e) { }

                    var reader = new FileReader();
                    reader.onload = function (e) {
                        if ($('#cert-bg-img').length) {
                            $('#cert-bg-img').attr('src', e.target.result);
                        } else {
                            $('#cert-bg-placeholder').replaceWith('<img src="' + e.target.result + '" id="cert-bg-img" style="width: 100%; height: 100%; object-fit: cover; position: absolute; z-index: 1;">');
                        }

                        const fit = ($('#cert-bg-fit').val() || 'cover') === 'stretch' ? 'fill' : 'cover';
                        $('#cert-bg-img').css('object-fit', fit);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>
@endpush
