@php
    $galleryMedia = $model->media()->orderBy('created_at', 'desc')->get();
    $mediaTypeIcons = [
        'image' => 'fa-image',
        'video' => 'fa-video',
    ];
@endphp

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Galeria de Fotos e Vídeos</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="premium-upload-box mb-4" 
             id="gallery-uploader" 
             data-url="{{ $uploadUrl }}"
             data-delete-url-base="{{ $deleteUrlBase }}"
             data-max-size="50">
            <div class="upload-dropzone" id="dropzone">
                <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-primary"></i>
                <h5>Arraste arquivos aqui ou clique para selecionar</h5>
                <p class="text-muted small">Imagens (JPG, PNG, WEBP) e Vídeos (MP4, MOV) até 50MB</p>
                <input type="file" id="file-input" multiple class="d-none" accept="image/*,video/*">
                <button type="button" class="btn btn-primary mt-2" onclick="document.getElementById('file-input').click()">
                    Selecionar Arquivos
                </button>
            </div>
            
            <div class="upload-progress-container d-none mt-3">
                <div class="progress progress-sm">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <span class="upload-status small text-muted">Enviando...</span>
                    <span class="upload-percentage small font-weight-bold">0%</span>
                </div>
            </div>
        </div>

        <div class="row gallery-container" id="gallery-items">
            @forelse($galleryMedia as $item)
                <div class="col-sm-6 col-md-4 col-lg-3 mb-4 gallery-item" data-id="{{ $item->id }}">
                    <div class="card h-100 shadow-sm border-0 overflow-hidden position-relative">
                        @if($item->type === 'image')
                            <img src="{{ $item->url }}" class="card-img-top img-fluid" style="height: 160px; object-fit: cover;">
                        @else
                            <div class="bg-dark d-flex align-items-center justify-content-center" style="height: 160px;">
                                <i class="fas fa-video fa-3x text-white-50"></i>
                            </div>
                        @endif
                        
                        <div class="position-absolute" style="top: 10px; left: 10px;">
                            <span class="badge badge-{{ $item->type === 'image' ? 'info' : 'warning' }} shadow-sm">
                                <i class="fas {{ $mediaTypeIcons[$item->type] }} mr-1"></i>
                                {{ ucfirst($item->type) }}
                            </span>
                        </div>

                        <div class="card-footer p-2 bg-white d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ $item->created_at->format('d/m/Y') }}</small>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-media" 
                                    data-url="{{ str_replace(':media', $item->id, $deleteUrlPattern) }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 empty-gallery-message">
                    <i class="far fa-images fa-4x text-muted mb-3"></i>
                    <p class="text-muted">Nenhuma mídia enviada ainda.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('js')
<script>
$(document).ready(function() {
    const $dropzone = $('#dropzone');
    const $fileInput = $('#file-input');
    const $progressContainer = $('.upload-progress-container');
    const $progressBar = $('.progress-bar');
    const $uploadPercentage = $('.upload-percentage');
    const $galleryContainer = $('#gallery-items');
    
    const uploadUrl = '{{ $uploadUrl }}';

    $dropzone.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    }).on('dragleave', function() {
        $(this).removeClass('dragover');
    }).on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        handleFiles(e.originalEvent.dataTransfer.files);
    });

    $fileInput.on('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length === 0) return;

        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

        $progressContainer.removeClass('d-none');
        $progressBar.css('width', '0%');
        $uploadPercentage.text('0%');

        axios.post(uploadUrl, formData, {
            onUploadProgress: function(progressEvent) {
                const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                $progressBar.css('width', percentCompleted + '%');
                $uploadPercentage.text(percentCompleted + '%');
            }
        })
        .then(function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: response.data.message,
                timer: 3000,
                toast: true,
                position: 'top-end',
                showConfirmButton: false
            });
            window.location.reload(); // Simpler for now to ensure all logic (like delete URLs) is correct
        })
        .catch(function(error) {
            let message = 'Erro ao enviar arquivos.';
            if (error.response && error.response.data && error.response.data.message) {
                message = error.response.data.message;
            } else if (error.response && error.response.data && error.response.data.errors) {
                message = Object.values(error.response.data.errors).flat().join('<br>');
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                html: message
            });
        })
        .finally(function() {
            $progressContainer.addClass('d-none');
            $fileInput.val('');
        });
    }

    $(document).on('click', '.btn-delete-media', function() {
        const url = $(this).data('url');
        const $item = $(this).closest('.gallery-item');

        Swal.fire({
            title: 'Tem certeza?',
            text: "Esta ação não pode ser desfeita!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, deletar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(url)
                    .then(function(response) {
                        $item.fadeOut(function() {
                            $(this).remove();
                            if ($('#gallery-items .gallery-item').length === 0) {
                                $galleryContainer.html(`
                                    <div class="col-12 text-center py-5 empty-gallery-message">
                                        <i class="far fa-images fa-4x text-muted mb-3"></i>
                                        <p class="text-muted">Nenhuma mídia enviada ainda.</p>
                                    </div>
                                `);
                            }
                        });
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Deletado!',
                            text: 'Mídia removida com sucesso.',
                            timer: 2000,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false
                        });
                    })
                    .catch(function(error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: 'Não foi possível deletar o arquivo.'
                        });
                    });
            }
        });
    });
});
</script>

<style>
.upload-dropzone {
    border: 2px dashed #007bff;
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    transition: background 0.3s ease;
    cursor: pointer;
}
.upload-dropzone:hover, .upload-dropzone.dragover {
    background: rgba(0, 123, 255, 0.05);
}
.premium-upload-box {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
}
</style>
@endpush
