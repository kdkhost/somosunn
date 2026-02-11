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

                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <label class="mb-2"><i class="fas fa-image mr-1"></i> Foto de Capa</label>
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        @if($mentorship->image)
                                            <img src="{{ asset($mentorship->image) }}" class="img-fluid rounded shadow-sm" alt="Capa da mentoria">
                                        @else
                                            <div class="bg-white rounded mb-2 d-flex align-items-center justify-content-center border"
                                                style="height: 120px;">
                                                <i class="fas fa-image fa-3x text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-8">
                                        <div class="custom-file mb-2">
                                            <input type="file" name="image" class="custom-file-input" id="mentorshipImage"
                                                accept="image/png, image/jpeg, image/jpg" {{ !$mentorship->exists ? 'required' : '' }}>
                                            <label class="custom-file-label" for="mentorshipImage" data-browse="Buscar">Escolher imagem</label>
                                        </div>
                                        <small class="text-muted d-block">Recomendado: 1280x720px (JPG/PNG)</small>

                                        @error('image')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror

                                        @if($mentorship->image)
                                            <div class="custom-control custom-switch mt-2">
                                                <input type="checkbox" class="custom-control-input" id="remove_image" name="remove_image" value="1">
                                                <label class="custom-control-label" for="remove_image">Remover imagem atual</label>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
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
                                <div class="col-12">
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
                                                            <button type="button" class="btn btn-outline-light" id="cert-fit">
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
                                                @if($mentorship->certificate_bg)
                                                    <img src="{{ asset($mentorship->certificate_bg) }}" id="cert-bg-img"
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
                                                <div id="cert-grid-overlay"
                                                    style="position: absolute; top:0; left:0; width: 100%; height: 100%; z-index: 5; pointer-events: none; display: none;">
                                                </div>
                                                <div id="cert-elements-layer"
                                                    style="position: absolute; top:0; left:0; width: 100%; height: 100%; z-index: 10;">
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

                                            <div class="form-group">
                                                <label class="small text-muted text-uppercase font-weight-bold">Ajuste do
                                                    Fundo</label>
                                                <select id="cert-bg-fit" class="form-control form-control-sm">
                                                    <option value="cover">Cover (cortar)</option>
                                                    <option value="stretch">Stretch (esticar)</option>
                                                </select>
                                            </div>

                                            @php
                                                $certTitleInput = data_get($mentorship->certificate_settings, 'meta.titleText');
                                                if (!is_string($certTitleInput) || trim($certTitleInput) === '') {
                                                    $legacyCustom = data_get($mentorship->certificate_settings, 'custom_title');
                                                    $legacyTitle = data_get($mentorship->certificate_settings, 'title');
                                                    $certTitleInput = is_string($legacyCustom)
                                                        ? $legacyCustom
                                                        : (is_string($legacyTitle) ? $legacyTitle : 'CERTIFICADO DE CONCLUSÃO');
                                                }

                                                $certPresentationInput = data_get($mentorship->certificate_settings, 'meta.presentationText');
                                                if (!is_string($certPresentationInput)) {
                                                    $legacyCustomPres = data_get($mentorship->certificate_settings, 'custom_presentation_text');
                                                    $legacyPres = data_get($mentorship->certificate_settings, 'presentation_text');
                                                    $certPresentationInput = is_string($legacyCustomPres)
                                                        ? $legacyCustomPres
                                                        : (is_string($legacyPres) ? $legacyPres : '');
                                                }
                                            @endphp

                                            <div class="form-group">
                                                <label class="small text-muted text-uppercase font-weight-bold">Título do
                                                    Certificado</label>
                                                <input type="text" class="form-control" name="certificate_title"
                                                    id="certificate_title"
                                                    value="{{ old('certificate_title', $certTitleInput) }}"
                                                    placeholder="CERTIFICADO DE CONCLUSÃO">
                                            </div>

                                            <div class="form-group">
                                                <label class="small text-muted text-uppercase font-weight-bold">Texto de
                                                    Apresentação</label>
                                                <textarea class="form-control" name="presentation_text" id="presentation_text"
                                                    rows="2"
                                                    placeholder="Texto de apresentação (opcional)">{{ old('presentation_text', $certPresentationInput) }}</textarea>
                                                <small class="text-muted">Texto acima do nome do aluno (opcional)</small>
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

                                            <h6 class="small text-muted text-uppercase font-weight-bold mb-2">Ferramentas</h6>
                                            <div class="bg-light p-2 rounded border mb-3">
                                                <div class="custom-control custom-switch mb-2">
                                                    <input type="checkbox" class="custom-control-input" id="cert-grid-enabled">
                                                    <label class="custom-control-label" for="cert-grid-enabled">Mostrar
                                                        grade</label>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Grade (%):</label>
                                                    <select id="cert-grid-step" class="form-control form-control-sm">
                                                        <option value="10">10%</option>
                                                        <option value="5" selected>5%</option>
                                                        <option value="2">2%</option>
                                                        <option value="1">1%</option>
                                                        <option value="0.5">0.5%</option>
                                                    </select>
                                                </div>

                                                <div class="custom-control custom-switch mb-2">
                                                    <input type="checkbox" class="custom-control-input" id="cert-snap-enabled"
                                                        checked>
                                                    <label class="custom-control-label" for="cert-snap-enabled">Snap na
                                                        grade</label>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Snap (%):</label>
                                                    <input type="number" id="cert-snap-step" class="form-control form-control-sm"
                                                        step="0.1" value="1">
                                                </div>

                                                <div class="form-group mb-0">
                                                    <label class="small mb-1">Nudge (setas):</label>
                                                    <select id="cert-nudge-step" class="form-control form-control-sm">
                                                        <option value="0.1">0.1%</option>
                                                        <option value="0.25">0.25%</option>
                                                        <option value="0.5" selected>0.5%</option>
                                                        <option value="1">1%</option>
                                                        <option value="2">2%</option>
                                                    </select>
                                                    <small class="text-muted">Dica: segure Shift para 5x</small>
                                                </div>
                                            </div>

                                                </div>

                                                <div class="col-12 col-xl-6 mt-3 mt-xl-0">
                                                    <hr class="d-xl-none">

                                                    <h6 class="small text-muted text-uppercase font-weight-bold mb-2">Camadas</h6>
                                            <div class="list-group mb-3" id="cert-layers"></div>

                                            <div id="cert-style-controls" style="display:none;">
                                                <label class="small text-muted text-uppercase font-weight-bold mb-2">Editar:
                                                    <span id="selected-elem-name" class="text-primary"></span></label>
                                                <div class="form-row">
                                                    <div class="col-6">
                                                        <div class="form-group mb-2">
                                                            <label class="small mb-1">X (%)</label>
                                                            <input type="number" id="style-x"
                                                                class="form-control form-control-sm" step="0.1">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group mb-2">
                                                            <label class="small mb-1">Y (%)</label>
                                                            <input type="number" id="style-y"
                                                                class="form-control form-control-sm" step="0.1">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="custom-control custom-switch mb-2">
                                                    <input type="checkbox" class="custom-control-input" id="style-locked">
                                                    <label class="custom-control-label" for="style-locked">Bloquear
                                                        elemento</label>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Tamanho da Fonte (px)</label>
                                                    <input type="number" id="style-font-size"
                                                        class="form-control form-control-sm" min="8" max="120">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Camada (Z-Index)</label>
                                                    <input type="number" id="style-z-index"
                                                        class="form-control form-control-sm" min="0" max="999">
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
                                                    @foreach(['student_name' => 'Nome do Aluno', 'course_name' => 'Nome da Mentoria', 'completion_date' => 'Data Conclusão', 'certificate_code' => 'Cód. Validação', 'author_name' => 'Nome do Mentor', 'workload_hours' => 'Horas', 'title' => 'Título do Certificado', 'presentation_text' => 'Texto de Apresentação', 'instructor_signature' => 'Assinatura do Mentor', 'platform_logo' => 'Logo UNN'] as $tag => $label)
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

            let rawCertSettings = {!! $mentorship->certificate_settings ? json_encode($mentorship->certificate_settings) : 'null' !!};

            let certDoc = {
                schemaVersion: 2,
                meta: { backgroundFit: 'cover' },
                elements: {}
            };

            const isV2 = rawCertSettings
                && typeof rawCertSettings === 'object'
                && rawCertSettings.schemaVersion === 2
                && rawCertSettings.elements
                && typeof rawCertSettings.elements === 'object';

            if (isV2) {
                certDoc = rawCertSettings;
                certDoc.meta = (certDoc.meta && typeof certDoc.meta === 'object') ? certDoc.meta : {};
                certDoc.elements = (certDoc.elements && typeof certDoc.elements === 'object') ? certDoc.elements : {};
            } else {
                certDoc.meta = certDoc.meta || {};
                if (rawCertSettings && typeof rawCertSettings.backgroundFit === 'string') {
                    certDoc.meta.backgroundFit = rawCertSettings.backgroundFit;
                }

                if (rawCertSettings && typeof rawCertSettings.custom_title === 'string') {
                    certDoc.meta.titleText = rawCertSettings.custom_title;
                } else if (rawCertSettings && typeof rawCertSettings.title === 'string') {
                    certDoc.meta.titleText = rawCertSettings.title;
                }

                if (rawCertSettings && typeof rawCertSettings.custom_presentation_text === 'string') {
                    certDoc.meta.presentationText = rawCertSettings.custom_presentation_text;
                } else if (rawCertSettings && typeof rawCertSettings.presentation_text === 'string') {
                    certDoc.meta.presentationText = rawCertSettings.presentation_text;
                }

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

            let certSettings = certDoc.elements;

            @php
                $logoAuth = \App\Models\Setting::get('logo_auth') ?: \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
                $logoAuthSrc = $logoAuth ? asset(ltrim($logoAuth, '/')) : asset('img/logo.svg');
            @endphp
            const platformLogoUrl = "{{ $logoAuthSrc }}";
            const instructorSignatureUrl = "{{ $mentorship->instructor_signature ? asset($mentorship->instructor_signature) : '' }}";
            let instructorSignaturePreviewUrl = instructorSignatureUrl;

            const initialTitleText = ($('#certificate_title').length ? ($('#certificate_title').val() || certDoc.meta.titleText) : certDoc.meta.titleText) || 'CERTIFICADO DE CONCLUSÃO';
            const initialPresentationText = ($('#presentation_text').length ? ($('#presentation_text').val() || certDoc.meta.presentationText) : certDoc.meta.presentationText) || '';

            const defaultTags = {
                'student_name': { x: 50, y: 40, text: '[Nome do Aluno]', fontSize: 30, color: '#000000', fontWeight: 'bold', fontFamily: 'Arial, sans-serif' },
                'course_name': { x: 50, y: 55, text: '{{ $mentorship->title }}', fontSize: 24, color: '#333333', fontWeight: 'bold', fontFamily: 'Arial, sans-serif' },
                'completion_date': { x: 50, y: 65, text: 'Concluído em: 01/01/2026', fontSize: 16, color: '#555555', fontWeight: 'normal', fontFamily: 'Arial, sans-serif' },
                'certificate_code': { x: 50, y: 85, text: 'Validação: ABC-123', fontSize: 12, color: '#999999', fontWeight: 'normal', fontFamily: 'Arial, sans-serif' },
                'author_name': { x: 30, y: 90, text: '{{ $mentorship->mentor->name ?? "Mentor" }}', fontSize: 18, color: '#333333', fontWeight: 'bold', fontFamily: 'Arial, sans-serif', zIndex: 10 },
                'workload_hours': { x: 70, y: 90, text: 'Mentoria', fontSize: 14, color: '#666666', fontWeight: 'normal', fontFamily: 'Arial, sans-serif', zIndex: 10 },
                'title': { x: 10, y: 18, text: initialTitleText, fontSize: 34, color: '#000000', fontWeight: 'bold', fontFamily: 'Arial, sans-serif', zIndex: 15, visible: false, multiline: true, maxWidth: 700, textAlign: 'center' },
                'presentation_text': { x: 10, y: 28, text: initialPresentationText, fontSize: 16, color: '#333333', fontWeight: 'normal', fontFamily: 'Arial, sans-serif', zIndex: 15, visible: false, multiline: true, maxWidth: 700, textAlign: 'center' },
                'instructor_signature': { x: 70, y: 80, text: 'Assinatura do Mentor', fontSize: 12, color: '#6c757d', fontWeight: 'normal', fontFamily: 'Arial, sans-serif', width: 200, height: 60, zIndex: 10, visible: !!instructorSignatureUrl },
                'platform_logo': { x: 50, y: 10, text: 'LOGO', fontSize: 36, color: '#0066cc', fontWeight: 'bold', fontFamily: 'Georgia, serif', width: 120, height: 60, mandatory: true, zIndex: 20 }
            };

            @php
                $certificateTagLabels = [
                    'student_name' => 'Nome do Aluno',
                    'course_name' => 'Nome da Mentoria',
                    'completion_date' => 'Data Conclusão',
                    'certificate_code' => 'Cód. Validação',
                    'author_name' => 'Nome do Mentor',
                    'workload_hours' => 'Horas',
                    'title' => 'Título do Certificado',
                    'presentation_text' => 'Texto de Apresentação',
                    'instructor_signature' => 'Assinatura do Mentor',
                    'platform_logo' => 'Logo da Plataforma',
                ];
            @endphp
            const tagLabels = @json($certificateTagLabels);

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
                    fonts.forEach(font => {
                        $('#style-font-family').append(`<option value="${font.font_family}">${font.name}</option>`);

                        if (font.type === 'google_link' && font.google_font_url) {
                            $('head').append(`<link href="${font.google_font_url}" rel="stylesheet">`);
                        } else if (font.type === 'file' && font.file_path) {
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

            function renderElements() {
                $canvas.empty();

                $.each(certSettings, function (key, data) {
                    if (!data || typeof data !== 'object' || data.x === undefined || data.y === undefined) return;

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
                            whiteSpace: data.multiline ? 'pre-line' : 'nowrap',
                            width: (data.multiline && data.maxWidth) ? (data.maxWidth + 'px') : 'auto',
                            textAlign: data.textAlign || 'left',
                            border: '1px dashed transparent',
                            padding: '4px',
                            zIndex: data.zIndex || 10,
                            display: (key !== 'platform_logo' && data.visible === false) ? 'none' : 'block'
                        });

                    if (key === 'platform_logo') {
                        $el.css({
                            width: (data.width || 120) + 'px',
                            height: (data.height || 60) + 'px',
                            padding: '0px',
                            backgroundImage: 'url(\"' + platformLogoUrl + '\")',
                            backgroundSize: '100% 100%',
                            backgroundRepeat: 'no-repeat',
                            backgroundPosition: 'center'
                        });
                        $el.text('');
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
                            backgroundImage: url ? ('url(\"' + url + '\")') : 'none',
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
                        $el.text(url ? '' : 'Assinatura');
                    } else {
                        $el.text(data.text || '');
                    }

                    $el.on('mousedown', function (e) {
                        $('.cert-element').css('border-color', 'transparent');
                        $(this).css('border-color', '#007bff');
                        activeElementId = key;

                        $('#selected-elem-name').text(tagLabels[key] || data.text || key);
                        $('#style-x').val(parseFloat(data.x ?? 0).toFixed(2));
                        $('#style-y').val(parseFloat(data.y ?? 0).toFixed(2));
                        $('#style-locked').prop('checked', !!data.locked);
                        $('#style-font-size').val(data.fontSize || 16);
                        $('#style-z-index').val(data.zIndex || 10);
                        $('#style-color').val(data.color || '#000000');
                        $('#style-font-weight').val(data.fontWeight || 'normal');
                        $('#style-font-family').val(data.fontFamily || 'Arial, sans-serif');

                        $('#cert-style-controls').show();
                        $('#logo-dims').toggle(key === 'platform_logo');

                        if (key === 'platform_logo') {
                            $('#logo-width').val(data.width || 120);
                            $('#logo-height').val(data.height || 60);
                            $('#logo-width, #logo-height').prop('disabled', !!data.locked);
                        }

                        updateLayersList();
                        e.stopPropagation();
                    });

                    $canvas.append($el);

                    if (key === 'platform_logo' || key === 'instructor_signature') {
                        $el.resizable({
                            aspectRatio: false,
                            disabled: !!data.locked,
                            handles: 'n, e, s, w, ne, se, sw, nw',
                            stop: function (event, ui) {
                                let w = ui.size.width;
                                let h = ui.size.height;
                                certSettings[key].width = w;
                                certSettings[key].height = h;

                                if (key === 'platform_logo') {
                                    $('#logo-width').val(Math.round(w));
                                    $('#logo-height').val(Math.round(h));
                                }
                            }
                        });
                    }
                });

                $('.cert-element').draggable({
                    containment: "#cert-canvas",
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

                        let x = (ui.position.left / parentW) * 100;
                        let y = (ui.position.top / parentH) * 100;

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
                    if (!visible) {
                        $('#el-' + key).hide();
                    }
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

            // Live sync: title/presentation in the canvas
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

            // Live preview: mentor signature in the canvas
            $('input[name="instructor_signature"]').on('change', function () {
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
                        $sig.css({
                            backgroundImage: 'url("' + instructorSignaturePreviewUrl + '")',
                            backgroundSize: 'contain',
                            backgroundRepeat: 'no-repeat',
                            backgroundPosition: 'center',
                            backgroundColor: 'transparent',
                            borderColor: 'transparent',
                            color: 'transparent',
                            display: 'block',
                        }).text('');
                    }

                    updateLayersList();
                };
                reader.readAsDataURL(this.files[0]);
            });

            // Style Change Listeners
            $('#style-font-size').on('input', function () {
                if (!activeElementId) return;
                let val = $(this).val();
                certSettings[activeElementId].fontSize = val;
                $('#el-' + activeElementId).css('font-size', val + 'px');
            });

            $('#style-z-index').on('input', function () {
                if (!activeElementId) return;
                let val = $(this).val();
                certSettings[activeElementId].zIndex = val;
                $('#el-' + activeElementId).css('z-index', val);
                updateLayersList();
            });

            $('#style-color').on('input', function () {
                if (!activeElementId) return;
                let val = $(this).val();
                certSettings[activeElementId].color = val;
                $('#el-' + activeElementId).css('color', val);
            });

            $('#style-font-weight').on('change', function () {
                if (!activeElementId) return;
                let val = $(this).val();
                certSettings[activeElementId].fontWeight = val;
                $('#el-' + activeElementId).css('font-weight', val);
            });

            $('#style-font-family').on('change', function () {
                if (!activeElementId) return;
                let val = $(this).val();
                certSettings[activeElementId].fontFamily = val;
                $('#el-' + activeElementId).css('font-family', val);
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

            // Keyboard nudging (arrow keys)
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
                val = Math.max(30, Math.min(200, val));
                if (certSettings['platform_logo'] && certSettings['platform_logo'].locked) {
                    $(this).val(certSettings['platform_logo'].height || 60);
                    return;
                }
                certSettings['platform_logo'].height = val;
                $('#el-platform_logo').css('height', val + 'px');
            });

            // Toggle Visibility (logo is mandatory)
            $('.cert-toggle').on('change', function () {
                let key = $(this).data('tag');

                if (key === 'platform_logo') {
                    $(this).prop('checked', true);
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
                $.ajax({
                    url: $(this).attr('action'), type: 'POST', data: formData, processData: false, contentType: false,
                    success: function () { toastr.success('Certificado salvo!'); },
                    error: function () { toastr.error('Erro ao salvar.'); }
                });
            });
        });

        window.previewCertBg = function (input) {
            if (input.files && input.files[0]) {
                try {
                    $(input).next('.custom-file-label').html(input.files[0].name);
                } catch (e) { }

                var reader = new FileReader();
                reader.onload = function (e) {
                    if ($('#cert-bg-img').length) $('#cert-bg-img').attr('src', e.target.result);
                    else $('#cert-bg-placeholder').replaceWith('<img src="' + e.target.result + '" id="cert-bg-img" style="width: 100%; height: 100%; object-fit: cover; position: absolute; z-index: 1;">');

                    const fit = ($('#cert-bg-fit').val() || 'cover') === 'stretch' ? 'fill' : 'cover';
                    $('#cert-bg-img').css('object-fit', fit);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush
