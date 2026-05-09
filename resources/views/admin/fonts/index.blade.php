@extends('admin.layouts.app')

@section('page_title', 'Fontes Personalizadas')
@section('breadcrumb')<li class="breadcrumb-item active">Fontes</li>@endsection

@section('content')
    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="info-box bg-gradient-primary elevation-1">
                <span class="info-box-icon"><i class="fas fa-font"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de Fontes</span>
                    <span class="info-box-number">{{ $fonts->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="info-box bg-gradient-info elevation-1">
                <span class="info-box-icon"><i class="fab fa-google"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Google Fonts</span>
                    <span class="info-box-number">{{ $fonts->where('type', 'google_link')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="info-box bg-gradient-success elevation-1">
                <span class="info-box-icon"><i class="fas fa-file"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Arquivo (Upload)</span>
                    <span class="info-box-number">{{ $fonts->where('type', 'file')->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-font text-primary mr-2"></i>Gerenciar Fontes
            </h3>
            <div class="card-tools">
                <button class="btn btn-primary btn-sm rounded-pill elevation-1" data-toggle="modal" data-target="#fontModal">
                    <i class="fas fa-plus mr-1"></i> Nova Fonte
                </button>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                <i class="fas fa-info-circle mr-1"></i>
                Gerencie as fontes disponiveis para uso nos certificados da plataforma. As fontes adicionadas ficam
                disponiveis globalmente para todos os cursos.
            </p>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Nome</th>
                            <th class="d-none d-md-table-cell">Familia da Fonte</th>
                            <th class="d-none d-lg-table-cell">Tipo</th>
                            <th class="d-none d-lg-table-cell">Carregado por</th>
                            <th class="d-none d-md-table-cell">Data</th>
                            <th class="text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fonts as $font)
                            <tr id="font-{{ $font->id }}">
                                <td>
                                    <strong>{{ $font->name }}</strong>
                                    <div class="d-md-none small text-muted">
                                        {{ $font->font_family }}
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span style="font-family: {{ $font->font_family }};">{{ $font->font_family }}</span>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    @if($font->type === 'google_link')
                                        <span class="badge badge-info"><i class="fab fa-google mr-1"></i>Google</span>
                                    @else
                                        <span class="badge badge-success"><i class="fas fa-file mr-1"></i>Arquivo</span>
                                    @endif
                                </td>
                                <td class="d-none d-lg-table-cell">{{ optional($font->uploader)->name ?? 'Sistema' }}</td>
                                <td class="d-none d-md-table-cell">
                                    <small>{{ $font->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td class="text-right">
                                    <button class="btn btn-sm btn-outline-danger rounded-pill btn-delete-font" data-id="{{ $font->id }}"
                                        data-name="{{ $font->name }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-font fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">Nenhuma fonte personalizada adicionada ainda.</p>
                                </td>
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
                    <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>Adicionar Nova Fonte</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="fontForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nome da Fonte (Amigavel) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Ex: Roboto Negrito" required>
                            <small class="text-muted">Nome descritivo para identificacao.</small>
                        </div>

                        <div class="form-group">
                            <label>Familia da Fonte (CSS) <span class="text-danger">*</span></label>
                            <input type="text" name="font_family" class="form-control"
                                placeholder="Ex: 'Roboto', sans-serif" required>
                            <small class="text-muted">Nome tecnico da fonte conforme sera usado no CSS.</small>
                        </div>

                        <div class="form-group">
                            <label>Tipo de Fonte <span class="text-danger">*</span></label>
                            <select name="type" id="fontType" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="google_link">Google Fonts (Link)</option>
                                <option value="file">Arquivo de Fonte (Upload)</option>
                            </select>
                        </div>

                        <!-- Google Fonts Option -->
                        <div id="google-font-section" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Como obter:</strong> Acesse <a href="https://fonts.google.com"
                                    target="_blank">Google Fonts</a>,
                                escolha uma fonte e copie o link do tipo <code>&lt;link href="..."&gt;</code>.
                            </div>
                            <div class="form-group">
                                <label>URL do Google Fonts</label>
                                <input type="url" name="google_font_url" id="google_font_url" class="form-control"
                                    placeholder="https://fonts.googleapis.com/css2?family=...">
                            </div>
                        </div>

                        <!-- File Upload Option -->
                        <div id="file-font-section" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Formatos aceitos:</strong> TTF, OTF, WOFF, WOFF2 (max: 5MB)
                            </div>
                            <div class="form-group">
                                <label>Arquivo da Fonte</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="font_file" id="font_file"
                                        accept=".ttf,.otf,.woff,.woff2">
                                    <label class="custom-file-label" for="font_file">Escolher arquivo...</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill elevation-1" id="btn-save-font">
                            <i class="fas fa-save mr-1"></i>Adicionar Fonte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const $nameInput = $('input[name="name"]');
            const $familyInput = $('input[name="font_family"]');

            function normalizeFontValue(value) {
                return (value || '').toString().replace(/\s+/g, ' ').trim();
            }

            function buildFallbackFromFile(fileName) {
                const baseName = (fileName || '').toString().replace(/\.[^/.]+$/, '');
                const normalized = normalizeFontValue(baseName.replace(/[_-]+/g, ' '));

                if (!normalized) {
                    return 'Fonte Personalizada';
                }

                return normalized;
            }

            function fillFontFields(name, family) {
                const detectedName = normalizeFontValue(name);
                const detectedFamily = normalizeFontValue(family).replace(/^['"]+|['"]+$/g, '');

                if (detectedName) {
                    $nameInput.val(detectedName);
                }

                if (detectedFamily) {
                    $familyInput.val(detectedFamily);
                }
            }

            function detectFontMetadata(file) {
                if (!file) {
                    return;
                }

                const detectData = new FormData();
                detectData.append('_token', '{{ csrf_token() }}');
                detectData.append('font_file', file);

                $.ajax({
                    url: '{{ route("admin.fonts.detect-metadata") }}',
                    type: 'POST',
                    data: detectData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        fillFontFields(response.name, response.font_family);
                    },
                    error: function () {
                        const fallback = buildFallbackFromFile(file.name);
                        fillFontFields(fallback, fallback);
                    }
                });
            }

            // Toggle form fields based on type
            $('#fontType').on('change', function () {
                const type = $(this).val();
                $('#google-font-section').toggle(type === 'google_link');
                $('#file-font-section').toggle(type === 'file');

                if (type === 'google_link') {
                    $('#google_font_url').attr('required', true);
                    $('#font_file').removeAttr('required');
                    $('#font_file').val('');
                    $('#font_file').next('.custom-file-label').html('Escolher arquivo...');
                } else if (type === 'file') {
                    $('#font_file').attr('required', true);
                    $('#google_font_url').removeAttr('required');
                    $('#google_font_url').val('');
                }
            });

            // Update file input label
            $('#font_file').on('change', function () {
                const fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName);

                const file = this.files && this.files[0] ? this.files[0] : null;
                detectFontMetadata(file);
            });

            // Form Submit
            $('#fontForm').on('submit', function (e) {
                e.preventDefault();

                const $btn = $('#btn-save-font');
                const originalText = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Salvando...');

                const formData = new FormData(this);

                $.ajax({
                    url: '{{ route("admin.fonts.store") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        toastr.success(response.message || 'Fonte adicionada!');
                        $('#fontModal').modal('hide');
                        setTimeout(() => location.reload(), 800);
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false).html(originalText);
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let msg = '';
                            $.each(xhr.responseJSON.errors, (k, v) => msg += v[0] + '<br>');
                            toastr.error(msg);
                        } else {
                            toastr.error('Erro ao adicionar fonte.');
                        }
                    }
                });
            });

            // Delete Font
            $('.btn-delete-font').on('click', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');

                confirmAction(null, 'Remover?', 'Deseja remover a fonte "' + name + '"?', function () {
                    $.ajax({
                        url: '/admin/fonts/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (response) {
                            toastr.success(response.message || 'Fonte removida.');
                            $('#font-' + id).fadeOut();
                        },
                        error: function () {
                            toastr.error('Erro ao remover fonte.');
                        }
                    });
                });
            });

            // Reset modal on close
            $('#fontModal').on('hidden.bs.modal', function () {
                $('#fontForm')[0].reset();
                $('#google-font-section, #file-font-section').hide();
                $('.custom-file-label').html('Escolher arquivo...');
            });
        });
    </script>
@endpush
