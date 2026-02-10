@extends('admin.layouts.app')

@section('page_title', $mentorship->exists ? 'Editar Mentoria' : 'Nova Mentoria')

@php
    $schedulePretty = '';
    if (!empty($mentorship->schedule) && is_array($mentorship->schedule)) {
        $schedulePretty = json_encode($mentorship->schedule, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
@endphp

@section('content')
    <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs" id="mentorship-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="general-tab" data-toggle="pill" href="#general" role="tab"
                        aria-controls="general" aria-selected="true">Dados Gerais</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="cert-tab" data-toggle="pill" href="#certificate" role="tab"
                        aria-controls="certificate" aria-selected="false">Certificado</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="mentorship-tabs-content">
                <!-- TAB GERAL -->
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <div class="mb-3">
                        <h5 class="mb-1">{{ $mentorship->exists ? 'Atualizar mentoria' : 'Cadastrar mentoria' }}</h5>
                        <p class="text-muted mb-0">
                            Preencha os dados principais. O campo de agenda aceita JSON para horarios, datas e links de
                            reuniao.
                        </p>
                    </div>

                    <form method="POST" id="mentorshipForm"
                        action="{{ $mentorship->exists ? route('admin.mentorships.update', $mentorship) : route('admin.mentorships.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @if($mentorship->exists)
                            @method('PUT')
                        @endif

                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label for="title">Titulo</label>
                                <input id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title', $mentorship->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="mentor_id">Mentor responsavel</label>
                                <select id="mentor_id" name="mentor_id"
                                    class="form-control @error('mentor_id') is-invalid @enderror">
                                    @foreach(($mentors ?? collect()) as $mentor)
                                        <option value="{{ $mentor->id }}" @selected((string) old('mentor_id', $mentorship->mentor_id) === (string) $mentor->id)>
                                            {{ $mentor->name }} ({{ $mentor->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('mentor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="price">Preco (R$)</label>
                                <input id="price" name="price" type="number" step="0.01" min="0"
                                    class="form-control @error('price') is-invalid @enderror"
                                    value="{{ old('price', $mentorship->price) }}">
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="slots">Vagas</label>
                                <input id="slots" name="slots" type="number" min="1"
                                    class="form-control @error('slots') is-invalid @enderror"
                                    value="{{ old('slots', $mentorship->slots) }}">
                                @error('slots')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Descricao</label>
                            <textarea id="description" name="description" rows="5"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Explique objetivo, publico e formato da mentoria">{{ old('description', $mentorship->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="type">Tipo</label>
                                <select id="type" name="type" class="form-control @error('type') is-invalid @enderror">
                                    <option value="online" {{ old('type', $mentorship->type) === 'online' ? 'selected' : '' }}>Online</option>
                                    <option value="presencial" {{ old('type', $mentorship->type) === 'presencial' ? 'selected' : '' }}>Presencial</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-4 video-platform-group">
                                <label for="video_platform">Plataforma</label>
                                <select id="video_platform" name="video_platform"
                                    class="form-control @error('video_platform') is-invalid @enderror">
                                    <option value="" {{ old('video_platform', $mentorship->video_platform) === '' ? 'selected' : '' }}>Selecione...</option>
                                    <option value="zoom" {{ old('video_platform', $mentorship->video_platform) === 'zoom' ? 'selected' : '' }}>Zoom</option>
                                    <option value="google_meet" {{ old('video_platform', $mentorship->video_platform) === 'google_meet' ? 'selected' : '' }}>Google Meet
                                    </option>
                                    <option value="teams" {{ old('video_platform', $mentorship->video_platform) === 'teams' ? 'selected' : '' }}>Teams</option>
                                    <option value="other" {{ old('video_platform', $mentorship->video_platform) === 'other' ? 'selected' : '' }}>Outra</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4 video-link-group">
                                <label for="video_link">Link da Videochamada</label>
                                <input id="video_link" name="video_link"
                                    class="form-control @error('video_link') is-invalid @enderror"
                                    value="{{ old('video_link', $mentorship->video_link) }}" placeholder="https://...">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="demo_link">Link da Sessão de Demonstração (Opcional)</label>
                            <input id="demo_link" name="demo_link"
                                class="form-control @error('demo_link') is-invalid @enderror"
                                value="{{ old('demo_link', $mentorship->demo_link) }}"
                                placeholder="Link para gravação ou demo">
                            <small class="form-text text-muted">Use este campo para salvar links de sessões gravadas ou
                                demonstrações.</small>
                        </div>

                        <div class="form-group">
                            <label for="schedule_json">Agenda (JSON opcional)</label>
                            <textarea id="schedule_json" name="schedule_json" rows="8"
                                class="form-control @error('schedule_json') is-invalid @enderror"
                                placeholder='{"timezone":"America/Sao_Paulo","sessions":[{"date":"2026-03-10","time":"19:00","link":"https://meet.google.com/..."}] }'>{{ old('schedule_json', $schedulePretty) }}</textarea>
                            @error('schedule_json')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.mentorships.index') }}" class="btn btn-outline-secondary">Voltar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Salvar mentoria
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB CERTIFICADO -->
                <div class="tab-pane fade" id="certificate" role="tabpanel" aria-labelledby="cert-tab">
                    @if(!$mentorship->exists)
                        <div class="alert alert-info border-0 shadow-sm">
                            <i class="fas fa-info-circle mr-2"></i> Você poderá configurar o certificado após salvar a mentoria
                            pela primeira vez.
                        </div>
                    @else
                        <form id="certForm" method="POST" action="{{ route('admin.mentorships.update', $mentorship) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-xl-9 col-lg-8">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-secondary text-white small py-1">Editor Visual (A4 Paisagem)
                                        </div>
                                        <div class="card-body bg-dark d-flex justify-content-center align-items-center p-4"
                                            style="min-height: 600px; overflow: auto;">
                                            <div id="cert-canvas"
                                                style="position: relative; width: 842px; height: 595px; background-color: white; box-shadow: 0 0 30px rgba(0,0,0,0.5); flex-shrink: 0; overflow: hidden;">
                                                @if($mentorship->certificate_bg)
                                                    <img src="{{ asset($mentorship->certificate_bg) }}" id="cert-bg-img"
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
                                                <div id="cert-elements-layer"
                                                    style="position: absolute; top:0; left:0; width: 100%; height: 100%; z-index: 10;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-lg-4">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-dark text-white font-weight-bold">Configurações</div>
                                        <div class="card-body">
                                            <div class="form-group custom-control custom-switch mb-4">
                                                <input type="checkbox" class="custom-control-input" id="is_certificate_enabled"
                                                    name="is_certificate_enabled" value="1" {{ $mentorship->is_certificate_enabled ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold"
                                                    for="is_certificate_enabled">Habilitar Certificado</label>
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

                                            <div class="form-group mt-3">
                                                <label class="small text-muted text-uppercase font-weight-bold">Assinatura do
                                                    Mentor</label>
                                                @if($mentorship->instructor_signature)
                                                    <div class="mb-2 text-center border p-2 bg-light rounded">
                                                        <img src="{{ asset($mentorship->instructor_signature) }}"
                                                            style="max-height: 50px;" class="img-fluid">
                                                    </div>
                                                @endif
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" name="instructor_signature"
                                                        accept="image/*">
                                                    <label class="custom-file-label">Trocar assinatura</label>
                                                </div>
                                            </div>

                                            <hr>

                                            <div id="cert-style-controls" style="display:none;">
                                                <label class="small text-muted text-uppercase font-weight-bold mb-2">Estilizar:
                                                    <span id="selected-elem-name" class="text-primary"></span></label>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Tamanho da Fonte (px)</label>
                                                    <input type="number" id="style-font-size"
                                                        class="form-control form-control-sm" min="8" max="120">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Cor do Texto</label>
                                                    <input type="color" id="style-color" class="form-control form-control-sm"
                                                        style="height: 30px;">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Peso da Fonte</label>
                                                    <select id="style-font-weight" class="form-control form-control-sm">
                                                        <option value="normal">Normal</option>
                                                        <option value="bold">Negrito</option>
                                                        <option value="500">Médio (500)</option>
                                                    </select>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Fonte</label>
                                                    <select id="style-font-family" class="form-control form-control-sm">
                                                        <option value="Arial, sans-serif">Arial</option>
                                                        <option value="'Times New Roman', serif">Times New Roman</option>
                                                        <option value="Georgia, serif">Georgia</option>
                                                        <option value="'Courier New', monospace">Courier New</option>
                                                    </select>
                                                </div>
                                                <div id="logo-dims" class="form-row" style="display:none;">
                                                    <div class="col-6">
                                                        <label class="small mb-1">Largura</label>
                                                        <input type="number" id="logo-width"
                                                            class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="small mb-1">Altura</label>
                                                        <input type="number" id="logo-height"
                                                            class="form-control form-control-sm">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <label
                                                    class="small text-muted text-uppercase font-weight-bold mb-2">Visibilidade</label>
                                                <div class="list-group list-group-flush border rounded overflow-hidden">
                                                    @foreach(['student_name' => 'Nome do Aluno', 'course_name' => 'Nome da Mentoria', 'completion_date' => 'Data Conclusão', 'certificate_code' => 'Cód. Validação', 'author_name' => 'Nome do Mentor', 'workload_hours' => 'Horas', 'platform_logo' => 'Logo UNN'] as $tag => $label)
                                                        <div
                                                            class="list-group-item py-2 px-3 d-flex justify-content-between align-items-center bg-light">
                                                            <span class="small font-weight-bold">{{ $label }}</span>
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox" class="custom-control-input cert-toggle"
                                                                    id="toggle-{{ $tag }}" data-tag="{{ $tag }}" checked>
                                                                <label class="custom-control-label" for="toggle-{{ $tag }}"></label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <input type="hidden" name="certificate_settings" id="certificate_settings_input">
                                            <button type="submit" class="btn btn-primary btn-block mt-4" id="btn-save-cert">
                                                <i class="fas fa-save mr-1"></i> Salvar Certificado
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .cert-element {
            padding: 4px;
            border-radius: 4px;
        }

        .cert-element:hover {
            background: rgba(0, 123, 255, 0.1);
            border: 1px dashed #007bff !important;
        }

        .ui-draggable-dragging {
            opacity: 0.7;
            z-index: 1000;
            border: 1px solid #007bff !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <script>
        $(document).ready(function () {
            if ('{{ $mentorship->exists }}' == '') return;

            // Initial Settings
            let certSettings = {!! $mentorship->certificate_settings ? json_encode($mentorship->certificate_settings) : '{}' !!};

            const defaultTags = {
                'student_name': { x: 50, y: 40, text: '[Nome do Aluno]', fontSize: 30, color: '#000000', fontWeight: 'bold', fontFamily: 'Arial, sans-serif' },
                'course_name': { x: 50, y: 55, text: '{{ $mentorship->title }}', fontSize: 24, color: '#333333', fontWeight: 'bold', fontFamily: 'Arial, sans-serif' },
                'completion_date': { x: 50, y: 65, text: 'Concluído em: 01/01/2026', fontSize: 16, color: '#555555', fontWeight: 'normal', fontFamily: 'Arial, sans-serif' },
                'certificate_code': { x: 50, y: 85, text: 'Validação: ABC-123', fontSize: 12, color: '#999999', fontWeight: 'normal', fontFamily: 'Arial, sans-serif' },
                'author_name': { x: 30, y: 90, text: '{{ $mentorship->mentor->name ?? "Mentor" }}', fontSize: 18, color: '#333333', fontWeight: 'bold', fontFamily: 'Arial, sans-serif' },
                'workload_hours': { x: 70, y: 90, text: 'Mentoria', fontSize: 14, color: '#666666', fontWeight: 'normal', fontFamily: 'Arial, sans-serif' },
                'platform_logo': { x: 50, y: 10, text: 'LOGO UNN', fontSize: 36, color: '#0066cc', fontWeight: 'bold', fontFamily: 'Georgia, serif', width: 120, height: 60, mandatory: true }
            };

            $.each(defaultTags, function (key, val) {
                if (!certSettings[key]) certSettings[key] = val;
            });

            const $canvas = $('#cert-elements-layer');
            let activeElementId = null;

            function renderElements() {
                $canvas.empty();
                $.each(certSettings, function (key, data) {
                    let $el = $('<div>').addClass('cert-element').attr('id', 'el-' + key).attr('data-tag', key)
                        .css({ position: 'absolute', left: data.x + '%', top: data.y + '%', fontSize: data.fontSize + 'px', color: data.color, fontWeight: data.fontWeight, fontFamily: data.fontFamily || 'Arial, sans-serif', cursor: 'move', whiteSpace: 'nowrap', border: '1px dashed transparent' })
                        .text(data.text);

                    $el.on('mousedown', function (e) {
                        $('.cert-element').css('border-borderColor', 'transparent');
                        $(this).css('border-color', '#007bff');
                        activeElementId = key;
                        $('#selected-elem-name').text(data.text);
                        $('#style-font-size').val(data.fontSize);
                        $('#style-color').val(data.color);
                        $('#style-font-weight').val(data.fontWeight);
                        $('#style-font-family').val(data.fontFamily || 'Arial, sans-serif');
                        $('#cert-style-controls').show();
                        $('#logo-dims').toggle(key === 'platform_logo');
                        e.stopPropagation();
                    });
                    $canvas.append($el);
                });

                $('.cert-element').draggable({
                    containment: "#cert-canvas",
                    stop: function (event, ui) {
                        let key = $(this).data('tag');
                        certSettings[key].x = (ui.position.left / $('#cert-canvas').width()) * 100;
                        certSettings[key].y = (ui.position.top / $('#cert-canvas').height()) * 100;
                    }
                });

                $('.cert-toggle').each(function () {
                    let key = $(this).data('tag');
                    if (key !== 'platform_logo' && !$(this).is(':checked')) $('#el-' + key).hide();
                });
            }

            renderElements();

            $('#style-font-size').on('input', function () { if (activeElementId) { certSettings[activeElementId].fontSize = $(this).val(); $('#el-' + activeElementId).css('font-size', $(this).val() + 'px'); } });
            $('#style-color').on('input', function () { if (activeElementId) { certSettings[activeElementId].color = $(this).val(); $('#el-' + activeElementId).css('color', $(this).val()); } });
            $('#style-font-weight').on('change', function () { if (activeElementId) { certSettings[activeElementId].fontWeight = $(this).val(); $('#el-' + activeElementId).css('font-weight', $(this).val()); } });
            $('#style-font-family').on('change', function () { if (activeElementId) { certSettings[activeElementId].fontFamily = $(this).val(); $('#el-' + activeElementId).css('font-family', $(this).val()); } });

            $('.cert-toggle').on('change', function () {
                let key = $(this).data('tag');
                if (key === 'platform_logo') { $(this).prop('checked', true); return; }
                $(this).is(':checked') ? $('#el-' + key).show() : $('#el-' + key).hide();
            });

            $('#certForm').on('submit', function (e) {
                e.preventDefault();
                $('#certificate_settings_input').val(JSON.stringify(certSettings));
                var formData = new FormData(this);
                $.ajax({
                    url: $(this).attr('action'), type: 'POST', data: formData, processData: false, contentType: false,
                    success: function () { toastr.success('Certificado salvo!'); },
                    error: function () { toastr.error('Erro ao salvar.'); }
                });
            });
        });

        window.previewCertBg = function (input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    if ($('#cert-bg-img').length) $('#cert-bg-img').attr('src', e.target.result);
                    else $('#cert-bg-placeholder').replaceWith('<img src="' + e.target.result + '" id="cert-bg-img" style="width: 100%; height: 100%; object-fit: cover; position: absolute; z-index: 1;">');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush