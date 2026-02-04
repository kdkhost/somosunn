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
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="courseTabsContent">
            <!-- INFO TAB -->
            <div class="tab-pane fade show active" id="info" role="tabpanel">
                <form method="POST" action="{{ $course->exists ? route('admin.courses.update',$course) : route('admin.courses.store') }}">
                    @csrf
                    @if($course->exists) @method('PUT') @endif
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label>Título do Curso</label>
                                <input name="title" class="form-control form-control-lg" value="{{ old('title',$course->title) }}" required placeholder="Ex: Curso Completo de Laravel">
                            </div>
                            <div class="form-group mb-3">
                                <label>Descrição Completa</label>
                                <textarea name="description" class="form-control summernote">{{ old('description',$course->description) }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
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
                                    <div class="form-check mb-3">
                                        <input type="checkbox" name="published" value="1" class="form-check-input" id="published" {{ $course->status === 'published' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="published">Publicado (Legacy)</label>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-block btn-lg">Salvar Informações</button>
                            
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
                    <button class="btn btn-success" onclick="openLessonModal()"><i class="fas fa-plus"></i> Nova Aula</button>
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
                                <button class="btn btn-sm btn-info btn-edit-lesson" data-id="{{ $lesson->id }}"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('courses.lessons.destroy', [$course, $lesson]) }}" method="POST" class="d-inline ajax-delete">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">Nenhuma aula cadastrada ainda.</div>
                    @endforelse
                </div>
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
                        <button type="button" class="btn btn-primary" id="btnSaveLesson">Salvar Dados da Aula</button>
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
                        <button class="btn btn-sm btn-outline-secondary" onclick="renameAttachment(${lessonId}, ${att.id}, '${att.file_name}')"><i class="fas fa-pen"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteAttachment(${lessonId}, ${att.id})"><i class="fas fa-trash"></i></button>
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

                    if (response.lesson) {
                        // It was a Create action, switch to saved mode
                        const lesson = response.lesson;
                        
                        // Switch to Edit Mode in UI
                        $('#lessonId').val(lesson.id);
                        $('#lessonMethod').val('PUT'); // Next saves are updates
                        $('#lessonModalTitle').text('Editar Aula: ' + lesson.title);
                        
                        // Enable Attachments
                        $('#attachmentsSection').show();
                        $('#attachmentsWarning').hide();
                        updateDropzoneUrl(lesson.id);

                        // Lock Video
                        $('#lessonVideo').prop('disabled', true);
                        $('#lessonVideoFile').prop('disabled', true);
                        $('#video-source-tab a').addClass('disabled');

                        // Refresh the list in the background (optional, or just append)
                        // For now, let's keep it simple. If they close, they see the list. 
                        // To be perfect, we should append to #lessons-list. But reloading on modal close is safer for integrity.
                        // Let's force a reload ONLY when the modal is closed if changes happened.
                        $('#lessonModal').one('hidden.bs.modal', function () {
                            window.location.reload();
                        });

                    } else {
                        // Update action
                         $('#lessonModal').one('hidden.bs.modal', function () {
                            window.location.reload();
                        });
                    }
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
    });
</script>
@endpush