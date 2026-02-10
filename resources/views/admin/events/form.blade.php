@extends('admin.layouts.app')

@section('page_title', $event->exists ? 'Editar Evento' : 'Novo Evento')

@section('content')
    <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs" id="event-tabs" role="tablist">
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
            <div class="tab-content" id="event-tabs-content">
                <!-- TAB GERAL -->
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <form method="POST" enctype="multipart/form-data"
                        action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}">
                        @csrf
                        @if($event->exists) @method('PUT') @endif
                        <div class="form-group mb-2"><label>Título</label><input name="title" class="form-control"
                                value="{{ old('title', $event->title) }}" required></div>
                        <div class="form-group mb-2"><label>Início</label><input name="start_at" type="datetime-local"
                                class="form-control" value="{{ old('start_at', $event->start_at) }}"></div>
                        <div class="form-group mb-2"><label>Preço</label><input name="price" class="form-control mask-money"
                                value="{{ old('price', $event->price) }}"></div>
                        <div class="form-group mb-2">
                            <label>Imagem do evento</label>
                            <input type="hidden" name="remove_image" value="0">
                            <div class="upload-box" data-max-size="5242880"
                                data-existing-url="{{ $event->image ? asset('storage/' . $event->image) : '' }}"
                                data-remove-input="[name='remove_image']">
                                <input type="file" name="image" accept="image/*" class="d-none">
                                <div class="upload-preview mb-2"></div>
                                <div class="upload-meta text-muted"></div>
                                <small class="text-muted upload-help"></small>
                                <div class="progress upload-progress progress-sm d-none mt-2">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-2"><label>Local (Nome do Local)</label><input name="location"
                                        class="form-control" value="{{ old('location', $event->location) }}"></div>
                                <div class="form-group mb-2"><label>Endereço Completo</label>
                                    <div class="input-group"><input name="address" id="addressInput" class="form-control"
                                            value="{{ old('address', $event->address) }}">
                                        <div class="input-group-append"><button type="button" class="btn btn-secondary"
                                                id="searchBtn"><i class="fas fa-search"></i> Buscar</button></div>
                                    </div>
                                </div>
                                <div class="form-group mb-2"><label>Latitude</label><input name="latitude" id="latInput"
                                        class="form-control" value="{{ old('latitude', $event->latitude) }}" readonly></div>
                                <div class="form-group mb-2"><label>Longitude</label><input name="longitude" id="lngInput"
                                        class="form-control" value="{{ old('longitude', $event->longitude) }}" readonly>
                                </div>
                                <input type="hidden" name="published" value="0">
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="published" value="1" class="form-check-input" {{ old('published', $event->published) ? 'checked' : '' }}>
                                    <label class="form-check-label">Publicado</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Mapa (Clique para marcar)</label>
                                <div id="map" style="height: 300px; border-radius: 8px; border: 1px solid #ddd;"></div>
                            </div>
                        </div>

                        <button class="btn btn-primary mt-3">Salvar</button>
                    </form>
                </div>

                <!-- TAB CERTIFICADO -->
                <div class="tab-pane fade" id="certificate" role="tabpanel" aria-labelledby="cert-tab">
                    @if(!$event->exists)
                        <div class="alert alert-info border-0 shadow-sm">
                            <i class="fas fa-info-circle mr-2"></i> Você poderá configurar o certificado após salvar o evento
                            pela primeira vez.
                        </div>
                    @else
                        <form id="certForm" method="POST" action="{{ route('admin.events.update', $event) }}"
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
                                                @if($event->certificate_bg)
                                                    <img src="{{ asset($event->certificate_bg) }}" id="cert-bg-img"
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
                                                    name="is_certificate_enabled" value="1" {{ $event->is_certificate_enabled ? 'checked' : '' }}>
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
                                                    Organizador</label>
                                                @if($event->instructor_signature)
                                                    <div class="mb-2 text-center border p-2 bg-light rounded">
                                                        <img src="{{ asset($event->instructor_signature) }}"
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
                                                    @foreach(['student_name' => 'Nome do Participante', 'course_name' => 'Nome do Evento', 'completion_date' => 'Data do Evento', 'certificate_code' => 'Cód. Validação', 'author_name' => 'Organizador', 'workload_hours' => 'Carga Horária', 'platform_logo' => 'Logo UNN'] as $tag => $label)
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        $(document).ready(function () {
            // Map Logic
            var initialLat = {{ $event->latitude ?? '-23.5505' }};
            var initialLng = {{ $event->longitude ?? '-46.6333' }};
            var zoom = {{ $event->latitude ? 15 : 10 }};
            var map = L.map('map').setView([initialLat, initialLng], zoom);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
            var marker;
            if ({{ $event->latitude ? 'true' : 'false' }}) marker = L.marker([initialLat, initialLng]).addTo(map);
            map.on('click', function (e) { setMarker(e.latlng.lat, e.latlng.lng); });
            function setMarker(lat, lng) {
                if (marker) marker.setLatLng([lat, lng]);
                else marker = L.marker([lat, lng]).addTo(map);
                document.getElementById('latInput').value = lat;
                document.getElementById('lngInput').value = lng;
            }
            $('#searchBtn').on('click', function () {
                var query = document.getElementById('addressInput').value;
                if (!query) return;
                toastr.info('Buscando endereço...');
                fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            var lat = data[0].lat; var lon = data[0].lon;
                            map.setView([lat, lon], 16); setMarker(lat, lon);
                            toastr.success('Endereço encontrado!');
                        } else toastr.error('Endereço não encontrado.');
                    }).catch(err => toastr.error('Erro na busca.'));
            });

            // Certificate Logic
            if ('{{ $event->exists }}' == '') return;
            let certSettings = {!! $event->certificate_settings ? json_encode($event->certificate_settings) : '{}' !!};
            const defaultTags = {
                'student_name': { x: 50, y: 40, text: '[Nome do Participante]', fontSize: 30, color: '#000000', fontWeight: 'bold', fontFamily: 'Arial, sans-serif' },
                'course_name': { x: 50, y: 55, text: '{{ $event->title }}', fontSize: 24, color: '#333333', fontWeight: 'bold', fontFamily: 'Arial, sans-serif' },
                'completion_date': { x: 50, y: 65, text: 'Participou em: {{ $event->start_at ? $event->start_at->format("d/m/Y") : "01/01/2026" }}', fontSize: 16, color: '#555555', fontWeight: 'normal', fontFamily: 'Arial, sans-serif' },
                'certificate_code': { x: 50, y: 85, text: 'Validação: ABC-123', fontSize: 12, color: '#999999', fontWeight: 'normal', fontFamily: 'Arial, sans-serif' },
                'author_name': { x: 50, y: 90, text: 'UNN Eventos', fontSize: 18, color: '#333333', fontWeight: 'bold', fontFamily: 'Arial, sans-serif' },
                'workload_hours': { x: 80, y: 90, text: 'Evento', fontSize: 14, color: '#666666', fontWeight: 'normal', fontFamily: 'Arial, sans-serif' },
                'platform_logo': { x: 50, y: 10, text: 'LOGO UNN', fontSize: 36, color: '#0066cc', fontWeight: 'bold', fontFamily: 'Georgia, serif', width: 120, height: 60, mandatory: true }
            };
            $.each(defaultTags, function (key, val) { if (!certSettings[key]) certSettings[key] = val; });
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