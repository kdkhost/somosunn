@extends('admin.layouts.app')

@section('page_title', $course->exists ? 'Editar Curso' : 'Novo Curso')
@section('breadcrumb_items')
    <li class="breadcrumb-item"><a href="{{ route('admin.courses.index') }}">Cursos</a></li>
    <li class="breadcrumb-item active">{{ $course->exists ? 'Editar' : 'Novo' }}</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<style>
    .nav-tabs .nav-link { font-weight: 500; }
    .lesson-item { border: 1px solid #ddd; padding: 10px 15px; background: #fff; margin-bottom: 5px; border-radius: 5px; display: flex; align-items: center; justify-content: space-between; }
    .lesson-item:hover { background: #f9f9f9; }
    .dropzone { border: 2px dashed #0087F7; border-radius: 5px; background: white; min-height: 150px; padding: 20px; }
    .attachment-list { list-style: none; padding: 0; margin-top: 15px; }
    .attachment-item { display: flex; align-items: center; justify-content: space-between; padding: 8px; border-bottom: 1px solid #eee; }
    .attachment-item:last-child { border-bottom: none; }
    .attachment-icon { margin-right: 10px; font-size: 1.2em; color: #666; }
    .swal2-container { z-index: 2000 !important; }
</style>
@endpush

@section('content')
<div class="card card-primary card-outline card-outline-tabs">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="courseTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="info-tab" data-toggle="pill" href="#info" role="tab">Informações Básicas</a>
            </li>
            <li class="nav-item">
                @if($course->exists)
                    <a class="nav-link" id="lessons-tab" data-toggle="pill" href="#lessons" role="tab">Conteúdo / Aulas</a>
                @else
                    <a class="nav-link disabled" href="#" title="Salve o curso primeiro">Conteúdo / Aulas <i class="fas fa-lock ml-1 text-muted"></i></a>
                @endif
            </li>
            <li class="nav-item">
                @if($course->exists)
                    <a class="nav-link" id="cert-tab" data-toggle="pill" href="#certificate" role="tab">Certificado</a>
                @else
                    <a class="nav-link disabled" href="#" title="Salve o curso primeiro">Certificado <i class="fas fa-lock ml-1 text-muted"></i></a>
                @endif
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="courseTabsContent">
            <!-- INFO TAB -->
            <div class="tab-pane fade show active" id="info" role="tabpanel">
                <form method="POST" action="{{ $course->exists ? route('admin.courses.update',$course) : route('admin.courses.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if($course->exists) @method('PUT') @endif
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label>Título do Curso</label>
                                <input name="title" class="form-control form-control-lg" value="{{ old('title',$course->title) }}" required placeholder="Ex: Curso Completo de Laravel">
                            </div>
                            <div class="form-group mb-3">
                                <label>Descrição Curta (Resumo)</label>
                                <textarea name="short_description" class="form-control" rows="3" placeholder="Breve resumo para exibição nos cards..." maxlength="500">{{ old('short_description',$course->short_description) }}</textarea>
                                <small class="text-muted">Máximo 500 caracteres.</small>
                            </div>
                            <div class="form-group mb-3">
                                <label>Descrição Completa</label>
                                <textarea name="full_description" id="fullDescription" class="form-control summernote">{{ old('full_description',$course->full_description) }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-header">Imagem de Capa</div>
                                <div class="card-body text-center">
                                    @if($course->thumbnail)
                                        <img src="{{ asset($course->thumbnail) }}" class="img-fluid rounded mb-2" style="max-height: 150px;">
                                    @else
                                        <div class="bg-secondary rounded mb-2 d-flex align-items-center justify-content-center" style="height: 150px;">
                                            <i class="fas fa-image fa-3x text-white"></i>
                                        </div>
                                    @endif
                                    <div class="custom-file text-left">
                                        <input type="file" name="thumbnail" class="custom-file-input" id="courseThumbnail" accept="image/png, image/jpeg, image/jpg">
                                        <label class="custom-file-label" for="courseThumbnail" data-browse="Buscar">Escolher arquivo</label>
                                    </div>
                                    <small class="text-muted d-block mt-1">Recomendado: 1280x720px (JPG/PNG)</small>
                                    
                                    <div class="progress mt-2" id="thumbnailProgressWrapper" style="display:none; height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" id="thumbnailProgressBar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label>Preço (R$)</label>
                                        <input name="price" class="form-control mask-money" value="{{ old('price',$course->price) }}" placeholder="0,00">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Autor</label>
                                        <input name="author_name" class="form-control" value="{{ old('author_name',$course->author_name ?? Auth::user()->name) }}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="draft" {{ $course->status == 'draft' ? 'selected' : '' }}>Rascunho</option>
                                            <option value="published" {{ $course->status == 'published' ? 'selected' : '' }}>Publicado</option>
                                            <option value="archived" {{ $course->status == 'archived' ? 'selected' : '' }}>Arquivado</option>
                                            <option value="paused" {{ $course->status == 'paused' ? 'selected' : '' }}>Vendas Pausadas</option>
                                        </select>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="is_featured" {{ $course->is_featured ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_featured">Destaque na Home</label>
                                    </div>

                                </div>
                            </div>

                            <!-- CONFIGURAÇÕES EXTRAS -->
                            <div class="card bg-light mb-3">
                                <div class="card-header">Configurações</div>
                                <div class="card-body">
                                    
                                    <h6 class="font-weight-bold">Certificado</h6>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="is_certificate_enabled" value="1" class="form-check-input" id="is_certificate_enabled" {{ $course->is_certificate_enabled ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_certificate_enabled">Habilitar Certificado</label>
                                    </div>
                                    <small class="text-muted d-block mb-3">O aluno receberá um certificado automático ao concluir 100% das aulas.</small>

                                    @if($course->exists && $course->slug)
                                        <hr>
                                        <h6 class="font-weight-bold">Link do Curso</h6>
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" id="courseLink" value="{{ route('courses.show', $course->slug) }}" readonly>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyLink()">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <a href="{{ route('courses.show', $course->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary btn-block">
                                            <i class="fas fa-external-link-alt"></i> Visualizar Página
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                            <button class="btn btn-primary btn-block btn-lg" data-toggle="tooltip" title="Salvar todas as alterações do curso">Salvar Informações</button>
                            
                            @if(!$course->exists)
                            <p class="text-muted text-center mt-3 small">
                                <i class="fas fa-info-circle"></i> Salve para liberar a aba de Aulas.
                            </p>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- LESSONS TAB -->
            @if($course->exists)
            <div class="tab-pane fade" id="lessons" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Grade Curricular</h4>
                    <button class="btn btn-success" onclick="openLessonModal()" data-toggle="tooltip" title="Adicionar uma nova aula"><i class="fas fa-plus"></i> Nova Aula</button>
                </div>

                <div id="lessons-list">
                    @forelse($course->lessons as $lesson)
                        <div class="lesson-item" data-id="{{ $lesson->id }}">
                            <div>
                                <i class="fas fa-grip-vertical text-muted mr-2" style="cursor:move"></i>
                                <strong>{{ $lesson->order }}. {{ $lesson->title }}</strong>
                                <span class="badge badge-light ml-2">{{ gmdate("H:i:s", $lesson->duration) }}</span>
                                @if($lesson->is_free_preview) <span class="badge badge-info">Preview</span> @endif
                            </div>
                            <div>
                                <button class="btn btn-sm btn-info btn-edit-lesson" data-id="{{ $lesson->id }}" data-toggle="tooltip" title="Editar Aula"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('courses.lessons.destroy', [$course, $lesson]) }}" method="POST" class="d-inline ajax-delete">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" data-toggle="tooltip" title="Excluir Aula"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">Nenhuma aula cadastrada ainda.</div>
                    @endforelse
                </div>
            </div>
            
            <!-- CERTIFICATE TAB -->
            <div class="tab-pane fade" id="certificate" role="tabpanel">
                <form id="certForm" method="POST" action="{{ route('admin.courses.update', $course) }}" enctype="multipart/form-data">
                    @csrf 
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-9">
                            <!-- CANVAS AREA (A4 Landscape aspect ratio) -->
                            <div class="card">
                                <div class="card-body bg-secondary d-flex justify-content-center align-items-center" style="min-height: 500px; overflow: hidden;">
                                    
                                    <div id="cert-canvas" style="position: relative; width: 842px; height: 595px; background-color: white; box-shadow: 0 0 20px rgba(0,0,0,0.5); overflow: hidden;">
                                        @if($course->certificate_bg)
                                            <img src="{{ asset($course->certificate_bg) }}" id="cert-bg-img" style="width: 100%; height: 100%; object-fit: cover; position: absolute; z-index: 1;">
                                        @else
                                            <div id="cert-bg-placeholder" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #ccc; z-index: 1; position: absolute;">
                                                <h3>Sem imagem de fundo</h3>
                                            </div>
                                        @endif
                                        
                                        <!-- Draggable Container z-index 10 -->
                                        <div id="cert-elements-layer" style="position: absolute; top:0; left:0; width: 100%; height: 100%; z-index: 10;">
                                            <!-- Elements will be injected here via JS -->
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <!-- TOOLS PANEL -->
                            <div class="card">
                                <div class="card-header bg-dark text-white">Configurações</div>
                                <div class="card-body">
                                    
                                    <div class="form-group">
                                        <label>Imagem de Fundo (A4 Paisagem)</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="certificate_bg" accept="image/*" onchange="previewCertBg(this)">
                                            <label class="custom-file-label">Escolher arquivo</label>
                                        </div>
                                        <small class="text-muted">Recomendado: 1920x1080px ou tamanho proporcional A4 (3508x2480px para alta qualidade).</small>
                                    </div>

                                    <hr>

                                    <h6>Elementos Visíveis</h6>
                                    <p class="small text-muted">Arraste os elementos no canvas.</p>
                                    
                                    <div class="list-group mb-3" id="cert-available-tags">
                                        <!-- Checkboxes to toggle visibility of elements -->
                                        <div class="list-group-item p-2">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input cert-toggle" id="toggle-student" data-tag="student_name" checked>
                                                <label class="custom-control-label" for="toggle-student">Nome do Aluno</label>
                                            </div>
                                        </div>
                                        <div class="list-group-item p-2">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input cert-toggle" id="toggle-course" data-tag="course_name" checked>
                                                <label class="custom-control-label" for="toggle-course">Nome do Curso</label>
                                            </div>
                                        </div>
                                        <div class="list-group-item p-2">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input cert-toggle" id="toggle-date" data-tag="completion_date" checked>
                                                <label class="custom-control-label" for="toggle-date">Data de Conclusão</label>
                                            </div>
                                        </div>
                                        <div class="list-group-item p-2">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input cert-toggle" id="toggle-code" data-tag="certificate_code" checked>
                                                <label class="custom-control-label" for="toggle-code">Código de Validação</label>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <!-- Styling Controls for Selected Element -->
                                    <div id="cert-style-controls" style="display:none;">
                                        <h6>Estilo: <span id="selected-elem-name" class="text-primary font-weight-bold"></span></h6>
                                        <div class="form-group mb-2">
                                            <label class="small mb-1">Tamanho da Fonte (px)</label>
                                            <input type="number" id="style-font-size" class="form-control form-control-sm" value="20">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="small mb-1">Cor</label>
                                            <input type="color" id="style-color" class="form-control form-control-sm" value="#000000">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="small mb-1">Peso da Fonte</label>
                                            <select id="style-font-weight" class="form-control form-control-sm">
                                                <option value="normal">Normal</option>
                                                <option value="bold">Negrito</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="certificate_settings" id="certificate_settings_input">
                                    <button type="submit" class="btn btn-primary btn-block mt-4" id="btn-save-cert">Salvar Certificado</button>
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

<!-- Modal Aula -->
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
                                <input type="number" name="duration" id="lessonDuration" class="form-control" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Fonte do Vídeo</label>
                        <ul class="nav nav-pills mb-2" id="video-source-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="pills-url-tab" data-toggle="pill" href="#pills-url" role="tab">Link Externo (YouTube/Vimeo)</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pills-file-tab" data-toggle="pill" href="#pills-file" role="tab">Upload de Arquivo (MP4)</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="video-source-content">
                            <div class="tab-pane fade show active" id="pills-url" role="tabpanel">
                                <input name="video_url" id="lessonVideo" class="form-control" placeholder="https://...">
                            </div>
                            <div class="tab-pane fade" id="pills-file" role="tabpanel">
                                <div class="custom-file">
                                    <input type="file" name="video_file" class="custom-file-input" id="lessonVideoFile" accept="video/mp4,video/x-m4v,video/*">
                                    <label class="custom-file-label" for="lessonVideoFile" id="lessonVideoFileLabel">Escolher vídeo...</label>
                                </div>
                                <small class="text-muted">A duração será detectada automaticamente ao selecionar o arquivo.</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Conteúdo (Texto/HTML)</label>
                        <textarea name="content" id="lessonContent" class="form-control summernote" rows="3"></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_free_preview" id="lessonPreview" class="form-check-input" value="1">
                        <label class="form-check-label" for="lessonPreview">Aula Gratuita (Preview)</label>
                    </div>

                    <div class="progress mt-3" id="uploadProgressWrapper" style="display:none;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" id="uploadProgressBar" style="width: 0%">0%</div>
                    </div>

                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-primary" id="btnSaveLesson" data-toggle="tooltip" title="Salvar e fechar modal">Salvar Dados da Aula</button>
                    </div>
                </form>

                <hr>

                <!-- Attachment Section (Only for existing lessons) -->
                <div id="attachmentsSection" style="display:none;">
                    <h5><i class="fas fa-paperclip"></i> Materiais de Apoio</h5>
                    <p class="text-muted small">Arquivos para download (PDF, Doc, Zip, etc)</p>
                    
                    <div class="dropzone" id="filesDropzone">
                        <div class="dz-message" data-dz-message><span>Arraste arquivos aqui ou clique para enviar</span></div>
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
            if(lesson.is_free_preview) $('#lessonPreview').prop('checked', true);

            // Fetch details (attachments)
            fetchLessonDetails(lesson.id);

            $('#attachmentsSection').show();
            $('#attachmentsWarning').hide();
            
            // Re-init dropzone url
             updateDropzoneUrl(lesson.id);
        }

        $('#lessonModal').modal('show');
        
        // Ensure Summernote is visible/init
        setTimeout(function(){
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
        if(myDropzone) {
            myDropzone.options.url = '/courses/{{ $course->id }}/lessons/' + lessonId + '/attachments';
        }
    }

    function fetchLessonDetails(lessonId) {
        $.get('/courses/{{ $course->id }}/lessons/' + lessonId + '/details', function(data) {
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
    window.renameAttachment = function(lessonId, attId, currentName) {
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
                    success: function() { fetchLessonDetails(lessonId); toastr.success('Renomeado'); }
                });
            }
        });
    };

    window.deleteAttachment = function(lessonId, attId) {
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
                    success: function() { fetchLessonDetails(lessonId); toastr.success('Removido'); }
                });
            }
        });
    };

    // Initialize Dropzone
    let myDropzone;
    $(document).ready(function() {
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
                
                init: function() {
                    // Fix SweetAlert2 focus inside Bootstrap Modal
                    // This kills the 'enforceFocus' feature of Bootstrap which fights with SweetAlert2
                    $.fn.modal.Constructor.prototype._enforceFocus = function() {};

                    this.on("addedfile", function(file) {
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

                    this.on("sending", function(file, xhr, formData) {
                        if(file.customName) {
                            formData.append("name", file.customName);
                        }
                    });

                    this.on("success", function(file, response) {
                        if(response.success) {
                            toastr.success('Arquivo enviado!');
                            const lessonId = $('#lessonId').val();
                            fetchLessonDetails(lessonId);
                            this.removeFile(file);
                        }
                    });

                    this.on("error", function(file, message) {
                        // If manually removed (canceled), don't show error
                        if(file.accepted === false) return; 
                        
                        // Handle Dropzone error message objects or strings
                        let msg = message;
                        if(typeof message === 'object' && message.error) msg = message.error;
                        
                        toastr.error(msg || 'Erro no upload');
                        this.removeFile(file);
                    });
                }
            });
        }

        // Edit Lesson Click
        $('.btn-edit-lesson').on('click', function() {
            const id = $(this).data('id');
            // Fetch basic data from data attributes or DOM? Better fetch clean JSON.
            // Simple approach: get row data. But for edit stability, fetching details is better.
            $.get('/courses/{{ $course->id }}/lessons/' + id + '/details', function(data) {
                openLessonModal(data);
            });
        });

        // Detect Duration for Local File
        $('#lessonVideoFile').change(function(e){
            var file = e.target.files[0];
            if(file){
                $('#lessonVideoFileLabel').text(file.name);
                var video = document.createElement('video');
                video.preload = 'metadata';
                video.onloadedmetadata = function() {
                    window.URL.revokeObjectURL(video.src);
                    var duration = Math.floor(video.duration);
                    $('#lessonDuration').val(duration);
                    toastr.info('Duração detectada: ' + duration + 's');
                }
                video.src = URL.createObjectURL(file);
            }
        });

        // Save Lesson Button Click (Force Submit)
        $('#btnSaveLesson').on('click', function(e) {
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
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            $progressBar.css('width', percentComplete + '%');
                            $progressBar.text(percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $('#uploadProgressWrapper').hide();
                    $btn.prop('disabled', false).text(originalText);
                    toastr.success('Aula salva com sucesso!');

                    // Close modal and reload page immediately usually creates a jarring effect
                    // Better interaction: Close modal, then reload.
                    $('#lessonModal').modal('hide');
                    setTimeout(function(){
                        window.location.reload();
                    }, 500); // 0.5s delay to see success
                },
                error: function(xhr) {
                    $('#uploadProgressWrapper').hide();
                    $btn.prop('disabled', false).text(originalText);
                    
                    if(xhr.responseJSON && xhr.responseJSON.errors) {
                        let msg = '';
                        $.each(xhr.responseJSON.errors, function(k,v){ msg += v[0]+'<br>'; });
                        toastr.error(msg);
                    } else if(xhr.status === 413) {
                        toastr.error('O arquivo é muito grande para o servidor. Limite: 500MB.');
                    } else if(xhr.status === 403) {
                         toastr.error('Não autorizado. Verifique suas permissões.');
                    } else {
                        toastr.error('Erro ao salvar.');
                        console.error(xhr);
                    }
                }
            });
        });
        
        // Ajax Delete Lesson
        $('.ajax-delete').on('submit', function(e){
            e.preventDefault();
            if(!confirm('Excluir aula?')) return;
            $.post($(this).attr('action'), $(this).serialize(), function(){
                location.reload();
            });
        });

        // Initialize Tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Image Preview Logic
        $('#courseThumbnail').on('change', function(event) {
            var file = event.target.files[0];
            if (file) {
                // Update Label
                $(this).next('.custom-file-label').html(file.name);
                
                // Show Preview
                var reader = new FileReader();
                reader.onload = function(e) {
                    // Find or create img element
                    var container = $(event.target).closest('.card-body');
                    var img = container.find('img');
                    
                    if (img.length) {
                        img.attr('src', e.target.result);
                    } else {
                        // Create img if replacing icon
                        var wrapper = container.find('.bg-secondary');
                        if (wrapper.length) wrapper.replaceWith('<img src="'+e.target.result+'" class="img-fluid rounded mb-2" style="max-height: 150px;">');
                        else container.prepend('<img src="'+e.target.result+'" class="img-fluid rounded mb-2" style="max-height: 150px;">');
                    }
                }
                reader.readAsDataURL(file);
            }
        });

        // Main Course Form AJAX Submit (to show progress)
        $('form[enctype="multipart/form-data"]').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $btn = $form.find('button[type="submit"], button.btn-primary.btn-block');
            var originalText = $btn.text();
            
            // Check if file is selected for progress bar
            var hasFile = $('#courseThumbnail')[0].files.length > 0;
            if(hasFile) {
                $('#thumbnailProgressWrapper').show();
            }

            // FORCE SYNC SUMMERNOTE
            $('#fullDescription').val($('#fullDescription').summernote('code'));

            $btn.prop('disabled', true).text('Salvando...');

            var formData = new FormData(this);

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable && hasFile) {
                            var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            $('#thumbnailProgressBar').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    toastr.success('Curso salvo com sucesso!');
                    setTimeout(function(){
                         window.location.href = "{{ route('admin.courses.index') }}";
                    }, 500);
                },
                error: function(xhr) {
                    $('#thumbnailProgressWrapper').hide();
                    $btn.prop('disabled', false).text(originalText);
                    
                     if(xhr.responseJSON && xhr.responseJSON.errors) {
                        // Validation errors
                        let msg = '';
                        $.each(xhr.responseJSON.errors, function(k,v){ msg += v[0]+'<br>'; });
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
    $(document).ready(function() {
        // Initial Settings from DB (or defaults)
        let certSettings = {!! $course->certificate_settings ? json_encode($course->certificate_settings) : '{}' !!};
        
        // Defaults if empty
        const defaultTags = {
            'student_name': { x: 50, y: 40, text: '[Nome do Aluno]', fontSize: 30, color: '#000000', fontWeight: 'bold' },
            'course_name': { x: 50, y: 55, text: '[Nome do Curso]', fontSize: 24, color: '#333333', fontWeight: 'bold' },
            'completion_date': { x: 50, y: 65, text: 'Concluído em: 01/01/2024', fontSize: 16, color: '#555555', fontWeight: 'normal' },
            'certificate_code': { x: 50, y: 85, text: 'Validação: ABC-123', fontSize: 12, color: '#999999', fontWeight: 'normal' }
        };

        // Merge defaults
        $.each(defaultTags, function(key, val) {
            if (!certSettings[key]) {
                certSettings[key] = val;
                // Default hidden logic: if new course, maybe hide? For now show all.
            }
        });

        const $canvas = $('#cert-elements-layer');
        let activeElementId = null;

        // Render Elements
        function renderElements() {
            $canvas.empty();
            $.each(certSettings, function(key, data) {
                 // Check visibility based on toggle switch
                 if(!$('#toggle-'+key.split('_')[1]).is(':checked') && !$('#toggle-'+key.split('_')[0]).is(':checked')) {
                    // This is a rough check, let's rely on the toggle state map if we had one.
                    // Actually, let's check the DOM toggle switch
                 }
                 
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
                        cursor: 'move',
                        whiteSpace: 'nowrap',
                        border: '1px dashed transparent',
                        padding: '5px'
                    })
                    .text(data.text);
                 
                 // Click to Select
                 $el.on('mousedown', function(e) {
                     $('.cert-element').css('border-color', 'transparent');
                     $(this).css('border-color', '#007bff');
                     activeElementId = key;
                     
                     // Populate Tools
                     $('#selected-elem-name').text(data.text);
                     $('#style-font-size').val(data.fontSize);
                     $('#style-color').val(data.color);
                     $('#style-font-weight').val(data.fontWeight);
                     $('#cert-style-controls').show();
                     
                     e.stopPropagation();
                 });

                 $canvas.append($el);
            });

            // Init Draggable
            $('.cert-element').draggable({
                containment: "#cert-canvas",
                stop: function(event, ui) {
                    let key = $(this).data('tag');
                    // Calculate % based on parent size
                    let parentWidth = $('#cert-canvas').width();
                    let parentHeight = $('#cert-canvas').height();
                    let left = parseFloat($(this).css('left'));
                    let top = parseFloat($(this).css('top'));
                    
                    // Convert back to percentage
                    let pLeft = (left / parentWidth) * 100; // Draggable sets absolute pixels usually?
                    // jQuery UI draggable sets style.left/top in pixels relative to parent
                    // But we used translate(-50%, -50%) for centering.
                    // Visual position (center) = left pos + width/2. 
                    // Let's simplify: We simply store the new css left/top as % if possible, or px -> %
                    
                    // Actually, let's just get the position relative to parent
                    // var pos = $(this).position(); 
                    // pos.left is px.
                    
                    // We want to store % to be responsive if canvas scales (though fixed px for A4 is fine too)
                    // Let's stick to % for center point.
                    
                    // Re-calculate %
                     let finalX = (ui.position.left / parentWidth) * 100; // Left edge
                     let finalY = (ui.position.top / parentHeight) * 100; // Top edge
                     
                     // Since we do translate(-50%,-50%), the visual center is what we want? 
                     // No, if we use translate, left:50% means center.
                     // jQuery changes left to px value of the FILE.
                     // The transform remains.
                     
                     // We need to adjust for the transform offset? jQuery UI Draggable might interfere with transform.
                     // Let's assume standard left/top corner drag for simplicity, remove transform!
                }
            });
            
            // Re-apply Draggable with better config
             $('.cert-element').draggable({
                containment: "#cert-canvas",
                scroll: false,
                stop: function(event, ui) {
                     let key = $(this).data('tag');
                     let parentW = $('#cert-canvas').width();
                     let parentH = $('#cert-canvas').height();
                     
                     // Get current position (Top-Left corner of element)
                     let leftPx = ui.position.left;
                     let topPx = ui.position.top;
                     
                     // Convert to %
                     certSettings[key].x = (leftPx / parentW) * 100;
                     certSettings[key].y = (topPx / parentH) * 100;
                }
            });
            
            // Remove transform for easier drag math
            $('.cert-element').css('transform', 'none'); 
        }

        renderElements();

        // Style Change Listeners
        $('#style-font-size').on('input', function(){
            if(activeElementId) {
                let val = $(this).val();
                certSettings[activeElementId].fontSize = val;
                $('#el-'+activeElementId).css('font-size', val+'px');
            }
        });
        $('#style-color').on('input', function(){
             if(activeElementId) {
                let val = $(this).val();
                certSettings[activeElementId].color = val;
                $('#el-'+activeElementId).css('color', val);
            }
        });
         $('#style-font-weight').on('change', function(){
             if(activeElementId) {
                let val = $(this).val();
                certSettings[activeElementId].fontWeight = val;
                $('#el-'+activeElementId).css('font-weight', val);
            }
        });

        // Toggle Visibility
        $('.cert-toggle').on('change', function() {
            let key = $(this).data('tag');
            if($(this).is(':checked')) {
                // If exists in settings, show, else reset
                $('#el-'+key).show();
            } else {
                $('#el-'+key).hide();
            }
        });

        // Sync Settings on Submit
        $('#certForm').on('submit', function() {
            $('#certificate_settings_input').val(JSON.stringify(certSettings));
        });
    });

    // Preview Background
    window.previewCertBg = function(input) {
         if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                if($('#cert-bg-img').length){
                    $('#cert-bg-img').attr('src', e.target.result);
                } else {
                    $('#cert-bg-placeholder').replaceWith('<img src="'+e.target.result+'" id="cert-bg-img" style="width: 100%; height: 100%; object-fit: cover; position: absolute; z-index: 1;">');
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush