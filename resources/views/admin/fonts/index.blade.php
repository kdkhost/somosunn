@extends('admin.layouts.app')

@section('title', 'Gerenciar Fontes Personalizadas')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Fontes Personalizadas</h3>
        <button class="btn btn-primary" data-toggle="modal" data-target="#fontModal">
            <i class="fas fa-plus"></i> Nova Fonte
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted">Gerencie as fontes disponíveis para uso nos certificados da plataforma. As fontes adicionadas ficam disponíveis globalmente para todos os cursos.</p>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Família da Fonte</th>
                        <th>Tipo</th>
                        <th>Carregado por</th>
                        <th>Data</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody id="fonts-list">
                    @forelse($fonts as $font)
                    <tr id="font-{{ $font->id }}">
                        <td><strong>{{ $font->name }}</strong></td>
                        <td><span style="font-family: {{ $font->font_family }};">{{ $font->font_family }}</span></td>
                        <td>
                            @if($font->type === 'google_link')
                                <span class="badge badge-info">Google Fonts</span>
                            @else
                                <span class="badge badge-success">Arquivo</span>
                            @endif
                        </td>
                        <td>{{ optional($font->uploader)->name ?? 'Sistema' }}</td>
                        <td>{{ $font->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-danger btn-delete-font" data-id="{{ $font->id }}" data-name="{{ $font->name }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Nenhuma fonte personalizada adicionada ainda.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="fontModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Nova Fonte</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="fontForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nome da Fonte (Amigável)</label>
                        <input type="text" name="name" class="form-control" placeholder="Ex: Roboto Negrito" required>
                        <small class="text-muted">Nome descritivo para identificação.</small>
                    </div>

                    <div class="form-group">
                        <label>Família da Fonte (CSS)</label>
                        <input type="text" name="font_family" class="form-control" placeholder="Ex: 'Roboto', sans-serif" required>
                        <small class="text-muted">Nome técnico da fonte conforme será usado no CSS.</small>
                    </div>

                    <div class="form-group">
                        <label>Tipo de Fonte</label>
                        <select name="type" id="fontType" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="google_link">Google Fonts (Link)</option>
                            <option value="file">Arquivo de Fonte Upload)</option>
                        </select>
                    </div>

                    <!-- Google Fonts Option -->
                    <div id="google-font-section" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Acesse <a href="https://fonts.google.com" target="_blank">Google Fonts</a>, escolha uma fonte e copie o link do tipo <code>&lt;link href="..."&gt;</code>.
                        </div>
                        <div class="form-group">
                            <label>URL do Google Fonts</label>
                            <input type="url" name="google_font_url" id="google_font_url" class="form-control" placeholder="https://fonts.googleapis.com/css2?family=...">
                        </div>
                    </div>

                    <!-- File Upload Option -->
                    <div id="file-font-section" style="display: none;">
                        <div class="form-group">
                            <label>Arquivo da Fonte</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="font_file" id="font_file" accept=".ttf,.otf,.woff,.woff2">
                                <label class="custom-file-label" for="font_file">Escolher arquivo...</label>
                            </div>
                            <small class="text-muted">Formatos aceitos: TTF, OTF, WOFF, WOFF2 (max: 5MB).</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-font">Adicionar Fonte</button>
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
            $('#google-font-section').toggle(type === 'google_link');
            $('#file-font-section').toggle(type === 'file');
            
            // Toggle required attributes
            if(type === 'google_link') {
                $('#google_font_url').attr('required', true);
                $('#font_file').removeAttr('required');
            } else if(type === 'file') {
                $('#font_file').attr('required', true);
                $('#google_font_url').removeAttr('required');
            }
        });

        // Update file input label
        $('#font_file').on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });

        // Form Submit
        $('#fontForm').on('submit', function(e) {
            e.preventDefault();
            
            const $btn = $('#btn-save-font');
            const originalText = $btn.text();
            $btn.prop('disabled', true).text('Salvando...');
            
            const formData = new FormData(this);
            
            $.ajax({
                url: '{{ route("admin.fonts.store") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    toastr.success(response.message || 'Fonte adicionada!');
                    $('#fontModal').modal('hide');
                    setTimeout(() => location.reload(), 800);
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).text(originalText);
                    if(xhr.responseJSON && xhr.responseJSON.errors) {
                        let msg = '';
                        $.each(xhr.responseJSON.errors, (k,v) => msg += v[0]+'<br>');
                        toastr.error(msg);
                    } else {
                        toastr.error('Erro ao adicionar fonte.');
                    }
                }
            });
        });

        // Delete Font
        $('.btn-delete-font').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            if(!confirm(`Deseja remover a fonte "${name}"?`)) return;
            
            $.ajax({
                url: '/admin/fonts/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    toastr.success(response.message || 'Fonte removida.');
                    $('#font-' + id).fadeOut();
                },
                error: function() {
                    toastr.error('Erro ao remover fonte.');
                }
            });
        });
    });
</script>
@endpush
