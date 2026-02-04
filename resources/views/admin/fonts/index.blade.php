@extends('admin.layouts.app')

@section('title', 'Gerenciar Fontes Personalizadas')

@push('styles')
<style>
    .fonts-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 3rem 2rem;
        border-radius: 1rem;
        color: white;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .font-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        border: 2px solid #f0f0f0;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .font-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }
    
    .font-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border-color: #667eea;
    }
    
    .font-card:hover::before {
        transform: scaleY(1);
    }
    
    .font-preview {
        font-size: 24px;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
        margin: 1rem 0;
        border-left: 4px solid #667eea;
    }
    
    .badge-google {
        background: linear-gradient(135deg, #4285f4 0%, #34a853 100%);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-file {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .btn-add-font {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 0.8rem 2rem;
        font-weight: 600;
        border-radius: 2rem;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
    }
    
    .btn-add-font:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 1rem;
        border: 2px dashed #e0e0e0;
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #667eea;
        margin-bottom: 1.5rem;
        opacity: 0.5;
    }
    
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.5rem 0.5rem 0 0;
    }
    
    .modal-header .close {
        color: white;
        opacity: 0.8;
    }
    
    .form-control:focus, .custom-file-input:focus ~ .custom-file-label {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .info-card {
        background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
        border-left: 4px solid #00acc1;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="fonts-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-2 font-weight-bold">
                    <i class="fas fa-font mr-3"></i>Fontes Personalizadas
                </h1>
                <p class="mb-0 opacity-75">
                    Gerencie as fontes disponíveis para uso nos certificados. Todas as fontes são compartilhadas globalmente.
                </p>
            </div>
            <button class="btn btn-light btn-add-font" data-toggle="modal" data-target="#fontModal">
                <i class="fas fa-plus mr-2"></i>Nova Fonte
            </button>
        </div>
    </div>

    <!-- Fonts Grid -->
    <div class="row">
        @forelse($fonts as $font)
            <div class="col-md-6 col-lg-4" id="font-{{ $font->id }}">
                <div class="font-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1 font-weight-bold">{{ $font->name }}</h5>
                            <small class="text-muted">
                                <i class="far fa-user mr-1"></i>{{ optional($font->uploader)->name ?? 'Sistema' }}
                            </small>
                        </div>
                        @if($font->type === 'google_link')
                            <span class="badge-google">
                                <i class="fab fa-google mr-1"></i>Google Fonts
                            </span>
                        @else
                            <span class="badge-file">
                                <i class="fas fa-file-upload mr-1"></i>Upload
                            </span>
                        @endif
                    </div>
                    
                    <div class="font-preview" style="font-family: {{ $font->font_family }};">
                        AaBbCc 123 - {{ $font->font_family }}
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            <i class="far fa-calendar mr-1"></i>{{ $font->created_at->format('d/m/Y H:i') }}
                        </small>
                        <button class="btn btn-sm btn-danger btn-delete-font rounded-pill" 
                                data-id="{{ $font->id }}" 
                                data-name="{{ $font->name }}">
                            <i class="fas fa-trash mr-1"></i>Remover
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-font"></i>
                    <h3 class="text-muted mb-2">Nenhuma Fonte Personalizada</h3>
                    <p class="text-muted mb-4">Adicione fontes customizadas para usar nos certificados dos seus cursos.</p>
                    <button class="btn btn-primary btn-lg rounded-pill" data-toggle="modal" data-target="#fontModal">
                        <i class="fas fa-plus mr-2"></i>Adicionar Primeira Fonte
                    </button>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="fontModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-plus-circle mr-2"></i>Adicionar Nova Fonte
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="fontForm">
                @csrf
                <div class="modal-body p-4">
                    
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-tag mr-1"></i>Nome da Fonte
                        </label>
                        <input type="text" name="name" class="form-control form-control-lg" 
                               placeholder="Ex: Roboto Bold, Montserrat Light" required>
                        <small class="text-muted">Nome descritivo para fácil identificação.</small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-code mr-1"></i>Família da Fonte (CSS)
                        </label>
                        <input type="text" name="font_family" class="form-control form-control-lg" 
                               placeholder="Ex: 'Roboto', sans-serif" required>
                        <small class="text-muted">Nome técnico exato como será usado no CSS.</small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-layer-group mr-1"></i>Tipo de Fonte
                        </label>
                        <select name="type" id="fontType" class="form-control form-control-lg" required>
                            <option value="">Selecione o tipo...</option>
                            <option value="google_link">
                                <i class="fab fa-google"></i> Google Fonts (Link)
                            </option>
                            <option value="file">
                                <i class="fas fa-file-upload"></i> Arquivo de Fonte (Upload)
                            </option>
                        </select>
                    </div>

                    <!-- Google Fonts Option -->
                    <div id="google-font-section" style="display: none;">
                        <div class="info-card">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle mt-1 mr-2" style="color: #00acc1;"></i>
                                <div>
                                    <strong>Como obter o link:</strong>
                                    <ol class="mb-0 pl-3 mt-2">
                                        <li>Acesse <a href="https://fonts.google.com" target="_blank" class="font-weight-bold">fonts.google.com</a></li>
                                        <li>Escolha a fonte desejada</li>
                                        <li>Clique em "Select this style"</li>
                                        <li>Copie o código <code>&lt;link href="..."&gt;</code></li>
                                        <li>Cole apenas a URL aqui</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">URL do Google Fonts</label>
                            <input type="url" name="google_font_url" id="google_font_url" 
                                   class="form-control" 
                                   placeholder="https://fonts.googleapis.com/css2?family=...">
                        </div>
                    </div>

                    <!-- File Upload Option -->
                    <div id="file-font-section" style="display: none;">
                        <div class="info-card">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle mt-1 mr-2" style="color: #00acc1;"></i>
                                <div>
                                    <strong>Formatos aceitos:</strong> TTF, OTF, WOFF, WOFF2<br>
                                    <strong>Tamanho máximo:</strong> 5MB
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Arquivo da Fonte</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="font_file" 
                                       id="font_file" accept=".ttf,.otf,.woff,.woff2">
                                <label class="custom-file-label" for="font_file">
                                    <i class="fas fa-cloud-upload-alt mr-2"></i>Escolher arquivo...
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="btn-save-font">
                        <i class="fas fa-check mr-1"></i>Adicionar Fonte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle form fields based on type
        $('#fontType').on('change', function() {
            const type = $(this).val();
            $('#google-font-section').slideUp(200);
            $('#file-font-section').slideUp(200);
            
            if(type === 'google_link') {
                $('#google-font-section').slideDown(300);
                $('#google_font_url').attr('required', true);
                $('#font_file').removeAttr('required');
            } else if(type === 'file') {
                $('#file-font-section').slideDown(300);
                $('#font_file').attr('required', true);
                $('#google_font_url').removeAttr('required');
            }
        });

        // Update file input label
        $('#font_file').on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(`<i class="fas fa-file mr-2"></i>${fileName}`);
        });

        // Form Submit
        $('#fontForm').on('submit', function(e) {
            e.preventDefault();
            
            const $btn = $('#btn-save-font');
            const originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...');
            
            const formData = new FormData(this);
            
            $.ajax({
                url: '{{ route("admin.fonts.store") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    toastr.success(response.message || 'Fonte adicionada com sucesso!', 'Sucesso!');
                    $('#fontModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    if(xhr.responseJSON && xhr.responseJSON.errors) {
                        let msg = '';
                        $.each(xhr.responseJSON.errors, (k,v) => msg += v[0]+'<br>');
                        toastr.error(msg, 'Erro de Validação');
                    } else {
                        toastr.error('Erro ao adicionar fonte. Tente novamente.', 'Erro!');
                    }
                }
            });
        });

        // Delete Font with Animation
        $('.btn-delete-font').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const $card = $('#font-' + id);
            
            if(!confirm(`⚠️ Tem certeza que deseja remover a fonte "${name}"?\n\nEsta ação não pode ser desfeita.`)) return;
            
            $.ajax({
                url: '/admin/fonts/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    toastr.success(response.message || 'Fonte removida com sucesso!', 'Removido!');
                    $card.fadeOut(400, function() {
                        $(this).remove();
                        // Check if no fonts left
                        if($('.font-card').length === 0) {
                            location.reload();
                        }
                    });
                },
                error: function() {
                    toastr.error('Erro ao remover fonte. Tente novamente.', 'Erro!');
                }
            });
        });
        
        // Reset modal on close
        $('#fontModal').on('hidden.bs.modal', function() {
            $('#fontForm')[0].reset();
            $('#google-font-section, #file-font-section').hide();
            $('.custom-file-label').html('<i class="fas fa-cloud-upload-alt mr-2"></i>Escolher arquivo...');
        });
    });
</script>
@endpush
